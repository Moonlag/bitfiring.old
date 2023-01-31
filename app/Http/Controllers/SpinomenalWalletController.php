<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;
use App\Http\Traits\SpinomenalTrait;
use App\Http\Traits\CallBackTrait;
use Illuminate\Database\Query\Expression;

class SpinomenalWalletController extends Controller
{
	
	use DBTrait;
	use AuthTrait;
	use SpinomenalTrait;
	use CallBackTrait;
	
    private $authToken = '';

    public function __construct()
    {

        $this->middleware(function ($request, $next) {
			
			$data = json_decode(file_get_contents('php://input'), true);
			
			$this->insert_callback('spinomenal', json_encode($request->all()).json_encode($data).$_SERVER['REQUEST_URI'], json_encode($request->header()));
			$this->custom_error(['Sig','TimeStamp','GameToken','ExternalId'], $request->all(), 6001, "Invalid Parameters");		
			$this->check_signature($request->all(), 6002, "Invalid Signature");

			return $next($request);
			
        });
		
    }
	
    private function custom_error(array $check_fields, array $response, $code, $message) 
	{
		
        $error = [];
		
        foreach ($check_fields as $field) {
            if(!isset($response[$field])) {
                array_push($error, $field);
            }
        }

        if(!empty($error)) {
			return $this->show_error($code, $message, 200, "spinomenal", $input);
			
        }
		
    }

    private function check_signature($input, $code, $message)
    {
      
		$signature = md5($input['TimeStamp'].$input['GameToken'].$input['RequestId']."ff898345CCD");

		if($signature != $input['Sig']) {
			echo json_encode(["ErrorCode" => $code, "ErrorMessage" => $message]);
			die();
		}
		
    }

	public function get_balance(Request $request)
	{
		
		$input = $request->all();
		
		$game = $this->get_game_session("token", $input['GameToken']);
		
		if (!$game) {
			return $this->show_error(6003, "Token was not found", 200, "spinomenal", $input);
		}
		
		$user_status = $this->get_user_status($input['ExternalId']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(6004, "Player account locked", 200, "spinomenal", $input);
		}
		
		$user_wealth = $this->get_user_wealth($input['ExternalId']);
		
		$data = [
			'Balance' => ($user_wealth->balance * 100),
			'ErrorCode' => 0,
			'ErrorMessage' => null,
			'TimeStamp' => date("YmdHis"),
		];
		
		echo json_encode($data);
		exit;
		
	}

	public function process_bet(Request $request)
	{
		
		$input = $request->all();		
		
		$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['TicketId']],["status",">",2]]);

		if($bet) {
			return $this->show_error(5030, "Round is closed", 200, "spinomenal", $input);
		}
		
		$game = $this->get_game_session("token", $input['GameToken']);				
		
		if (!$game) {
			return $this->show_error(6003, "Token was not found", 200, "spinomenal", $input);
		}		

		$user_status = $this->get_user_status($input['ExternalId']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(6004, "Player account locked", 200, "spinomenal", $input);
		}
		
		$user_wealth = $this->get_user_wealth($input['ExternalId']);

		if($user_wealth->balance < ($input['BetAmount']/100+$input['WinAmount']/100) && $user_wealth->balance < ($input['BetAmount']/100)) {
			return $this->show_error(6011, "Insufficient funds", 200, "spinomenal", $input);
		}
		
		if(in_array($input['TransactionType'],["InstantWin", "BET", "Free_InstantWin"])) {

			$current_balance = ($user_wealth->balance-($input['BetAmount']/100));
			$bet_id = $this->add_bet($input, $user_wealth, $game, "spinomenal");
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "spinomenal", $bet_id, -1, "BetAmount");
			$this->update_balance($game->user_id, $user_wealth->id, -($input['BetAmount']/100));			
			$this->update_bet(['status' => 1], $bet_id, "id");	
			
		}
		
		if(in_array($input['TransactionType'],["InstantWin", "Free_InstantWin", "WIN", "CANCELED_BET"]) && $input['WinAmount'] > 0) {

			if(!isset($bet_id)) {
				$bet = $this->get_bet("tx_id", $input['RoundId'], 1);
				$bet_id = $bet->id;
			}
			
			if(!isset($current_balance)) {
				$current_balance = $user_wealth->balance;
			}
			
			$status = $input['TransactionType']=="CANCELED_BET"?4:3;
		
			$current_balance = $current_balance+($input['WinAmount']/100);
			$this->add_transaction($input, $user_wealth, $game, "spinomenal", $bet_id, 1, "WinAmount");
			$this->update_bet(['balance_after' => $current_balance,	'payoffs_sum' => ($input['WinAmount']/100),	'profit'=>new Expression('profit - '.($input['WinAmount']/100)), 'updated_at' => date('Y-m-d H:i:s'), 'status' => $status ], $bet_id, "id");
			$this->update_balance($game->user_id, $user_wealth->id, ($input['WinAmount']/100));			

		}
		
		if(isset($input['IsRoundFinish']) && $input['IsRoundFinish'] && $bet_id) {
			$this->update_bet(['status' => 5], $bet_id, "id");
		}
		
		$data = [
			'ExtTransactionId' => $bet_id,
			'Balance' => $current_balance*100,
			'ErrorCode' => 0,
			'ErrorMessage' => null,
			'TimeStamp' => date("YmdHis"),
		];
		
		echo json_encode($data);
		
	}

	public function solve_bet(Request $request)
	{
		
		$input = $request->all();		
		
		$token = $this->get_game_session("token", $input['GameToken']);		
		
		if (!$token) {
			return $this->show_error(6003, "Token was not found", 200, "spinomenal", $input);
		}		

		$user_status = $this->get_user_status($input['ExternalId']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error(6004, "Player account locked", 200, "spinomenal", $input);
		}
		
		$bet = $this->get_bet("tx_id", $input['RoundId'], 1);

		if(!$bet) {
			return $this->show_error(6010, "Unknown ticket", 200, "spinomenal", $input);
		}
		
		$data = [
			'ExtTransactionId' => $bet->tx_id,
			'Balance' => $bet->balance_after,
			'ErrorCode' => 0,
			'ErrorMessage' => null,
			'TimeStamp' => date("YmdHis"),
		];
		
		echo json_encode($data);
		
	}
	

}
