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
use App\Http\Traits\ServiceTrait;

class SlottyController extends Controller
{
	use DBTrait;
	use AuthTrait;
	use CallBackTrait;
	use BonusTrait;
	use ServiceTrait;
	
    private $authToken = '';

    public function __construct()
    {
		
        $this->middleware(function ($request, $next) {
			
			$input = $request->all();
			
			$data = json_decode(file_get_contents('php://input'), true);
			$this->insert_callback('slotty', json_encode($request->all()).json_encode($data).json_encode($_SERVER), json_encode($request->header()));	
			
			$passed = $this->check_signature($input);			
			
			if(!$passed) {
				return $this->show_error("ERR006", "Authentication failed", 401, "slotty", $input);		
			} 
			
			return $next($request);
			
        });
    }
	
    private function check_signature($input)
    {
      	
		$params = $input;
		
		unset($params['hash']);
		if(isset($params['ipinfo'])) {
			unset($params['ipinfo']);
		}
		ksort($params);
		
		$uri = "";
		foreach($params as $key=>$part) {
			$uri .= "&".$key."=".urlencode($part);
		}
		$uri = substr($uri, 1);
		
		$hash = hash_hmac('sha256', $uri, "282c23fe-c82a-4c3d-893a-d1d17559b6f7");

		if ($hash == $input['hash']) {
			return 1;
		}
		
		return 0;
		
    }	

    public function play(Request $request)
    {
		
		$input = $request->all();
		
		$method = $input['action'];
		
		return $this->$method($input);

    }

    public function balance($input)
    {

		$game = $this->get_game_session("","", 0, [["user_id","=",$input['player_id']]]);
		$user_status = $this->get_user_status($input['player_id']);
		$user_wealth = $this->get_user_wallet($input['player_id'], $game->wallet_id, $game->game_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("ERR005", "Unauthorized request", 401, "slotty", $input);	
		}
		
		$currency = $this->get_single_denomination_currency("", "", [["altercode", "=", $input['currency']],["provider_id", "=", $game->provider_id]]);

		if (!$currency) {
			return $this->show_error("ERR008", "Unknown currency", 401, "slotty", $input);
		}
		
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$body = [
			'status'  => 200,
			'balance' => $user_wealth->balance/$denomination->denomination,
			'currency'=> $denomination->altercode,
		];
		
		return response()->json($body, 200);
		
    }

    public function bet($input)
    {
		
		$user_status = $this->get_user_status($input['player_id']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("ERR005", "Unauthorized request", 401, "slotty", $input);
		}
				
		$game = $this->get_game_session("","", 0, [["ident", "=", $input['game_id']],["user_id","=",$input['player_id']]]);
		
		$currency = $this->get_single_denomination_currency("", "", [["altercode", "=", $input['currency']],["provider_id", "=", $game->provider_id]]);

		if (!$currency) {
			return $this->show_error("ERR008", "Unknown currency", 401, "slotty", $input);
		}	
		
		$user_wealth = $this->get_user_wallet($input['player_id'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$input['withdraw'] = $input['amount'] * $denomination->denomination;
		
		if($user_wealth->balance < $input['withdraw']) {
			return $this->show_error("ERR003", "Insufficient Funds", 401, "slotty", $input);
		}
		
		
		if (!$game) {
			return $this->show_error("ERR002", "The session has timed out", 401, "slotty", $input);
		}
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['round_id']],["internal_session_id",'=',$game->id]]);
 
