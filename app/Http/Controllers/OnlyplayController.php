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

class OnlyplayController extends Controller
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
			$this->insert_callback('Onlyplay', json_encode($request->all()).json_encode($data).$_SERVER['REQUEST_URI'], json_encode($request->header()));	
			
			return $next($request);
			
        });
    }
	
    public function info(Request $request)
    {
		
		$input = $request->all();
		
		$user_status = $this->get_user_status($input['user_id']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(2401, "Session not found or expired", 200, "onlyplay", $input);
		}		
		
		$game = $this->get_game_session("token", $input['session_id']);
		$user_wealth = $this->get_user_wallet($input['user_id'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, 23);
		
		$response_body = [
			"success" => true,
			"balance" => ''.$user_wealth->balance/$denomination->denomination,
			"nickname" => "player",
			"avatar" => null, 
		];
		
		return response()->json($response_body, 200); 
	
    }

	public function bet(Request $request)
	{
		
		$input = $request->all();

		$user_status = $this->get_user_status($input['user_id']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(2401, "Session not found or expired", 200, "onlyplay", $input);
		}
		
		$game = $this->get_game_session("token", $input['session_id']);
		$user_wealth = $this->get_user_wallet($input['user_id'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, 23);				
		
		$input['withdraw'] = $input['amount'] * $denomination->denomination;
		
		if($user_wealth->balance < $input['withdraw']) {
			return $this->show_error("5001", "Insufficient Funds", 200, "onlyplay", $input);
		}
		
		if (!$game) {
			return $this->show_error(2401, "Session not found or expired", 200, "onlyplay", $input);
		}		
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['round_id']],["internal_session_id",'=',$game->id]]);
 
		if(!$bet) {

			$current_balance = $user_wealth->balance-$input['withdraw'];
			$bet_id = $this->add_bet($input, $user_wealth, $game, "onlyplay");
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "onlyplay", $bet_id, -1, "withdraw");
			$this->update_balance($game->user_id, $user_wealth->id, -$input['withdraw']);
			$this->update_bonus_data($game, $user_wealth, -$input['withdraw']);	
			$this->update_fixed_data($game, $user_wealth, $input['deposit']);
			$this->update_bet(['status' => 1], $bet_id, "id");			
			
		}			
		
		$user_wealth = $this->get_user_wallet($input['user_id'], $game->wallet_id, $game->game_id);

		$body = [
			"success" => true,
			"balance" => ''.$user_wealth->balance/$denomination->denomination,
		];
		
		return response()->json($body, 200);		
		
	}
	

	public function win(Request $request)
	{
		
		$input = $request->all();

		$user_status = $this->get_user_status($input['user_id']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(2401, "Session not found or expired", 200, "onlyplay", $input);
		}
		
		$game = $this->get_game_session("token", $input['session_id']);
		$user_wealth = $this->get_user_wallet($input['user_id'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);				
				
		if (!$game) {
			return $this->show_error(2401, "Session not found or expired", 200, "onlyplay", $input);
		}	
		
		
	 	$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['round_id']],["status",'!=',5]]);

		if(!$bet) {
			return $this->show_error("2402", "Already processed", 200, "onlyplay", $input);		
		}		
		
		$input['deposit'] = $input['amount'] * $denomination->denomination;
		
		if(isset($input['round_closed']) && $input['round_closed'] === true) {
			$status = 5;
		} else {
			$status = 3;
		}
		
		$current_balance = $user_wealth->balance+$input['deposit'];
		$tx_id  = $this->add_transaction($input, $user_wealth, $game, "onlyplay", $bet->id, 1, "deposit");
		$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => $input['deposit'], 'profit'=>new Expression('profit - '.$input['deposit']), 'updated_at' => date('Y-m-d H:i:s'), 'status' => $status], $bet->id, "id");
		$this->update_balance($game->user_id, $user_wealth->id, $input['deposit']);			
		$this->update_fixed_data($game, $user_wealth, $input['deposit']);
		
		$user_wealth = $this->get_user_wallet($input['user_id'], $game->wallet_id, $game->game_id);

		$body = [
			"success" => true,
			"balance" => ''.$user_wealth->balance/$denomination->denomination,
		];
		
		return response()->json($body, 200);		
		
	}	
	
	public function cancel(Request $request)
	{
		
		$input = $request->all();
		 
		$bet = $this->get_bet("tx_id", $input['round_id'], 1);
		
		if(!isset($bet->id)) {
			return response()->json(json_decode ("{}"), 200);
		}		
		
		$rollback 	 = $this->get_rollback("bet_id", $bet->id, 1);
		$transactions = $this->get_transactions(0, 0, 0, 0, [["reference_id","=",$bet->id],["type_id","=",1]]);

		if(!$rollback && $bet && $transactions) {

			foreach($transactions as $transaction) {
				$reverted_id = $this->revert_transaction($transaction);
			}
			
		}
		
		$game = $this->get_game_session("token", $input['session_id']);
		$user_wealth = $this->get_user_wallet($input['user_id'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, 23);	
		
		$body = [
			"success" => true,
			"balance" => ''.$user_wealth->balance/$denomination->denomination,
		];
		
		return response()->json($body, 200);
		
	}
	

}
