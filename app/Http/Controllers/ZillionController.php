<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;
use App\Http\Traits\CallBackTrait;
use Illuminate\Database\Query\Expression;

class ZillionController extends Controller
{
	use DBTrait;
	use AuthTrait;
	use CallBackTrait;
	
    private $authToken = 'ahsgfda6756231hgas7652';

    public function __construct()
    {
		
        $this->middleware(function ($request, $next) {
			
            if ($request->hasHeader('x-request-sign', false) && $this->checkRequestSignature($request)) {
				
				$data = json_decode(file_get_contents('php://input'), true);
				$this->insert_callback('Zillion', json_encode($request->all()).json_encode($data).$_SERVER['REQUEST_URI'], json_encode($request->header()));	
				
                return $next($request);
            }

            throw new HttpException(403);
			
        });
    }

    private function checkRequestSignature(Request $request): bool
    {
		
		mail('magistriam@gmail.com','test', hash_hmac('sha256', $request->getContent(), $this->authToken).'-'.$request->header('x-request-sign').'-'.json_encode($request->getContent()) );
				
        return $request->header('x-request-sign') === hash_hmac('sha256', $request->getContent(), $this->authToken);
    }

    public function play(Request $request)
    {
		
		$input = $request->all();
		
		$user_wealth = $this->get_user_wealth($input['user_id']);	
		
		$token = $this->get_game_session("", "", 0, [["user_id", "=", $input['user_id']],["ident", "=", $input['game']]]);

		if (!$token && isset($input['actions']) && count($input['actions']) > 0) {
			return response()->json(["code" => "500", "message" => "Unknown Error", "balance" => $user_wealth->balance], 403);
		}

		$user_status = $this->get_user_status($input['user_id']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return response()->json(["code" => "110", "message" => "Player is disabled", "balance" => $user_wealth->balance], 403);
		}

		$data = [
			'balance' => $user_wealth->balance*100,
		];			
		
		if(isset($input['actions'])) {
			
			foreach($input['actions'] as $action) {
				
				$user_wealth = $this->get_user_wealth($input['user_id']);
				
				switch($action['action']) {
				case 'bet':

					$bet = $this->get_bet("external_session_id", $input['game_id']);
					
					if($bet) {
						$bet = $bet->id;
						continue 2;
					}				
				
					if($user_wealth->balance < ($action['amount']/100)) {
						return response()->json(["code" => "100", "message" => "Not enough funds", "balance" => $user_wealth->balance], 403);		
					}				
					
					$bet_id = $this->bet($action, $user_wealth, $input, $token);
					
					$data = [
						'balance' => ($user_wealth->balance*100 - $action['amount']),
					];		
				
				break;
				case 'win':
				
					$bet = $this->get_bet("external_session_id", $input['game_id']);
					
					if(!$bet) {
						continue 2;
					}					
				
					$bet_id = $this->win($action, $user_wealth, $input, $token, $bet);
					
					$data = [
						'balance' => ($user_wealth->balance*100 + $action['amount']),
					];	
					
				break;
				}
				
				if(!isset($bet_id)) {
					$bet = $this->get_bet("external_session_id", $input['game_id'], 1);
					$bet_id = $bet->id;				
				}
				
				$data['transactions'][] = [
					'action_id' => $action['action_id'],
					'tx_id' 	=> $bet_id,
				];			
				
			}
			
		}
		
		echo json_encode($data);
		exit;
        
    }

	public function win($action, $user_wealth, $input, $token, $bet) {
		
		$this->update_bet([
			'balance_after' => ($user_wealth->balance+($action['amount']/100)),
			'payoffs_sum'   => $action['amount']/100,
			'profit' 		=> new Expression('profit - '.($action['amount']/100)),
			'updated_at'    => date('Y-m-d H:i:s'),
		], $input['game_id']);
		
		$this->insert_transaction([
			'amount' 	   => ($action['amount']/100),
			'bonus_part'   => 0,
			'currency_id'  => $user_wealth->currency_id,
			'reference_id' => $bet->id,
			'reference_type_id' => 4,
			'player_id'    => $token->user_id,
			'type_id'	   => 3,
			'wallet_id'    => $user_wealth->id,
			'token'		   => $action['action_id'],
			'created_at'   => date('Y-m-d H:i:s')
		]);				
		
		$this->update_balance($token->user_id, $user_wealth->id, ($action['amount']/100));	

		$this->update_bet([
			'status' => 3,
		], $input['game_id']);
	
		return $bet->id;
		
	}		
	
	public function bet($action, $user_wealth, $input, $token) {
		
		
		$current_balance = ($user_wealth->balance-($action['amount']/100));
		
		$bet_id = $this->insert_bet([
			'balance_before' 	   => $user_wealth->balance,
			'balance_after' 	   => ($user_wealth->balance-($action['amount']/100)),
			'bet_sum'			   => ($action['amount']/100),
			'tx_id' 			   => $input['game_id'],
			'external_session_id'  => $token->token,
			'internal_session_id'  => $token->id,
			'profit' 			   => ($action['amount']/100),
			'user_id' 			   => $token->user_id,
			'game_id' 			   => $token->game_id,
			'created_at' 		   => date('Y-m-d H:i:s'),
			'bet_at' 			   => date('Y-m-d H:i:s.u'), 
			'wallet_id' 		   => $user_wealth->id,
			'status' 			   => 0,
		]);

		$this->insert_transaction([
			'amount' 	   => -($action['amount']/100),
			'bonus_part'   => 0,
			'currency_id'  => $user_wealth->currency_id,
			'reference_id' => $bet_id,
			'reference_type_id' => 4,
			'player_id'    => $token->user_id,
			'type_id'	   => 1,
			'wallet_id'    => $user_wealth->id,
			'token'		   => $action['action_id'],
			'created_at'   => date('Y-m-d H:i:s')
		]);			
		
		$this->update_balance($token->user_id, $user_wealth->id, -($action['amount']/100));		
		
		return $bet_id;
		
	}
	


    public function rollback(Request $request)
    {

		$input = $request->all();
		
		$user_wealth = $this->get_user_wealth($input['user_id']);
		$to_revert = [];
		$revert_failed = 0;
		
		$data = [
			'balance' => $user_wealth->balance*100,
			'game_id' => $input['game_id'],
		];
	
		foreach($input['actions'] as $action) {
			
			$bet 	  	 = $this->get_bet("tx_id", $input['game_id'], 1);
			$rollback 	 = $this->get_rollback("token", $action['original_action_id'], 1);
			$transaction = $this->get_transaction("token", $action['original_action_id'], 1);		
			
			if($bet && $transaction && !$rollback) {
				
				$to_revert[] = $transaction;
				
			} else {
				
				$revert_failed = 1;
				
				$data['transactions'][] = [
					'action_id' => $action['action_id'],
					'tx_id' 	=> "",
				];	
				
			}
			
		}
		
		if(!$revert_failed) {
			
			foreach($to_revert as $transaction) {	
			
				$reverted_id = $this->revert_transaction($transaction);
				$data['transactions'][] = [
					'action_id' => $action['action_id'],
					'tx_id' 	=> $reverted_id,
				];
				
			}
			
		}
		
		echo json_encode($data);
		exit;
	
    }
}
