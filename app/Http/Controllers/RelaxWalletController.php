<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;
use Illuminate\Database\Query\Expression;

class RelaxWalletController extends Controller
{
	
	use DBTrait;
	use AuthTrait;
	
    private $authToken = '';

    public function __construct()
    {

        $this->middleware(function ($request, $next) {
			
			$data = json_decode(file_get_contents('php://input'), true);
			
			$this->insert_callback('Relax', json_encode($request->all()).json_encode($data), json_encode($request->header()));	
			
			return $next($request);
			
        });
		
    }
	
	public function verify_token(Request $request)
	{
		
		$input = $request->all();
		$token = $this->get_game_session("token", $input['token']);
		
		if (!$token) {
			return response()->json(['errorcode' => "INVALID_TOKEN", 'errormessage' => "The token could not be verified."], 401);
		}
		
		//also add self exclusions etc
		$user_status = $this->get_user_status($token->user_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return response()->json(['errorcode' => "BLOCKED_FROM_PRODUCT", 'errormessage' => "User is blocked from product."], 403);
		}
		
		$user_wealth = $this->get_user_wealth($token->user_id);
		
		$data = [
			'customerid' => $token->user_id,
			'countrycode' => "CA",
			'cashiertoken' => $token->big_token,
			'customercurrency' => $user_wealth->code,
			'balance' => ($user_wealth->balance*100),
		];
		
		echo json_encode($data);
		
	}	
	
	public function get_balance(Request $request)
	{
		
		$input = $request->all();
		
		//also add self exclusions etc
		$user_status = $this->get_user_status($input['customerid']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return response()->json(['errorcode' => "UNHANDLED", 'errormessage' => "Final fallback error code."], 500);
		}
		
		$user_wealth = $this->get_user_wealth($input['customerid']);
		
		$data = [
			'customercurrency' => $user_wealth->code,
			'balance' => ($user_wealth->balance*100),
		];
		
		echo json_encode($data);
		
	}
	
	public function withdraw(Request $request)
	{

		$input = $request->all();
		$token = $this->get_game_session("big_token", $input['cashiertoken']);
		
		if (!$token) {
			return response()->json(['errorcode' => "INVALID_TOKEN", 'errormessage' => "The token could not be verified."], 401);
		}
		
		//also add self exclusions etc
		$user_status = $this->get_user_status($token->user_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return response()->json(['errorcode' => "BLOCKED_FROM_PRODUCT", 'errormessage' => "User is blocked from product."], 403);
		}
	
		$user_wealth = $this->get_user_wealth($token->user_id);
		
		if($user_wealth->balance < ($input['amount']/100)) {
			return response()->json(['errorcode' => "INSUFFICIENT_FUNDS", 'errormessage' => "There are insufficient funds to go through with the withdrawal."], 403);		
		}
		
		$bet_id = $this->insert_bet([
			'balance_before' 	   => $user_wealth->balance,
			'balance_after' 	   => ($user_wealth->balance-($input['amount']/100)),
			'bet_sum'			   => ($input['amount']/100),
			'tx_id' 			   => $input['txid'],
			'external_session_id'  => $input['data']['round_id'],
			'internal_session_id'  => $token->id,
			'profit' 			   => ($input['amount']/100),
			'user_id' 			   => $token->user_id,
			'game_id' 			   => $token->game_id,
			'created_at' 		   => date('Y-m-d H:i:s'),
			'bet_at' 			   => date('Y-m-d H:i:s.u'), 
			'wallet_id' 		   => $user_wealth->id,
			'status' 			   => 0,
		]);
		
		$this->insert_transaction([
			'amount' 	   => -($input['amount']/100),
			'bonus_part'   => 0,
			'currency_id'  => $user_wealth->currency_id,
			'reference_id' => $bet_id,
			'reference_type_id' => 4,
			'player_id'    => $token->user_id,
			'type_id'	   => 1,
			'created_at'   => date('Y-m-d H:i:s')
		]);		
		
		$this->update_balance($token->user_id, $user_wealth->id, -($input['amount']/100));
			
		$data = [
			'balance' => ($user_wealth->balance-($input['amount']/100))*100,
			'txid' => $input['txid'],
			'remotetxid' => $bet_id,
			'customercurrency' => $user_wealth->code,
			//"betdistribution": {
			//	"real": 20,
			//	"bonus": 80
			//}
		];
		
		echo json_encode($data);
		
	}	
	
