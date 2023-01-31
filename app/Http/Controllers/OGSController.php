<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;
use Illuminate\Database\Query\Expression;
use App\Http\Traits\CallBackTrait;
use App\Http\Traits\BonusTrait;
use App\Http\Traits\ServiceTrait;
use Spatie\ArrayToXml\ArrayToXml;

class OGSController extends Controller
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
			$passed = $this->check_signature($input);

			$data = json_decode(file_get_contents('php://input'), true);
			$this->insert_callback('OGS', json_encode($request->all()).json_encode($data).json_encode($_SERVER), json_encode($request->header()));	
			/* 
		 	if(!$passed) {
				
				$error = $this->show_error(1003, "Authentication failed", 200, "ogs", $input);
				$result = ArrayToXml::convert($error['array'], $error['root']);
				return response($result, 200)->header('Content-Type', 'application/xml');			
			}  */
			
			return $next($request);
			
        });
    }
	
    private function check_signature($input)
    {
      	
		$key_array = ["signature", "ipinfo"];
		$uri = "";
		foreach($input as $key=>$part) {
			if(!in_array($key, $key_array)) {
				if(strlen($part) > 0) {
					$uri .= "&".$key."=".$part;
				} else {
					$uri .= "&".$key;
				}
			}
		}
		$uri = substr($uri, 1);
		
		$uri_md5 = md5($uri."m5iPmsf4IqPsT9bvpZb5Evm2PPIoC4");
		
		if(strtoupper($uri_md5) != $input['signature']) {
			return 0;
		}
		
		return 1;
		
    }	

    public function play(Request $request)
    {
				
		$input = $request->all();
		$request = $input['request'];
		
		$result =  $this->$request($input);
		
		return response($result, 200)->header('Content-Type', 'application/xml');
				
    }

    public function getaccount($input)
    {

		$root = [
			'rootElementName' => 'RSP',
			'_attributes' => [
				'request' => $input['request'],
				'rc' 	  => 0,
			],
		];		
	
		$game = $this->get_game_session("token", $input['sessionid']);
		$user_status = $this->get_user_status($input['loginname']);
		$user_wealth = $this->get_user_wallet($input['loginname'], $game->wallet_id, $game->game_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error(1035, "Account blocked", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root']);
			return $result;
		}
		
		
		if (!$game) {
			$error = $this->show_error(1000, "Not logged on", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root'], true, 'UTF-8');
			return $result;
		}
		
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$array = [
			'APIVERSION'	=> $input['apiversion'],
			'GAMESESSIONID' => $input['sessionid'],
			'ACCOUNTID' 	=> $input['loginname'],
			'CURRENCY'	    => $denomination->altercode,
			'CITY' 			=> "New York",
			'COUNTRY'		=> "USA",
		];
		
		$result = ArrayToXml::convert($array, $root, true, 'UTF-8');
		return $result;
		
    }

    public function getbalance($input)
    {

		$root = [
			'rootElementName' => 'RSP',
			'_attributes' => [
				'request' => $input['request'],
				'rc' 	  => 0,
			],
		];		
	
		$game = $this->get_game_session("token", $input['gamesessionid']);
		$user_status = $this->get_user_status($input['loginname']);
		$user_wealth = $this->get_user_wallet($input['loginname'], $game->wallet_id, $game->game_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error(1035, "Account blocked", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root']);
			return $result;
		}
		
		if (!$game) {
			$error = $this->show_error(1000, "Not logged on", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root'], true, 'UTF-8');
			return $result;
		}
		
		
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$array = [
			'APIVERSION'	=> $input['apiversion'],
			'BALANCE' 		=> $user_wealth->balance/$denomination->denomination,
		];
		
		$result = ArrayToXml::convert($array, $root, true, 'UTF-8');
		return $result;
		
    }

    public function wager($input)
    {

		$root = [
			'rootElementName' => 'RSP',
			'_attributes' => [
				'request' => $input['request'],
				'rc' 	  => 0,
			],
		];	
		
		$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['roundid']],["external_session_id","=",$input['gamesessionid']]]);

		if($bet) {
			$error = $this->show_error(110, "Operation not allowed", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root']);
			return $result;
		}

		$user_status = $this->get_user_status($input['loginname']);

		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error(1035, "Account blocked", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root']);
			return $result;
		}
		
		$game = $this->get_game_session("token", $input['gamesessionid']);
		$user_wealth = $this->get_user_wallet($input['loginname'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);	
		
		if($user_wealth->balance < ($input['betamount']*$denomination->denomination)) {
			$error = $this->show_error(1006, "Out of money", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root'], true, 'UTF-8');
			return $result;
		}
		
		if (!$game) {
			$error = $this->show_error(1000, "Not logged on", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root'], true, 'UTF-8');
			return $result;
		}
		
		$current_balance = ($user_wealth->balance-($input['betamount']*$denomination->denomination));
		$bet_id = $this->add_bet($input, $user_wealth, $game, "ogs", $denomination);
		$tx_id  = $this->add_transaction($input, $user_wealth, $game, "ogs", $bet_id, -1, "betamount", $denomination);
		$this->update_balance($game->user_id, $user_wealth->id, -($input['betamount']*$denomination->denomination));
		$this->update_fixed_data($game, $user_wealth, -$input['betamount']*$denomination->denomination);		
		$this->update_bonus_data($game, $user_wealth, $input['betamount']*$denomination->denomination);			
		$this->update_bet(['status' => 1], $bet_id, "id");	
			
		$array = [
			'APIVERSION'			=> $input['apiversion'],
			'REALMONEYBET'  		=> $input['betamount'],
			'BONUSMONEYBET' 		=> 0.00,
			'BALANCE' 				=> $current_balance/$denomination->denomination,
			'ACCOUNTTRANSACTIONID' 	=> $tx_id,
		];
		$result = ArrayToXml::convert($array, $root, true, 'UTF-8');
		return $result;
		
    }
	
	public function result($input) 
	{
		
		$root = [
			'rootElementName' => 'RSP',
			'_attributes' => [
				'request' => $input['request'],
				'rc' 	  => 0,
			],
		];	
		
		$bet = $this->get_bet("tx_id", $input['roundid']);

		if(!$bet) {
			$error = $this->show_error(110, "Operation not allowed", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root']);
			return $result;
		}
		
		$user_status = $this->get_user_status($input['loginname']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error(1035, "Account blocked", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root']);
			return $result;
		}
		
		$game = $this->get_game_session("token", $input['gamesessionid']);
		$user_wealth = $this->get_user_wallet($input['loginname'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);	
		
		if (!$game) {
			$error = $this->show_error(1000, "Not logged on", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root'], true, 'UTF-8');
			return $result;
		}
		
		$status = $input['gamestatus']=="completed"?5:1;
		
		
		$current_balance = $user_wealth->balance+($input['result']*$denomination->denomination);
		$tx_id = $this->add_transaction($input, $user_wealth, $game, "ogs", $bet->id, 1, "result", $denomination);
		$this->update_bet(['balance_after' => $current_balance,	'payoffs_sum' => ($input['result']*$denomination->denomination), 'profit'=>new Expression('profit - '.($input['result']*$denomination->denomination)), 'updated_at' => date('Y-m-d H:i:s'), 'status' => $status ], $bet->id, "id");
		$this->update_balance($game->user_id, $user_wealth->id, ($input['result']*$denomination->denomination));
		$this->update_fixed_data($game, $user_wealth, $input['result']*$denomination->denomination);

		$array = [
			'APIVERSION'			=> $input['apiversion'],
			'BALANCE'  				=> $current_balance/$denomination->denomination,
			'ACCOUNTTRANSACTIONID' 	=> $tx_id,
		];
		
		$result = ArrayToXml::convert($array, $root, true, 'UTF-8');
		return $result;			
		
		
	}
	
	public function rollback($input) 
	{
		
		$root = [
			'rootElementName' => 'RSP',
			'_attributes' => [
				'request' => $input['request'],
				'rc' 	  => 0,
			],
		];
		
		$user_status = $this->get_user_status($input['loginname']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error(1035, "Account blocked", 200, "ogs", $input);
			$result = ArrayToXml::convert($error['array'], $error['root']);
			return $result;
		}		
		
		$bet 	  	 = $this->get_bet("tx_id", $input['roundid']);
		$rollback 	 = $this->get_rollback("bet_id", $bet->id, 1);
		$transactions = $this->get_transactions(0, 0, 0, 0, [["reference_id","=",$bet->id],["type_id","!=",1]]);			

		if(!$rollback && $bet && $transactions) {

			foreach($transactions as $transaction) {
				$reverted_id = $this->revert_transaction($transaction);
			}
			
		}
		
		$game = $this->get_game_session("token", $input['gamesessionid']);
		$user_wealth = $this->get_user_wallet($input['loginname'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);		
		
		$array = [
			'APIVERSION'			=> $input['apiversion'],
			'BALANCE'  				=> $user_wealth->balance/$denomination->denomination,
			'ACCOUNTTRANSACTIONID' 	=> $input['roundid'],
		];
		
		$result = ArrayToXml::convert($array, $root, true, 'UTF-8');
		return $result;			
		
		
	}
	
	public function ping($input) 
	{
		
		$root = [
			'rootElementName' => 'RSP',
			'_attributes' => [
				'request' => $input['request'], 
				'rc' 	  => 0,
			],
		];	
				
		$result = ArrayToXml::convert($array, $root, true, 'UTF-8');
		return $result;			
		
		
	}	
	
}
