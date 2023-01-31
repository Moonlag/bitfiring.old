<?php

namespace App\Http\Traits;

use App\Events\UpdateWallets;
use DB;
use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Query\Expression;


trait CallBackTrait
{

    public function update_balance($user_id, $wallet_id, $amount)
    {
        DB::table('wallets')->where('user_id', '=', $user_id)->where('id', '=', $wallet_id)->update(
            ['balance' => new Expression('balance + ' . $amount)]
        );

        $wallet = $this->get_user_wallet($user_id, $wallet_id);
        $bet = -1 * $amount;
        event(new UpdateWallets($user_id, $wallet_id, $wallet->balance, $bet, $wallet->currency_id));

    }

    public function revert_transaction($transaction)
    {
        $tx_id = $this->insert_transaction([
            'amount' => -$transaction->amount,
            'bonus_part' => 0,
            'currency_id' => $transaction->currency_id,
            'reference_id' => $transaction->reference_id,
            'reference_type_id' => 4,
            'player_id' => $transaction->player_id,
            'token' => $transaction->token,
            'wallet_id' => $transaction->wallet_id,
            'type_id' => 2,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->update_bet([
            'status' => 2,
            'finished_at' => date('Y-m-d H:i:s'),
        ], $transaction->reference_id, "id");

        $this->update_balance($transaction->player_id, $transaction->wallet_id, -$transaction->amount);

        $this->insert_rollback(["bet_id" => $transaction->reference_id, "token" => $transaction->token, "external_transaction_id" => $transaction->external_tx_id]);

        return $tx_id;

    }

    public function add_bet($input, $user_wealth, $game, $provider, $denomination = [], $action = [])
    {

        $bet_id = 0;

        switch ($provider) {
            case "onespinforwin":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - ($action['amount'] * $denomination->denomination),
                    'bet_sum' => $action['amount'] * $denomination->denomination,
                    'tx_id' => $action['action_id'],
                    'internal_session_id' => $game->id,
                    'external_session_id' => $input['round_id'],
                    'profit' => $action['amount'] * $denomination->denomination,
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "infingame":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - ($input['withdraw'] * $denomination->denomination),
                    'bet_sum' => $input['withdraw'] * $denomination->denomination,
                    'tx_id' => $input['roundnum']['@attributes']['id'],
                    'external_session_id' => $input['@attributes']['id'],
                    'internal_session_id' => $game->id,
                    'profit' => $input['withdraw'] * $denomination->denomination,
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "sportgames":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - $input['withdraw'],
                    'bet_sum' => $input['withdraw'],
                    'tx_id' => $input['betId'],
                    'external_session_id' => $input['sessionToken'],
                    'internal_session_id' => $game->id,
                    'profit' => $input['withdraw'],
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "kagaming":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - $input['withdraw'],
                    'bet_sum' => $input['withdraw'],
                    'tx_id' => $input['transactionId'],
                    'external_session_id' => $input['sessionId'],
                    'internal_session_id' => $game->id,
                    'profit' => $input['withdraw'],
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "bgaming":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - ($action['amount'] * $denomination->denomination),
                    'bet_sum' => $action['amount'] * $denomination->denomination,
                    'tx_id' => $action['action_id'],
                    'internal_session_id' => $game->id,
                    'external_session_id' => $input['game_id'],
                    'profit' => $action['amount'] * $denomination->denomination,
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "outcomebet":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - ($input['params']['withdraw'] * $denomination->denomination),
                    'bet_sum' => $input['params']['withdraw'] * $denomination->denomination,
                    'tx_id' => $input['params']['gameRoundRef'],
                    'internal_session_id' => $game->id,
                    'external_session_id' => $input['params']['transactionRef'],
                    'profit' => $input['params']['withdraw'] * $denomination->denomination,
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "mascot":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - ($input['params']['withdraw'] * $denomination->denomination),
                    'bet_sum' => $input['params']['withdraw'] * $denomination->denomination,
                    'tx_id' => $input['params']['gameRoundRef'],
                    'internal_session_id' => $game->id,
                    'external_session_id' => $input['params']['transactionRef'],
                    'profit' => $input['params']['withdraw'] * $denomination->denomination,
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "ogs":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - ($input['betamount'] * $denomination->denomination),
                    'bet_sum' => ($input['betamount'] * $denomination->denomination),
                    'tx_id' => $input['roundid'],
                    'external_session_id' => $input['gamesessionid'],
                    'internal_session_id' => $game->id,
                    'profit' => ($input['betamount'] * $denomination->denomination),
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "igrosoft":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - $input['withdraw'],
                    'bet_sum' => $input['withdraw'],
                    'tx_id' => $input['gameRound'],
                    'external_session_id' => $input['sessionId'],
                    'internal_session_id' => $game->id,
                    'profit' => $input['withdraw'],
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "slotty":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - $input['withdraw'],
                    'bet_sum' => $input['withdraw'],
                    'tx_id' => $input['round_id'],
                    'external_session_id' => $input['round_id'],
                    'internal_session_id' => $game->id,
                    'profit' => $input['withdraw'],
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "onlyplay":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - $input['withdraw'],
                    'bet_sum' => $input['withdraw'],
                    'tx_id' => $input['round_id'],
                    'external_session_id' => $input['token'],
                    'internal_session_id' => $game->id,
                    'profit' => $input['withdraw'],
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
            case "evo":
                $bet_id = $this->insert_bet([
                    'balance_before' => $user_wealth->balance,
                    'balance_after' => $user_wealth->balance - $input['withdraw'],
                    'bet_sum' => $input['withdraw'],
                    'tx_id' => $input['callback_id'],
                    'external_session_id' => $input['data']['round_id'],
                    'internal_session_id' => $game->id,
                    'profit' => $input['withdraw'],
                    'user_id' => $game->user_id,
                    'game_id' => $game->game_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'bet_at' => date('Y-m-d H:i:s.u'),
                    'wallet_id' => $user_wealth->id,
                    'status' => 0,
                ]);
                break;
        }

        return $bet_id;

    }

    public function add_transaction($input, $user_wealth, $game, $provider, $bet_id, $sign, $param_name, $denomination = [], $action = [])
    {

        $tx_id = 0;

        $bonus_ping = $this->is_there_bonus("user_id", $game->user_id);

        switch ($provider) {
            case "onespinforwin":

                $bonus_part = ($sign * $input[$param_name] * $denomination->denomination) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name] * $denomination->denomination,
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $game->token,
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $action['action_id'],
                ]);
                break;
            case "infingame":

                $bonus_part = ($sign * $input[$param_name] * $denomination->denomination) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name] * $denomination->denomination,
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $input['roundnum']['@attributes']['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $input['roundnum']['@attributes']['id'],
                ]);
                break;
            case "sportgames":

                $bonus_part = ($sign * $input[$param_name]) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name],
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $input['sessionToken'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $input['betId'],
                ]);
                break;
            case "kagaming":

                $bonus_part = ($sign * $input[$param_name]) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name],
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $input['sessionId'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $input['transactionId'],
                ]);
                break;
            case "bgaming":

                $bonus_part = ($sign * $input[$param_name] * $denomination->denomination) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name] * $denomination->denomination,
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $game->token,
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $action['action_id'],
                ]);
                break;
            case "evo":

                $bonus_part = ($sign * $input[$param_name] * $denomination->denomination) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name],
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $input['token'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                break;
            case "outcomebet":

                $bonus_part = ($sign * $input['params'][$param_name] * $denomination->denomination) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input['params'][$param_name] * $denomination->denomination,
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $game->token, //change to round id ?? check this stuff later on
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                break;
            case "mascot":

                $bonus_part = ($sign * $input['params'][$param_name] * $denomination->denomination) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input['params'][$param_name] * $denomination->denomination,
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $game->token, //change to round id ?? check this stuff later on
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                break;
            case "ogs":

                $bonus_part = ($sign * $input[$param_name] * $denomination->denomination) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * ($input[$param_name] * $denomination->denomination),
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $input['gamesessionid'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                break;
            case "igrosoft":

                $bonus_part = ($sign * $input[$param_name]) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name],
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $input['sessionId'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $input['transactionId'],
                ]);
                break;
            case "slotty":

                $bonus_part = ($sign * $input[$param_name]) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name],
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $input['round_id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $input['transaction_id'],
                ]);
                break;
            case "onlyplay":

                $bonus_part = ($sign * $input[$param_name]) / 2;

                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name],
                    'bonus_part' => $bonus_ping > 0 ? $bonus_part : 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $bet_id,
                    'reference_type_id' => 4,
                    'player_id' => $game->user_id,
                    'type_id' => 1,
                    'wallet_id' => $user_wealth->id,
                    'token' => $input['round_id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $input['tx_id'],
                ]);
                break;
        }

        return $tx_id;

    }


    public function add_transaction_fs($input, $user_wealth, $freespin, $provider, $freespin_id, $sign, $param_name, $denomination = [], $action = [])
    {

        $tx_id = 0;

        switch ($provider) {
            case "bgaming":
                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name] * $denomination->denomination,
                    'bonus_part' => 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $freespin_id,
                    'reference_type_id' => 2,
                    'player_id' => $freespin->player_id,
                    'type_id' => 1,
                    'wallet_id' => $freespin->wallet_id,
                    'token' => $freespin->issue_code,
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $input['action_id'],
                ]);
                break;
            case "cryptogaming":
                $tx_id = $this->insert_transaction([
                    'amount' => $sign * $input[$param_name] * $denomination->denomination,
                    'bonus_part' => 0,
                    'currency_id' => $user_wealth->currency_id,
                    'reference_id' => $freespin_id,
                    'reference_type_id' => 2,
                    'player_id' => $freespin->player_id,
                    'type_id' => 1,
                    'wallet_id' => $freespin->wallet_id,
                    'token' => $freespin->issue_code,
                    'created_at' => date('Y-m-d H:i:s'),
                    'external_tx_id' => $input['action_id'],
                ]);
                break;
        }

        return $tx_id;

    }

    public function update_fixed_data($game, $user_wealth, $amount)
    {
        if ($this->reject_games_category($game->game_id)) {
            return;
        }

        $bonus = $this->get_issued_user_bonus(0, 0, $game->game_id, [["user_id", "=", $game->user_id], ["status", "=", 2]]);

        if ($bonus) {

            $bonus_amount = $this->change_bonus_fixed_amount($bonus, $user_wealth->currency_id, $amount);

            if ($bonus_amount < 0) {
                $this->update_fixed_data($game, $user_wealth, $bonus_amount);
            }
        }

    }


    public function update_bonus_data($game, $user_wealth, $amount)
    {
        if ($this->reject_games_category($game->game_id)) {
            return;
        }

        $bonus = $this->get_issued_user_bonus(0, 0, $game->game_id, [["user_id", "=", $game->user_id], ["status", "=", 2]]);

        if ($bonus) {
            $this->change_bonus_wagered_amount($bonus->id, $user_wealth->currency_id, $amount);
        }

        if ($bonus && ($bonus->wagered + $amount) > $bonus->to_wager) {
            $this->update_bonus_issue($bonus->id, ['status' => 3]);
        }

    }

    public function show_error($text_code, $message, $http_code, $type, $input = [])
    {

        $headers = [];

        switch ($type) {
            case "onespinforwin":

                $response = [
                    "success" => false,
                    "code" => $text_code,
                    "message" => $message,
                    "balance" => isset($input['balance']) ? $input['balance'] : 0.00,
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "infingame":


                $response = [
                    $input['element'] => [
                        '_attributes' => [
                            'id' => $input['@attributes']['id'],
                            'result' => "fail",
                        ],
                        'error' => [
                            '_attributes' => [
                                'code' => $text_code,
                            ],
                            'msg' => $message,
                        ],
                    ]
                ];

                return $response;

                break;
            case "sportgames":

                $response = [
                    "message" => $message,
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "kagaming":

                $response = [
                    "status" => $message,
                    "statusCode" => $text_code,
                    "userMessage" => $message,
                    //"balance" => isset($input['balance'])?$input['balance']:0.00,
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "bgaming":

                $response = [
                    "success" => false,
                    "code" => $text_code,
                    "message" => $message,
                    "balance" => isset($input['balance']) ? $input['balance'] : 0.00,
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "evo":

                $response = new \stdClass();
                $response->status = 'error';
                $response->error = $input;

                break;
            case "onlyplay":

                $response = [
                    "success" => false,
                    "code" => $text_code,
                    "message" => $message,
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "slotty":

                $response = [
                    "status" => $http_code,
                    "error" => [
                        "code" => $text_code,
                        "message" => $message,
                        "display" => true,
                    ],
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "igrosoft":

                $response = [
                    "sessionId" => $input['sessionId'],
                    "status" => $text_code,
                    "message" => $message,
                    "amount" => "0.00",
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "mascot":

                $response = [
                    "jsonrpc" => "2.0",
                    "id" => $input['id'],
                    "error" => [
                        "code" => $text_code,
                        "message" => $message,
                    ],
                ];
                $response = array_merge($response);

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "outcomebet":

                $response = [
                    "jsonrpc" => "2.0",
                    "id" => $input['id'],
                    "error" => [
                        "code" => $text_code,
                        "message" => $message,
                    ],
                ];
                $response = array_merge($response);

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "spinomenal":

                $response = [
                    "ErrorCode" => $text_code,
                    "ErrorMessage" => $message,
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "ogs":

                $root = [
                    'rootElementName' => 'RSP',
                    '_attributes' => [
                        'rc' => $text_code,
                        'msg' => $message,
                        'request' => $input['request'],
                    ],
                ];

                $response = [
                    "APIVERSION" => $input['apiversion'],
                ];

                return ["array" => $response, "root" => $root];

                break;
        }

        return response()->json($response, $http_code, $headers);

    }

    public function show_response($body, $input, $http_code, $type)
    {

        $headers = [];

        switch ($type) {
            case "evo":

                $response = new \stdClass();
                $response->status = 'ok';
                $response->data = [
                    'balance' => '' . $body['balance'],
                    'currency' => $body['code'],
                ];

                break;
            case "mascot":

                $response = [
                    "jsonrpc" => "2.0",
                    "id" => $input['id'],
                    "result" => $body,
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
            case "outcomebet":

                $response = [
                    "jsonrpc" => "2.0",
                    "id" => $input['id'],
                    "result" => $body,
                ];

                $headers = ['Content-Length' => strlen(json_encode($response)), 'Accept' => 'application/json'];

                break;
        }

        return response()->json($response, $http_code, $headers);

    }

}