	public function deposit(Request $request)
	{
		
		$input = $request->all();
		$token = $this->get_game_session("big_token", $input['cashiertoken']);
		
		if (!$token) {
			return response()->json(['errorcode' => "INVALID_TOKEN", 'errormessage' => "The token could not be verified."], 401);
		}
		
		//also add self exclusions etc
		$user_status = $this->get_user_status($token->user_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return response()->json(['errorcode' => "BLOCKED_FROM_PRODUCT", 'errormessage' => "User is blocked from product."], 403);
		}
	
		$user_wealth = $this->get_user_wealth($token->user_id);
		
		$bet = $this->get_bet("external_session_id", $input['gamesessionid']);
		
		if(!$bet) {
			return response()->json(['errorcode' => "UNHANDLED", 'errormessage' => "Final fallback error code."], 500);
		}
		
		$this->update_bet([
			'balance_after' => ($user_wealth->balance+($input['amount']/100)),
			'profit'=>new Expression('profit - '.($input['amount']/100)),
			'updated_at' => date('Y-m-d H:i:s'),
		], $input['gamesessionid']);
		
		$this->insert_transaction([
			'amount' 	   => ($input['amount']/100),
			'bonus_part'   => 0,
			'currency_id'  => $user_wealth->currency_id,
			'reference_id' => $bet->id,
			'reference_type_id' => 4,
			'player_id'    => $token->user_id,
			'type_id'	   => 3,
			'created_at'   => date('Y-m-d H:i:s')
		]);		
		
		$this->update_balance($token->user_id, $user_wealth->id, ($input['amount']/100));

		$this->update_bet([
			'status' => 3,
		], $input['gamesessionid']);
		
		$data = [
			'balance' => ($user_wealth->balance+($input['amount']/100)),
			'txid' => $input['txid'],
			'remotetxid' => $bet->id,
			//'customercurrency' => $user_wealth->code,
			//"betdistribution": {
			//	"real": 20,
			//	"bonus": 80
			//}
		];
		
		echo json_encode($data);
		
	}	

	public function rollback(Request $request)
	{
		
		$input = $request->all();
			
		$bet = $this->get_bet("external_session_id", $input['gamesessionid']);
		
		if(!$bet) {
			return response()->json(['errorcode' => "TRANSACTION_DECLINED", 'errormessage' => "Transaction not found"], 403);
		}
		
		$user_wealth = $this->get_user_wealth($bet->user_id);
				
		$this->update_balance($bet->user_id, $user_wealth->id, $bet->profit);
		
		$this->insert_transaction([
			'amount' 	   => $bet->profit,
			'bonus_part'   => 0,
			'currency_id'  => $user_wealth->currency_id,
			'reference_id' => $bet->id,
			'reference_type_id' => 4,
			'player_id'    => $bet->user_id,
			'type_id'	   => 2,
			'created_at'   => date('Y-m-d H:i:s')
		]);			
		
		$this->update_bet([
			'status' => 2,
		], $input['gamesessionid']);	
		
		$data = [
			'balance' => ($user_wealth->balance + $bet->profit),
			'txid' => $input['txid'],
			'remotetxid' => $bet->id,
		];
			
		
		echo json_encode($data);
		
	}	
	
    private function custom_error(array $check_fields, array $response, $code="", $message="") 
	{
		
        $error = [];
		
        foreach ($check_fields as $field) {
            if(!isset($response[$field])) {
                array_push($error, $field);
            }
        }

        if(!empty($error)) {
            
			return 1;
			
        }
		
		return 0;
		
    }

    private function check_signature($input, $code, $message)
    {
        
		$signature = md5($input['TimeStamp'].$input['GameToken']."0"."ff898345CCD");

		if($signature != $input['Sig']) {
			echo json_encode(["ErrorCode" => $code, "ErrorMessage" => $message]);
			die();
		}
		
    }

}
