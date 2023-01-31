<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;
use Illuminate\Database\Query\Expression;

class BoomingController extends Controller
{
	use DBTrait;
	use AuthTrait;
	
    private $authToken = '';

    public function __construct()
    {
		
        $this->middleware(function ($request, $next) {
			
			$data = json_decode(file_get_contents('php://input'), true);
			$this->insert_callback('Booming', json_encode($request->all()).json_encode($data).json_encode($_SERVER), json_encode($request->header()));	
			
			return $next($request);
			
        });
    }

    public function callback(Request $request)
    {
		
		$input = $request->all();
		
		$token = $this->get_game_session("big_token", $input['operator_data']);
		
		if (!$token) {
			return response()->json([
				'error' => "custom", 
				'message' => "The token could not be verified.", 
				"buttons" => [
					["title" => "OK", "action" => "close_dialog"],
				]], 403);
		}
		
		//also add self exclusions etc
		$user_status = $this->get_user_status($token->user_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {

			return response()->json([
				'error' => "custom", 
				'message' => "User is disabled", 
				"buttons" => [
					["title" => "OK", "action" => "close_dialog"],
				]], 403);

		}		
	
		$user_wealth = $this->get_user_wealth($token->user_id);
		
		if($user_wealth->balance < $input['bet']) {
			return response()->json(['error' => "low_balance", 'message' => "Not enough funds for the wager"], 403);		
		}		
		
		$bet = $this->get_bet(0,0,0,0,[['external_session_id','=',$input['round']],['internal_session_id','=',$token->id]]);
		
		if(isset($bet->id)) {
			echo json_encode(['balance' => $user_wealth->balance]);
			exit;
		}			
		
		$current_balance = $user_wealth->balance-$input['bet'];

		$bet_id = $this->insert_bet([
			'balance_before' 	   => $user_wealth->balance,
			'balance_after' 	   => $current_balance,
			'bet_sum'			   => $input['bet'],
			'tx_id' 			   => $input['session_id'],
			'internal_session_id'  => $token->id,
			'external_session_id'  => $input['round'],
			'profit' 			   => $input['bet'],
			'user_id' 			   => $token->user_id,
			'game_id' 			   => $token->game_id,
			'created_at' 		   => date('Y-m-d H:i:s'),
			'bet_at' 			   => date('Y-m-d H:i:s.u'), 
			'wallet_id' 		   => $user_wealth->id,
			'status' 			   => 0,
		]);
		
		$this->insert_transaction([
			'amount' 	   => -$input['bet'],
			'bonus_part'   => 0,
			'currency_id'  => $user_wealth->currency_id,
			'reference_id' => $bet_id,
			'reference_type_id' => 4,
			'player_id'    => $token->user_id,
			'type_id'	   => 1,
			'created_at'   => date('Y-m-d H:i:s')
		]);			

		$this->update_balance($token->user_id, $user_wealth->id, -$input['bet']);		

		if($input['win'] > 0) {
			
			$current_balance = $current_balance+$input['win'];
			
			$this->insert_transaction([
				'amount' 	   => $input['win'],
				'bonus_part'   => 0,
				'currency_id'  => $user_wealth->currency_id,
				'reference_id' => $bet_id,
				'reference_type_id' => 4,
				'player_id'    => $token->user_id,
				'type_id'	   => 1,
				'created_at'   => date('Y-m-d H:i:s')
			]);		
			
			$this->update_bet([
				'balance_after' => $current_balance,
				'payoffs_sum' => $input['win'],
				'profit'=>new Expression('profit - '.$input['win']),
				'updated_at' => date('Y-m-d H:i:s'),	
				'status' => 3,
			], $bet_id, "id");	
			
			$this->update_balance($token->user_id, $user_wealth->id, $input['win']);
			
		}
		
		echo json_encode(['balance' => $current_balance]);
		exit;
				
    }

    public function rollback(Request $request)
    {
		
		$this->callback($request);
		
        exit;
    }
}
