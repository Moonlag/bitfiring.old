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
use App\Http\Traits\EvoplayTrait;
 
class EvoWalletController extends Controller
{
	
	use DBTrait;
	use AuthTrait;
	use EvoplayTrait;
	use CallBackTrait;
	use BonusTrait;
	use ServiceTrait;
	
    private $authToken = '';
	private $answer;
	
    public function __construct() 
    {

        $this->middleware(function ($request, $next) {
			
			$data = json_decode(file_get_contents('php://input'), true);
			
			$this->insert_callback('Evo', json_encode($request->all()).json_encode($data), json_encode($request->header()));	

			return $next($request);
			
        });
		
    }
	
	public function callback(Request $request)
	{
		
		$input = $request->all();
					
		if(!isset($input['name'])) {
			
			return $this->show_error("", "", 200, "evo", ['scope'=>'user', 'no_refund'=>1, 'message'=>'Critical Error, Please contact administrator']);
			
		}
		
		$this->prepare_evoplay('http://api.evoplay.games', 3871, 1, 'a9ae64f179729a2b36058306307503a5');
		
		$check_params = $input;
		unset($check_params['ipinfo']);
		unset($check_params['signature']);
		
		$output = $this->evo_signatureFormed($check_params);
		
		if(1==2 && $output['signature'] != $input['signature']) {
			
			return $this->show_error("", "", 200, "evo", ['scope'=>'user', 'no_refund'=>1, 'message'=>'Authentication Error, Please contact administrator']);
			
		}
		
		$class = $input['name'];

		return $this->$class($input);
		
	}
	

	public function init($input)
	{
		
		$game = $this->get_game_session('token', $input['token']);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, 1);
		
		return $this->show_response(["balance"=>''.$user_wealth->balance/$denomination->denomination, "code"=>$denomination->altercode], [], 200, "evo");
		
	}
	
	public function bet($input)
	{
		
		$game = $this->get_game_session('token', $input['token']);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, 1);
	
		$input['withdraw'] = ''.$input['data']['amount'] * $denomination->denomination;
		
		if($user_wealth->balance < $input['withdraw']) {
			return $this->show_error("", "", 200, "evo", ['scope'=>'user', 'no_refund'=>1, 'message'=>'Out of funds']);			
		}		

		$bet = $this->get_bet(0,0,0,0,[["external_session_id",'=',$input['data']['round_id']],["internal_session_id",'=',$game->id]]);
		
		if(!$bet) {

			$current_balance = $user_wealth->balance-$input['withdraw'];
			$bet_id = $this->add_bet($input, $user_wealth, $game, "evo", $denomination);
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "evo", $bet_id, -1, "withdraw", $denomination);
			$this->update_balance($game->user_id, $user_wealth->id, -$input['withdraw']);
			$this->update_fixed_data($game, $user_wealth, -$input['withdraw']);
			$this->update_bonus_data($game, $user_wealth, $input['withdraw']);	
			$this->update_bet(['status' => 1], $bet_id, "id");			
			
		}			
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		
		return $this->show_response(["balance"=>''.$user_wealth->balance/$denomination->denomination, "code"=>$denomination->altercode], [], 200, "evo");
		
	}
	
	public function win($input)
	{
		
		$game = $this->get_game_session('token', $input['token']);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, 1);
		$bet = $this->get_bet(0,0,0,0,[["external_session_id",'=',$input['data']['round_id']],["status",'!=',5]]);
		
		$input['deposit'] = $input['data']['amount'] * $denomination->denomination;
		
		if(!$bet) {
			return $this->show_response(["balance"=>''.$user_wealth->balance/$denomination->denomination, "code"=>$denomination->altercode], [], 200, "evo");					
		}
		
		if(!$input['deposit']) {
			
			$this->update_bet(['finished_at' => date('Y-m-d H:i:s')], $bet->id, "id");
			return $this->show_response(["balance"=>''.$user_wealth->balance/$denomination->denomination, "code"=>$denomination->altercode], [], 200, "evo");
		}
	
		if(isset($input['data']['final_action']) && $input['data']['final_action']) {
			$status = 5;
			$finished_at = date('Y-m-d H:i:s');
		} else {
			$status = 3;
			$finished_at = null;
		}

		$current_balance = $user_wealth->balance+$input['deposit'];
		$tx_id  = $this->add_transaction($input, $user_wealth, $game, "evo", $bet->id, 1, "deposit", $denomination);
		$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => $input['deposit'], 'profit'=>new Expression('profit - '.$input['deposit']), 'updated_at' => date('Y-m-d H:i:s'), 'finished_at' => $finished_at, 'status' => $status], $bet->id, "id");
		$this->update_balance($game->user_id, $user_wealth->id, $input['deposit']);			
		$this->update_fixed_data($game, $user_wealth, $input['deposit']);
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		
		return $this->show_response(["balance"=>''.$user_wealth->balance/$denomination->denomination, "code"=>$denomination->altercode], [], 200, "evo");
		
	}
	
	public function refund($input)
	{
		
		$bet = $this->get_bet("tx_id", $input['data']['refund_callback_id']);
		
		$game = $this->get_game_session('token', $input['token']);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, 1);
		
		if(!$bet) {		
			return $this->show_response(["balance"=>''.$user_wealth->balance/$denomination->denomination, "code"=>$denomination->altercode], [], 200, "evo");			
		}
		$rollback 	 = $this->get_rollback("bet_id", $bet->id, 1);
		$transactions = $this->get_transactions(0, 0, 0, 0, [["reference_id","=",$bet->id],["type_id","=",1]]);		

		if(!$rollback && $bet && $transactions) {
			foreach($transactions as $transaction) {
				$reverted_id = $this->revert_transaction($transaction);
			}
			
		}	

		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		
		return $this->show_response(["balance"=>''.$user_wealth->balance/$denomination->denomination, "code"=>$denomination->altercode], [], 200, "evo");	
		
	}

}
