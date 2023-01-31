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

class SportGamesController extends Controller
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
			
			
			exit;
			
			$input = $request->all();
			
			$data = json_decode(file_get_contents('php://input'), true);
			$this->insert_callback('sportgames', json_encode($request->all()).json_encode($data).json_encode($_SERVER), json_encode($request->header()));	
			
			//$passed = $this->check_signature($input);			
			
			//if(!$passed) {
			//	return $this->show_error("ERR006", "Authentication failed", 401, "sportgames", $input);		
			//} 
			
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
	
    public function health()
    {

		$body = [];
		
		return response()->json($body, 200);
		
    }

    public function info(Request $request)
    {

		$input = $request->all();
	
		$game = $this->get_game_session("","", 0, [["token","=",$input['sessionToken']]]);
		if(!isset($game->game_id)) {
			return $this->show_error("", "Invalid session token", 400, "sportgames", $input);	
		}
		
		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("", "Invalid session token", 400, "sportgames", $input);	
		}
		
		$currency = $this->get_single_denomination_currency("", "", [["altercode", "=", $user_wealth->code],["provider_id", "=", $game->provider_id]]);

		if (!$currency) {
			return $this->show_error("", "Invalid player currency", 400, "sportgames", $input);
		}
		
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$body = [
			'playerId'  => (string) $game->user_id,
			'balance' => ceil($user_wealth->balance/$denomination->denomination),
			'currency'=> "USDT",
			'country'=> "NL",
		]; 
		
		return response()->json($body, 200);
		
    }

    public function bet(Request $request)
    {

		$input = $request->all();
		
		$user_status = $this->get_user_status($input['playerId']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("", "Invalid session token", 400, "sportgames", $input);	
		}
				
		$game = $this->get_game_session("","", 0, [["token", "=", $input['sessionToken']],["user_id","=",$input['playerId']]]);
		
		if (!$game) {
			return $this->show_error("", "Invalid session token", 400, "sportgames", $input);
		}
		
		$currency = $this->get_single_denomination_currency("", "", [["altercode", "=", $input['currency']],["provider_id", "=", $game->provider_id]]);

		if (!$currency) {
			return $this->show_error("", "Invalid player currency", 400, "sportgames", $input);
		}	
		
		$user_wealth = $this->get_user_wallet($input['playerId'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$input['withdraw'] = $input['amount'] * $denomination->denomination;
		
		if($user_wealth->balance < $input['withdraw']) {
			return $this->show_error("", "Insufficient balance", 403, "sportgames", $input);
		}
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['betId']],["internal_session_id",'=',$game->id]]);
 
		if(!$bet) {
			
			$current_balance = $user_wealth->balance-$input['withdraw'];
			$bet_id = $this->add_bet($input, $user_wealth, $game, "sportgames");
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "sportgames", $bet_id, -1, "withdraw");
			$this->update_balance($game->user_id, $user_wealth->id, -$input['withdraw']);
			$this->update_fixed_data($game, $user_wealth, -$input['withdraw']);
			$this->update_bonus_data($game, $user_wealth, $input['withdraw']);		
			$this->update_bet(['status' => 1], $bet_id, "id");			
			
		}	
		
		$user_wealth = $this->get_user_wallet($input['playerId'], $game->wallet_id, $game->game_id);
		
		$body = [
			'createdAt'	   => \Illuminate\Support\Carbon::now()->setTimezone('Europe/Lisbon')->format('Y-m-d\TH:i:s.v+0200'),
			'balance'	   => ceil($user_wealth->balance/$denomination->denomination),
			'processedTxId'=> (string) $bet_id,
		];
		
		return response()->json($body, 200);
		 
    }
	
    public function win(Request $request)
    {
		
		$input = $request->all();
		
		$user_status = $this->get_user_status($input['playerId']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("", "Invalid session token", 400, "sportgames", $input);	
		}
				
		$game = $this->get_game_session("","", 0, [["token", "=", $input['sessionToken']],["user_id","=",$input['playerId']]]);
		$currency = $this->get_single_denomination_currency("", "", [["altercode", "=", $input['currency']],["provider_id", "=", $game->provider_id]]);

		if (!$currency) {
			return $this->show_error("", "Invalid player currency", 400, "sportgames", $input);
		}	
		
		$user_wealth = $this->get_user_wallet($input['playerId'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		if (!$game) {
			return $this->show_error("", "Invalid session token", 400, "sportgames", $input);
		}
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['betId']],["internal_session_id",'=',$game->id],["status",'=',1]]);
		
		if(!$bet) {	
			
			$body = [
				'createdAt'	   => \Illuminate\Support\Carbon::now()->setTimezone('Europe/Lisbon')->format('Y-m-d\TH:i:s.v+0200'),
				'balance'	   => ceil($user_wealth->balance/$denomination->denomination),
				'processedTxId'=> (string) rand(1,10000),
			];		
			
			return response()->json($body, 200);			
			
		}

		$input['deposit'] = $input['amount'] * $denomination->denomination;

		$current_balance = $user_wealth->balance+$input['deposit'];
		$tx_id  = $this->add_transaction($input, $user_wealth, $game, "sportgames", $bet->id, 1, "deposit");
		$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => $input['deposit'], 'profit'=>new Expression('profit - '.$input['deposit']), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 3], $bet->id, "id");
		$this->update_balance($game->user_id, $user_wealth->id, $input['deposit']);			
		$this->update_fixed_data($game, $user_wealth, $input['deposit']);
		
		$user_wealth = $this->get_user_wallet($input['playerId'], $game->wallet_id, $game->game_id);
		
		$body = [
			'createdAt'	   => \Illuminate\Support\Carbon::now()->setTimezone('Europe/Lisbon')->format('Y-m-d\TH:i:s.v+0200'),
			'balance'	   => ceil($user_wealth->balance/$denomination->denomination),
			'processedTxId'=> (string) $bet->id,
		];		
		
		return response()->json($body, 200);
		
    }
	
    public function rollback(Request $request)
    {
		
		$input = $request->all();
		
		$user_status = $this->get_user_status($input['playerId']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("", "Invalid session token", 400, "sportgames", $input);	
		}
		
		$rollback 	 = $this->get_rollback("external_transaction_id", $input['betId'], 1);
		
		if($rollback) {
			return $this->show_error("", "Invalid request", 200, "sportgames", $input);	
		}
		
		$bet = $this->get_bet("tx_id", $input['betId'], 0, 0, [['status', '!=', 2]]);
		
		if($bet) {
			
			$transactions = $this->get_transactions(0, 0, 0, 0, [["external_tx_id","=",$input['betId']],["type_id","!=",2],["amount",">",0]]);			
			
			foreach ($transactions as $key=>$transaction) {
				if(!$rollback && $bet && $transaction) {
					$reverted_id = $this->revert_transaction($transaction);		
				}	
			}	
			
		}
		
		$game = $this->get_game_session("","", 0, [["user_id","=",$input['playerId']]]);
		$user_wealth = $this->get_user_wallet($input['playerId'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);		
		
		$body = [
			'createdAt'	   => \Illuminate\Support\Carbon::now()->setTimezone('Europe/Lisbon')->format('Y-m-d\TH:i:s.v+0200'),
			'balance'	   => ceil($user_wealth->balance/$denomination->denomination),
			'processedTxId'=> (string) $bet->id,
		];		
		
		return response()->json($body, 200);
		
    }
	
}