		if(!$bet) {
			
			$current_balance = $user_wealth->balance-$input['withdraw'];
			$bet_id = $this->add_bet($input, $user_wealth, $game, "slotty");
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "slotty", $bet_id, -1, "withdraw");
			$this->update_balance($game->user_id, $user_wealth->id, -$input['withdraw']);
			$this->update_fixed_data($game, $user_wealth, -$input['withdraw']);
			$this->update_bonus_data($game, $user_wealth, $input['withdraw']);		
			$this->update_bet(['status' => 1], $bet_id, "id");			
			
		}	
		
		$user_wealth = $this->get_user_wallet($input['player_id'], $game->wallet_id, $game->game_id);
		
		$body = [
			'status'  => 200,
			'balance' => $user_wealth->balance/$denomination->denomination,
			'currency'=> $denomination->altercode,
		];
		
		return response()->json($body, 200);
		 
    }
	
    public function win($input)
    {
		
		$user_status = $this->get_user_status($input['player_id']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("ERR005", "Unauthorized request", 401, "slotty", $input);
		}
				
		$game = $this->get_game_session("","", 0, [["ident", "=", $input['game_id']],["user_id","=",$input['player_id']]]);
		$currency = $this->get_single_denomination_currency("", "", [["altercode", "=", $input['currency']],["provider_id", "=", $game->provider_id]]);

		if (!$currency) {
			return $this->show_error("ERR008", "Unknown currency", 401, "slotty", $input);
		}	
		
		$user_wealth = $this->get_user_wallet($input['player_id'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		if (!$game) {
			return $this->show_error("ERR002", "The session has timed out", 401, "slotty", $input);
		}
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['round_id']],["internal_session_id",'=',$game->id],["status",'=',1]]);

		if(!$bet) {
			return $this->show_error("ERR007", "Already processed", 401, "slotty", $input);		
		}

		$input['deposit'] = $input['amount'] * $denomination->denomination;
		
		if($input['deposit'] > 0) {
			$status = 3;
		} else {
			$status = 1;
		}
		
		$current_balance = $user_wealth->balance+$input['deposit'];
		$tx_id  = $this->add_transaction($input, $user_wealth, $game, "slotty", $bet->id, 1, "deposit");
		$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => $input['deposit'], 'profit'=>new Expression('profit - '.$input['deposit']), 'updated_at' => date('Y-m-d H:i:s'), 'finished_at' => date('Y-m-d H:i:s'), 'status' => $status], $bet->id, "id");
		$this->update_balance($game->user_id, $user_wealth->id, $input['deposit']);			
		$this->update_fixed_data($game, $user_wealth, $input['deposit']);
		
		$user_wealth = $this->get_user_wallet($input['player_id'], $game->wallet_id, $game->game_id);
		
		$body = [
			'status'  => 200,
			'balance' => $user_wealth->balance/$denomination->denomination,
			'currency'=> $denomination->altercode,
		];		
		
		return response()->json($body, 200);
		
    }
	
    public function cancel($input)
    {
		
		$user_status = $this->get_user_status($input['player_id']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("ERR005", "Unauthorized request", 401, "slotty", $input);
		}
		
		$rollback 	 = $this->get_rollback("external_transaction_id", $input['transaction_id'], 1);
		
		if($rollback) {
			return $this->show_error("ERR007", "Already processed", 401, "slotty", $input);	
		}
		
		$bet 	  	 = $this->get_bet("tx_id", $input['round_id']);
		
		//if(!$bet) {
		//	return $this->show_error("ERR001", "Unknown error", 401, "slotty", $input);				
		//}	
		
		if($bet) {
			
			$transaction = $this->get_transaction(0, 0, 0, 0, [["external_tx_id","=",$input['transaction_id']],["type_id","!=",2]]);			

			if(!$rollback && $bet && $transaction) {
				$reverted_id = $this->revert_transaction($transaction);		
			}	
			
		}
		
		$game = $this->get_game_session("","", 0, [["user_id","=",$input['player_id']]]);
		$user_wealth = $this->get_user_wallet($input['player_id'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);		
		
		$body = [
			'status'  => 200,
			'balance' => $user_wealth->balance/$denomination->denomination,
			'currency'=> $denomination->altercode,
		];		
		
		return response()->json($body, 200);
		
    }
	
}
