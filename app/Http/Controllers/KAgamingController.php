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

class KAgamingController extends Controller
{
	use DBTrait;
	use AuthTrait;
	use CallBackTrait;
	use BonusTrait;
	use ServiceTrait;
	
    private $authToken = '';

    public function __construct()
    {
		
		ini_set('precision', 10);
		ini_set('serialize_precision', 10);
		
        $this->middleware(function ($request, $next) {
			
			$data = json_decode(file_get_contents('php://input'), true);
			$this->insert_callback('Kagaming', json_encode($request->all()).json_encode($data).$_SERVER['REQUEST_URI'], json_encode($request->header()));	
			
			$input = $request->all();
			
			if(!isset($input['hash'])) {
				return $this->show_error(2, "Invalid request", 200, "kagaming", $input);	
			}
			
			$passed = $this->check_signature($request);	
			
			if(!$passed) {
				return $this->show_error(3, "Invalid hash", 200, "kagaming", $input);		
			}
			
			return $next($request);
			
        });
    }
	
    private function check_signature(Request $request): bool
    {
		
		$hash = $request->only('hash')['hash'];
		$input = $request->all();
		unset($input['hash']);
		
        return $hash === hash_hmac('sha256', json_encode($input), "F877B1CCB7E03C178B2E0F302B9A8FBD");
    }	
	
    public function start(Request $request)
    {
		
		$input = $request->all();
		
		$game = $this->get_game_session("big_token", $input['token']);
		
		if (!$game) {
			return $this->show_error(100, "Invalid token", 200, "kagaming", $input);
		}	
		
		$user_status = $this->get_user_status($game->user_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(4, "Invalid player", 200, "kagaming", $input);
		}		
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$response_body = [
			"status"     => "success",
			"statusCode" => 0,
			"playerId"   => "player_".$game->user_id,
			"sessionId"  => $input['sessionId'], 
			"balance"    => floor($user_wealth->balance/$denomination->denomination),
			"currency"   => $denomination->altercode,
		];
		
		return response()->json($response_body, 200); 
	
    }
	
    public function end(Request $request)
    {

		$input = $request->all();
		
		$game = $this->get_game_session("big_token", $input['token']);
		
		if (!$game) {
			return $this->show_error(100, "Invalid token", 200, "kagaming", $input);
		}	
		
		$user_status = $this->get_user_status($game->user_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(4, "Invalid player", 200, "kagaming", $input);
		}		
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$response_body = [
			"status"     => "success",
			"statusCode" => 0,
			"playerId"   => "player_".$game->user_id,
			"sessionId"  => $input['sessionId'], 
			"balance"    => floor($user_wealth->balance/$denomination->denomination),
			"currency"   => $denomination->altercode,
		];
		
		return response()->json($response_body, 200); 
	
    }
	
    public function balance(Request $request)
    {
		
		$input = $request->all();
		
		$game = $this->get_game_session("big_token", $input['token']);
		
		if (!$game) {
			return $this->show_error(100, "Invalid token", 200, "kagaming", $input);
		}	
		
		$user_status = $this->get_user_status($game->user_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(4, "Invalid player", 200, "kagaming", $input);
		}		
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$response_body = [
			"status"     => "success",
			"statusCode" => 0,
			"online"     => true,
			"playerId"   => "player_".$game->user_id,
			"sessionId"  => $input['sessionId'], 
			"balance"    => floor($user_wealth->balance/$denomination->denomination),
			"currency"   => $denomination->altercode,
		];
		
		return response()->json($response_body, 200); 
	
    }
	
	public function credit(Request $request)
	{
		
		$input = $request->all();

		$game = $this->get_game_session("big_token", $input['token']);
		
		if (!$game) {
			return $this->show_error(100, "Invalid token", 200, "kagaming", $input);
		}	
		
		$user_status = $this->get_user_status($game->user_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(4, "Invalid player", 200, "kagaming", $input);
		}		
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);		
		
