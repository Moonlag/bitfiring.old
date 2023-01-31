<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;
use App\Http\Traits\CallBackTrait;
use App\Http\Traits\BonusTrait;
use App\Http\Traits\ServiceTrait;
use Illuminate\Database\Query\Expression;

class MascotController extends Controller
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
			
			$data = json_decode(file_get_contents('php://input'), true);
			$this->insert_callback('Mascot', json_encode($request->all()).json_encode($data).$_SERVER['REQUEST_URI'], json_encode($request->header()));	
			
			return $next($request);
			
        });
    }
	
    public function callback(Request $request)
    {
		
		$input = $request->all();
		
		$method = $input['method'];
		
		return $this->$method($input);
	
    }

	public function getBalance($input)
	{
		$exploded_user = explode("_", $input['params']['playerName']);
		$input['params']['playerName'] = $exploded_user[1];
		
		$user_status = $this->get_user_status($input['params']['playerName']);

		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("user_not_active", "User is disabled", 403, "mascot", $input);
		}
		
		$game = $this->get_game_session("","", 0, [["user_id","=",$input['params']['playerName']],["ident","=",$input['params']['gameId']]]);
		
		$user_wealth = $this->get_user_wallet($input['params']['playerName'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, 5);
		
		$response_body = ['balance' => floor($user_wealth->balance/$denomination->denomination)];
		
		return $this->show_response($response_body, $input, 200, "mascot");
		
	}
	
	public function rollbackTransaction($input)
	{
		
		$bet 	  	 = $this->get_bet("tx_id", $input['params']['roundId'], 1);
		
		if(!isset($bet->id)) {
			return $this->show_response([], $input, 200, "mascot");
		}		
		
		$rollback 	 = $this->get_rollback("bet_id", $bet->id, 1);
		$transactions = $this->get_transactions(0, 0, 0, 0, [["reference_id","=",$bet->id],["type_id","=",1]]);

		if(!$rollback && $bet && $transactions) {

			foreach($transactions as $transaction) {
				$reverted_id = $this->revert_transaction($transaction);
			}
			
		}
		
		return $this->show_response(json_decode ("{}"), $input, 200, "mascot");
		
	}
	
	public function withdrawAndDeposit($input)
	{
	
		$exploded_user = explode("_", $input['params']['playerName']);
		$input['params']['playerName'] = $exploded_user[1];	
	
		$user_status = $this->get_user_status($input['params']['playerName']);
		$game = $this->get_game_session("token", $input['params']['sessionId']);

		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(5, "User is disabled", 200, "mascot", $input);
		}	

		$user_wealth = $this->get_user_wallet($input['params']['playerName'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);

		$current_balance = $user_wealth->balance;
		
		if($user_wealth->balance < ($input['params']['withdraw']*$denomination->denomination)) {
			return $this->show_error(1, "ErrNotEnoughMoneyCode", 200, "mascot", $input);		
		}				
		
		if($denomination->altercode != $input['params']['currency']) {
			return $this->show_error(2, "ErrIllegalCurrencyCode", 200, "mascot", $input);		
		}	
		
		
		if(isset($input['params']['reason']) && $input['params']['reason'] == "GAME_PLAY_FINAL") {
			$finished_at = date('Y-m-d H:i:s');
		} else {
			$finished_at = null;
		}
		
		$bet = $this->get_bet("tx_id", $input['params']['gameRoundRef'], 1);
		if(isset($bet->id)) {
			$bet_id = $bet->id;
		} else {
			$bet_id = null;
		}

		
		if(($input['params']['withdraw']*$denomination->denomination) < 0) {
			return $this->show_error(3, "ErrNegativeDepositCode", 200, "mascot", $input);		
		} else {
			
			
			$current_balance = $current_balance-($input['params']['withdraw']*$denomination->denomination);
			if(!$bet_id) {
				$bet_id = $this->add_bet($input, $user_wealth, $game, "mascot", $denomination);
			}
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "mascot", $bet_id, -1, "withdraw", $denomination);
			$this->update_balance($game->user_id, $user_wealth->id, -($input['params']['withdraw']*$denomination->denomination));
			$this->update_fixed_data($game, $user_wealth, -$input['params']['withdraw']*$denomination->denomination);			
			$this->update_bonus_data($game, $user_wealth, $input['params']['withdraw']*$denomination->denomination);	
			$this->update_bet(['status' => 1, 'finished_at' => $finished_at], $bet_id, "id");
			
		}
		
		if($input['params']['deposit'] < 0) {
			return $this->show_error(4, "ErrNegativeWithdrawalCode", 200, "mascot", $input);		
		} elseif($input['params']['deposit'] > 0) {
			
			$current_balance = $current_balance+($input['params']['deposit']*$denomination->denomination);
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "mascot", $bet_id, 1, "deposit", $denomination);
			$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => ($input['params']['deposit']*$denomination->denomination), 'profit'=>new Expression('profit - '.($input['params']['deposit']*$denomination->denomination)), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 3, 'finished_at' => $finished_at], $bet_id, "id");
			$this->update_balance($game->user_id, $user_wealth->id, ($input['params']['deposit']*$denomination->denomination));
			$this->update_fixed_data($game, $user_wealth, $input['params']['deposit']*$denomination->denomination);			
			
		}
		
		$response_body = ['newBalance' => floor($current_balance / $denomination->denomination), 'transactionId' => $tx_id];
		
		return $this->show_response($response_body, $input, 200, "mascot");		
		
		
	}

}
