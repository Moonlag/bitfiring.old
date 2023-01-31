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

class BgamingController extends Controller
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
			$this->insert_callback('bgaming', json_encode($request->all()).json_encode($data).json_encode($_SERVER).$_SERVER['REQUEST_URI'], json_encode($request->header()));

			$passed = $this->check_signature($request);
			$passed = 1;
			if(!$passed) {
				return $this->show_error(403, "Auth error", 403, "bgaming", $input);
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

    public function play(Request $request)
    {

		$input = $request->all();

		$game = $this->get_game_session("","", 0, [["big_token","=",$input['session_id']]]);

		if(!isset($game->id)) {

			$game_class = new Game;
			$game = $game_class->get_game_by_identer($input['game']);
			$player_id = (int) filter_var($input['user_id'], FILTER_SANITIZE_NUMBER_INT);
			$user_wealth = $this->get_user_wealth($player_id, $game->id);

			$this->insert_game_session($game->id, $player_id, $game->identer, \Str::random(25), "reversed_game", $input['session_id'], $game->provider_id, $user_wealth->id);
			$game = $this->get_game_session("","", 0, [["big_token","=",$input['session_id']]]);

		}

		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);

		$input['balance'] = $user_wealth->balance/$denomination->denomination;

		if(!isset($user_status->status) || $user_status->status != 1) {
			return $this->show_error("110", "Player is disabled", 403, "bgaming", $input);
		}

		$data = [
			'balance' => $input['balance'],
		];

		if(isset($input['finished']) && $input['finished']) {
			$finished_at = date('Y-m-d H:i:s');
		} else {
			$finished_at = null;
		}

		if(isset($input['actions'])) {

			$data['game_id'] = $input['game_id'];

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
						return $this->show_error(100, "Not enough funds", 412, "bgaming", $input);
					}

					$bet_id = $this->add_bet($input, $user_wealth, $game, "bgaming", $denomination, $action);
					$tx_id  = $this->add_transaction($action, $user_wealth, $game, "bgaming", $bet_id, -1, "amount", $denomination, $action);
					$this->update_balance($game->user_id, $user_wealth->id, -$action['amount']*$denomination->denomination);
					$this->update_bonus_data($game, $user_wealth, $action['amount']*$denomination->denomination);
					$this->update_fixed_data($game, $user_wealth, -$action['amount']*$denomination->denomination);

					$this->update_bet(['status' => 1, 'finished_at' => $finished_at], $bet_id, "id");

					$data['balance'] = $user_wealth->balance/$denomination->denomination - $action['amount'];

				break;
				case 'win':

					$bet = $this->get_bet("external_session_id", $input['game_id']);

					if(!$bet) {
                        break;
					}

					$amount = $action['amount']*$denomination->denomination;

					$current_balance = $user_wealth->balance+$action['amount']*$denomination->denomination;
					$tx_id  = $this->add_transaction($action, $user_wealth, $game, "bgaming", $bet->id, 1, "amount", $denomination, $action);
					$this->update_bet(['balance_after' => $current_balance, 'payoffs_sum' => $amount, 'profit'=>new Expression('profit - '.$amount), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 3, 'finished_at' => $finished_at], $bet->id, "id");
					$this->update_balance($game->user_id, $user_wealth->id, $amount);
					$this->update_fixed_data($game, $user_wealth, $amount);

					$data['balance'] = $user_wealth->balance/$denomination->denomination + $action['amount'];

				break;
				}

				if(!isset($bet_id)) {
					$bet = $this->get_bet("external_session_id", $input['game_id'], 1);
					$bet_id = $bet->id;
				}

				$data['transactions'][] = [
					'action_id' => $action['action_id'],
					'tx_id' 	=> (string) $tx_id,
				];

			}

		}

		return response()->json($data, 200);

    }

    public function rollback(Request $request)
    {

		$input = $request->all();

		$game = $this->get_game_session("","", 0, [["big_token","=",$input['session_id']]]);

		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);

		$to_revert = [];
		$revert_failed = 0;

		$data = [
			'balance' => $user_wealth->balance/$denomination->denomination,
			'game_id' => $input['game_id'],
		];

		foreach($input['actions'] as $action) {

			$bet 	  	 = $this->get_bet("external_session_id", $input['game_id'], 1);
			$rollback 	 = $this->get_rollback("token", $action['original_action_id'], 1);

			$transaction = $this->get_transaction(0, 0, 0, 0, [["external_tx_id","=",$action['original_action_id']],["type_id","!=",2]]);

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
			//$bet_id = $this->add_bet($input, $user_wealth, $freespin, "bgaming", $denomination, $input['action']);
			$tx_id  = $this->add_transaction_fs($input['action'], $user_wealth, $freespin, "bgaming", $freespin->id, 1, "amount", $denomination);
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
