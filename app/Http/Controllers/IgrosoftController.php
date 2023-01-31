<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;
use Illuminate\Database\Query\Expression;
use App\Http\Traits\CallBackTrait;
use App\Http\Traits\BonusTrait;
use Spatie\ArrayToXml\ArrayToXml;

class IgrosoftController extends Controller
{
	use DBTrait;
	use AuthTrait;
	use CallBackTrait;
	use BonusTrait;
	
    private $authToken = '';

    public function __construct()
    {
		
        $this->middleware(function ($request, $next) {
			
			$input = $request->all();
			
			$data = json_decode(file_get_contents('php://input'), true);
			//$this->insert_callback('IGROSOFT', json_encode($request->all()).json_encode($data).json_encode($_SERVER), json_encode($request->header()));	
			
			//$passed = $this->check_signature($request->header());			
			
			//if(!$passed) {
			//	return $this->show_error("ERROR", "Authentication failed", 200, "igrosoft", $input);		
			//} 
			
			return $next($request);
			
        });
    }
	
    private function check_signature($headers)
    {
      	
		$to_md5 = $headers['x-casino-provider-id'][0].$headers['x-casino-transaction-id'][0].$headers['x-casino-timestamp'][0]."3CCFE6B21B21D0886DA705BC26D47EE9";

		$md5 = md5($to_md5);
	 	if($md5 != $headers['x-casino-signature'][0]) {
			return 0;
		} 

		return 1;
		
    }	

    public function play(Request $request)
    {
		
		$input = $request->all();
		$method = "";
		
		switch(true) {
		case (!isset($input['transactionId']) || !$input['transactionId']):
			$method = "balance";
		break;
		case (isset($input['withdraw'])):
			$method = "withdraw";									
		break;
		case (isset($input['deposit'])):
			$method = "deposit";
		break;
		}
		
		if($input['previousTrxId'] > 0 && ($input['transactionId'] - $input['previousTrxId']) > 1) {
			//echo 'here';
			//exit;
		}
		
		return $this->$method($input);

    }

    public function balance($input)
    {

		$user_status = $this->get_user_status($input['userId']);
		$user_wealth = $this->get_user_wealth($input['userId']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("ERROR", "User is not valid", 200, "igrosoft", $input);
		}
		
		$game = $this->get_game_session("token", $input['token']);
		
		if (!$game) {
			return $this->show_error("ERROR", "Game not found", 200, "igrosoft", $input);
		}
		
		$body = [
			'sessionId' 	=> $input['sessionId'],
			'status'		=> 'SUCCESS',
			'transactionId' => $input['transactionId'],
			'amount'		=> $user_wealth->balance,
		];
		
		return response()->json($body, 200);
		
    }

    public function withdraw($input)
    {
		
		$user_status = $this->get_user_status($input['userId']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("ERROR", "User is not valid", 200, "igrosoft", $input);
		}
		
		$user_wealth = $this->get_user_wealth($input['userId']);
		
		if($user_wealth->balance < $input['withdraw']) {
			return $this->show_error("ERROR", "Out of money", 200, "igrosoft", $input);
		}
		
		$game = $this->get_game_session("token", $input['token']);
		
		if (!$game) {
			return $this->show_error("ERROR", "Game not found", 200, "igrosoft", $input);
		}
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['gameRound']],["external_session_id",'=',$input['sessionId']]]);

		if(!$bet) { 
			$current_balance = $user_wealth->balance-$input['withdraw'];
			$bet_id = $this->add_bet($input, $user_wealth, $game, "igrosoft");
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "igrosoft", $bet_id, -1, "withdraw");
			$this->update_balance($game->user_id, $user_wealth->id, -$input['withdraw']);		
			$this->update_bonus_data($game, $user_wealth, $input['withdraw']);		
			$this->update_bet(['status' => 1], $bet_id, "id");			
			
		}
		
		$user_wealth = $this->get_user_wealth($input['userId']);
		
		if(count($input['failedTransactions']) >= 2) {
			
			$transactions = $this->get_transactions(0, 0, 0, 0, [["external_tx_id","=",array_shift($input['failedTransactions'])]]);
			
			if($transactions) {

				foreach($transactions as $transaction) {
									
					$bet 	  	 = $this->get_bet("id", $transaction->reference_id, 1);
					$rollback 	 = $this->get_rollback("bet_id", $bet->id, 1);
					
					if(!$rollback && $bet) {
					
						$reverted_id = $this->revert_transaction($transaction);
						
					}
				}
				
			}			
			
			
		}
		
		
		$body = [
			'sessionId' 	=> $input['sessionId'],
			'status'		=> 'SUCCESS',
			'transactionId' => $input['transactionId'],
			'amount'		=> $user_wealth->balance,
		];
		
		return response()->json($body, 200);
		
    }
	
    public function deposit($input)
    {
		
		$user_status = $this->get_user_status($input['userId']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("ERROR", "User is not valid", 200, "igrosoft", $input);
		}
		
		$user_wealth = $this->get_user_wealth($input['userId']);
		$game = $this->get_game_session("token", $input['token']);
		
		if (!$game) {
			return $this->show_error("ERROR", "Game not found", 200, "igrosoft", $input);
		}
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['gameRound']],["external_session_id",'=',$input['sessionId']],["status",'=',1]]);
 
		if(!$bet) {
			return $this->show_error("ERROR", "Round not found or complete", 200, "igrosoft", $input);				
		}

		$current_balance = $user_wealth->balance+$input['deposit'];
		$tx_id  = $this->add_transaction($input, $user_wealth, $game, "igrosoft", $bet->id, 1, "deposit");
		$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => $input['deposit'], 'profit'=>new Expression('profit - '.$input['deposit']), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 3], $bet->id, "id");
		$this->update_balance($game->user_id, $user_wealth->id, $input['deposit']);			
		
		$user_wealth = $this->get_user_wealth($input['userId']);
		
		$body = [
			'sessionId' 	=> $input['sessionId'],
			'status'		=> 'SUCCESS',
			'transactionId' => $input['transactionId'],
			'amount'		=> $user_wealth->balance,
		];		
		
		return response()->json($body, 200);
		
    }
	
}
