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
use App\Models\Game;

class OnespinForwinController extends Controller
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

			$input = $request->all();

			$data = json_decode(file_get_contents('php://input'), true);
			$this->insert_callback('onespinforwin', json_encode($request->all()).json_encode($data).json_encode($_SERVER), json_encode($request->header()));

			$passed = $this->check_signature($request);
			$passed = 1;
			if(!$passed) {
				return $this->show_error(403, "Auth error", 403, "onespinforwin", $input);
			}

			return $next($request);

        });
    }

    private function check_signature(Request $request): bool
    {
		//return 1;
		//mail('magistriam@gmail.com','test', hash_hmac('sha256', $request->getContent(), $this->authToken).'-'.$request->header('x-request-sign').'-'.json_encode($request->getContent()) );
		//$test[] = $request->header('X-REQUEST-SIGN');
		//$test[] = $request->getContent();
		//$test[] = hash_hmac('sha256', $request->getContent(), "vsv7hTbqnzaYEKt5VR48qjDX");
		//$test[] = $request->header('X-REQUEST-SIGN') === hash_hmac('sha256', $request->getContent(), "vsv7hTbqnzaYEKt5VR48qjDX");
		//echo json_encode($test);
		//exit;
        return $request->header('X-REQUEST-SIGN') === hash_hmac('sha256', $request->getContent(), "Pfhv5mqW8PvmsPWc4uaLg7gp");
    }

    public function authenticate(Request $request)
    {

		$input = $request->all();
		$key = "bitfiring";
		$pass = "R3WxweAaqLcQDj";

		if(!isset($input["account"]["username"]) || !isset($input["account"]["password"]) || $input["account"]["username"] != $key || $input["account"]["password"] != $pass) {
			return $this->show_error("ERR000", "Authentication failed", 200, "onespinforwin", $input);
		}


		$game = $this->get_game_session("","", 0, [["token","=",$input['clienttoken']]]);

		if (!$game) {
			return $this->show_error("ERR001", "The session has timed out or game does not exist", 200, "onespinforwin", $input);
		}



		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);

		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("ERR002", "Player is disabled", 200, "onespinforwin", $input);
		}

		$data = [
			'user_id'   => "bitfiring_".$game->user_id,
			'token'     => $input['clienttoken'],
			'currency'  => $denomination->altercode,
			'casino_id' => "5",
		];

		return response()->json($data, 200);

    }

    public function getbalance(Request $request)
    {

		$input = $request->all();

		$game = $this->get_game_session("","", 0, [["token","=",$input['token']]]);

		if (!$game) {
			return $this->show_error("ERR001", "The session has timed out or game does not exist", 200, "onespinforwin", $input);
		}

		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);

		$input['balance'] = $user_wealth->balance/$denomination->denomination;

		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("110", "Player is disabled", 403, "onespinforwin", $input);
		}

		$data = [
			'balance' => number_format($input['balance'], 0, '', ''),
		];

		return response()->json($data, 200);

    }

    public function play(Request $request)
    {

		$input = $request->all();

		$game = $this->get_game_session("","", 0, [["token","=",$input['token']]]);

		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);

		$input['balance'] = $user_wealth->balance/$denomination->denomination;

		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("110", "Player is disabled", 403, "onespinforwin", $input);
		}

		$data = [
			'balance' => $input['balance'],
		];

		if(isset($input['actions'])) {

			foreach($input['actions'] as $action) {

				$user_wealth = $this->get_user_wealth($game->user_id, $game->game_id);
				$current_balance = $user_wealth->balance/$denomination->denomination;

				switch($action['action']) {
				case 'bet':

					$bet = $this->get_bet("tx_id", $action['action_id']);

					if($bet) {
						$bet = $bet->id;
						break;
					}

					if($current_balance < $action['amount']) {
						return $this->show_error(100, "Not enough funds", 412, "onespinforwin", $input);
					}

					$bet_id = $this->add_bet($input, $user_wealth, $game, "onespinforwin", $denomination, $action);
					$tx_id  = $this->add_transaction($action, $user_wealth, $game, "onespinforwin", $bet_id, -1, "amount", $denomination, $action);
					$this->update_balance($game->user_id, $user_wealth->id, -$action['amount']*$denomination->denomination);
					$this->update_fixed_data($game, $user_wealth, -$action['amount']*$denomination->denomination);
					$this->update_bonus_data($game, $user_wealth, $action['amount']*$denomination->denomination);

					$this->update_bet(['status' => 1], $bet_id, "id");

					$data['balance'] = $user_wealth->balance/$denomination->denomination - $action['amount'];

				break;
				case 'win':

					$bet = $this->get_bet("external_session_id", $input['round_id']);

					if(!$bet) {
						break;
					}

					$amount = $action['amount']*$denomination->denomination;

					$current_balance = $user_wealth->balance+$action['amount']*$denomination->denomination;
					$tx_id  = $this->add_transaction($action, $user_wealth, $game, "onespinforwin", $bet->id, 1, "amount", $denomination, $action);
					$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => $amount, 'profit'=>new Expression('profit - '.$amount), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 3], $bet->id, "id");
					$this->update_balance($game->user_id, $user_wealth->id, $amount);
					$this->update_fixed_data($game, $user_wealth, $amount);

					$data['balance'] = $user_wealth->balance/$denomination->denomination + $action['amount'];

				break;
				}

				if(!isset($bet_id)) {
					$bet = $this->get_bet("external_session_id", $input['round_id'], 1);
					$bet_id = $bet->id;
				}

				$data = [
					'balance' => number_format($data['balance'], 0, '', ''),
				];

				//$data['transactions'][] = [
				//	'action_id' => $action['action_id'],
				//	'tx_id' 	=> (string) $bet_id,
				//];

			}

		}

		return response()->json($data, 200);

    }

    public function rollback(Request $request)
    {

		$input = $request->all();

		$game = $this->get_game_session("","", 0, [["token","=",$input['token']]]);

		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);

		$to_revert = [];
		$revert_failed = 0;

		$data = [
			'balance' => $user_wealth->balance/$denomination->denomination,
		];

		foreach($input['actions'] as $action) {

			$bet 	  	 = $this->get_bet("external_session_id", $input['round_id'], 1);
			$rollback 	 = $this->get_rollback("external_transaction_id", $action['original_action_id'], 1);

			$transaction = $this->get_transaction(0, 0, 0, 0, [["external_tx_id","=",$action['original_action_id']],["type_id","!=",2]]);
			if($action['original_action_id'] == 'd222227') {
				//dd($bet, $rollback, $transaction);
			}
			if($bet && $transaction && !$rollback) {

				$data['balance'] = $data['balance'] - $transaction->amount/$denomination->denomination;

				$reverted_id = $this->revert_transaction($transaction);

				$data['transactions'][] = [
					'action_id' => $action['action_id'],
					'tx_id' 	=> $reverted_id,
				];

			} else {

				$revert_failed = 1;

				$data['transactions'][] = [
					'action_id' => $action['action_id'],
					'tx_id' 	=> "0",
				];

			}

		}

		$data = [
			'balance' => number_format($data['balance'], 0, '', ''),
		];

		echo json_encode($data);
		exit;

    }

	public function freespins(Request $request)
	{

		$input = $request->all();

		$freespin = $this->get_single_freespin("issue_code", $input['issue_id']);
		$freespin->user_id = $freespin->player_id;
		$input['game_id'] = $freespin->game_id;

		$user_wealth = $this->get_user_wallet($freespin->player_id, $freespin->wallet_id);

		$denomination = $this->get_denomination($user_wealth->currency_id, 40);

		$input['balance'] = $user_wealth->balance/$denomination->denomination;

		if(isset($input['action']['action'])) {
			$transaction = $this->get_transaction(0, 0, 0, 0, [["external_tx_id","=",$input['action']['action_id']]]);
		}

		if(isset($input['action']['action']) && !isset($transaction->id) && $input['action']['amount'] > 0) {
			$amount = $input['action']['amount']*$denomination->denomination;
			$balance = isset($input['action']['action'])?$input['balance']+$input['action']['amount']:$input['balance'];
			//$bet_id = $this->add_bet($input, $user_wealth, $freespin, "onespinforwin", $denomination, $input['action']);
			$tx_id  = $this->add_transaction_fs($input['action'], $user_wealth, $freespin, "onespinforwin", $freespin->id, 1, "amount", $denomination);
			$this->update_balance($freespin->player_id, $user_wealth->id, $amount);
    		$this->freespins_issue($freespin->player_id, $user_wealth->currency_id, $amount, $freespin->bonus_id, $tx_id, $freespin->id);
		}

		$user_wealth = $this->get_user_wallet($freespin->player_id, $freespin->wallet_id);
		$balance = $user_wealth->balance/$denomination->denomination;

		$data = [
			'balance' => $balance,
		];

		return response()->json($data, 200);

	}

}