		$input['deposit']  = $input['amount'] * $denomination->denomination;
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['transactionId']],["internal_session_id",'=',$game->id]]);
 
		if($bet) {		
			
			if($input['deposit'] > 0) {
				
				$current_balance = $user_wealth->balance+$input['deposit'];
				$tx_id  = $this->add_transaction($input, $user_wealth, $game, "kagaming", $bet->id, 1, "deposit", $denomination);
				$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => $input['deposit'], 'profit'=>new Expression('profit - '.$input['deposit']), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 3], $bet->id, "id");
				$this->update_balance($game->user_id, $user_wealth->id, $input['deposit']);
				$this->update_fixed_data($game, $user_wealth, $input['deposit']);
				
			}
			
		}

		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);

		$body = [
			"balance" => floor($user_wealth->balance/$denomination->denomination),
			"status"     => "success",
			"statusCode" => 0,
		];
		
		return response()->json($body, 200);		
		
	}
	


	public function play(Request $request)
	{
		
		$input = $request->all();

		$game = $this->get_game_session("big_token", $input['token']);
		
		if (!$game) {
			return $this->show_error(100, "Invalid token", 200, "kagaming", $input);
		}	
		
		$user_status = $this->get_user_status($game->user_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(4, "Invalid player", 200, "kagaming", $input);
		}		
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);		
		
		$input['withdraw'] = $input['betAmount'] * $denomination->denomination;
		$input['deposit']  = $input['winAmount'] * $denomination->denomination;
		
		if($user_wealth->balance < $input['withdraw']) {
			return $this->show_error("200", "Insufficient balance (cashable or bonus) to play bet", 200, "kagaming", $input);
		}
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['transactionId']],["internal_session_id",'=',$game->id]]);
 
		if(!$bet) {

			$current_balance = $user_wealth->balance-$input['withdraw'];
			$bet_id = $this->add_bet($input, $user_wealth, $game, "kagaming", $denomination);
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "kagaming", $bet_id, -1, "withdraw", $denomination);
			$this->update_balance($game->user_id, $user_wealth->id, -$input['withdraw']);
			$this->update_bonus_data($game, $user_wealth, -$input['withdraw']);	
			$this->update_fixed_data($game, $user_wealth, $input['deposit']);
			$this->update_bet(['status' => 1], $bet_id, "id");			
			
			if($input['deposit'] > 0) {
				
				$current_balance = $user_wealth->balance-$input['withdraw']+$input['deposit'];
				$tx_id  = $this->add_transaction($input, $user_wealth, $game, "kagaming", $bet_id, 1, "deposit", $denomination);
				$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => $input['deposit'], 'profit'=>new Expression('profit - '.$input['deposit']), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 3], $bet_id, "id");
				$this->update_balance($game->user_id, $user_wealth->id, $input['deposit']);			
				$this->update_fixed_data($game, $user_wealth, $input['deposit']);				
				
			}
			
		} elseif($input['deposit'] > 0 && isset($input['freeGames']) && $input['freeGames'] == true) {
			
			$current_balance = $user_wealth->balance+$input['deposit'];
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "kagaming", $bet->id, 1, "deposit", $denomination);
			$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => new Expression('payoffs_sum + '.$input['deposit']), 'profit'=>new Expression('profit - '.$input['deposit']), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 3], $bet->id, "id");
			$this->update_balance($game->user_id, $user_wealth->id, $input['deposit']);
			$this->update_fixed_data($game, $user_wealth, $input['deposit']);		
			
		}
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);

		$body = [
			"balance" => floor($user_wealth->balance/$denomination->denomination),
			"status"     => "success",
			"statusCode" => 0,
		];
		
		return response()->json($body, 200);		
		
	}	
	
	public function revoke(Request $request)
	{

		$input = $request->all();
		 
		$bet = $this->get_bet("tx_id", $input['transactionId'], 1);
		
		if(!isset($bet->id)) {
			return $this->show_error(400, "Transaction does not exist or not revokable", 200, "kagaming", $input);
		}		
	
		$rollback 	 = $this->get_rollback("bet_id", $bet->id, 1);
		$transactions = $this->get_transactions(0, 0, 0, 0, [["reference_id","=",$bet->id],["type_id","=",1]]);
		
		if(!$rollback && $bet && $transactions) {

			foreach($transactions as $transaction) {
				$reverted_id = $this->revert_transaction($transaction);
			}
			
		}
		
		$user_wealth = $this->get_user_wallet($bet->user_id, $bet->wallet_id, $bet->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, 41);	
		
		$body = [
			"status"     => "success",
			"statusCode" => 0,
			"balance" => floor($user_wealth->balance/$denomination->denomination),
		];
		
		return response()->json($body, 200);
		
	}
	

}
