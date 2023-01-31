<?php

namespace App\Http\Controllers;

use App\Events\NewDeposit;
use App\Events\SwapBalance;
use App\Http\Traits\AffiliateTrait;
use App\Http\Traits\AuthTrait;
use App\Http\Traits\DBTrait;
use App\Http\Traits\EventTrait;
use App\Http\Traits\EmailTrait;
use App\Models\PaymentSystem;
use App\Models\Players;
use App\Models\Wallets;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Validator;

class DepositController extends Controller
{

    use AuthTrait;
    use DBTrait;
    use AffiliateTrait;
    use EventTrait;
    use EmailTrait;

    public function ajax_deposit(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

        $input = $request->all();

        $validator = Validator::make($input, [
            'total' => 'required_without:deposit_sum',
            'deposit_sum' => 'required_without:amount',
            "payment_system_id" => 'required'
        ]);

        $amount = $input['total'] > 0 ? $input['total'] : $input['deposit_sum'];

        if (!$amount) exit;

        $payment_system = \App\Models\PaymentSystem::find($input['payment_system_id']);

        if ($validator->passes() && isset($payment_system->id)) {
            $validator->errors()->add('payment_system', 'The payment system not found');
        }

        if ($validator->passes() && $input['total'] <= $payment_system->minimum && $input['total'] >= $payment_system->maximum) {
            $validator->errors()->add('payment_system', 'The payment minimum or maximum out of range');
        }

        if ($validator->passes()) {

            $wallet = \App\Models\Wallets::query()
                ->where('user_id', $this->common_data['user']->id)
                ->where('currency_id', '=', $payment_system->currency_id)
                ->first();

            if (empty($wallet)) {
                $wallet = new \App\Models\Wallets();
                $wallet->primary = 0;
                $wallet->currency_id = $payment_system->currency_id;
                $wallet->user_id = $this->common_data['user']->id;
                $wallet->balance = 0;
                $wallet->save();
            }

            if ($payment_system->currency_id != $wallet->currency_id || !$wallet) {
                return response()->json(['errors' => 'Unknown Error']);
            }

            $deposit = $this->check_deposit([
                ['user_id', '=', $this->common_data['user']->id],
                ['payment_system_id', '=', $payment_system->id],
                ['amount', '=', $amount]
            ]);

            $bonus_part = 0;
            $freespin = null;
            $bonus_option_id = null;
            if (!empty($input['bonuses'])) {
                $bonus_amount = \App\Models\Bonuses::find($input['bonuses']['id']);
                $percentage = (float)$bonus_amount->amount / 100;
                $bonus_part = (float)$amount * $percentage;
                if ($bonus_amount->freespin_id) {
                    $freespin = \App\Models\FreespinBonusModel::find($bonus_amount->freespin_id);
                }

                if (isset($input['bonuses']['bonus_option_id'])) {
                    $bonus_option_id = $input['bonuses']['bonus_option_id'];
                    $this->update_bonus_options([['player_id', '=', $this->common_data['user']->id], ['id', '=', $bonus_option_id]], 0);
                }
            }

            $created_at = Carbon::now();

            if (isset($deposit->id)) {
                $payment_id = $deposit->id;
                $address = $this->select_wallet_temp([['wallet', '=', $deposit->source_address]]);
            } else {
                $address = $this->get_wallet_temp($payment_system->id, $created_at);

                if (!isset($address->id)) {
                    return response()->json(['errors' => 'No isset Address']);
                }

                $payment_id = $this->insert_payment([
                    'email' => $this->common_data['user']->email,
                    'user_id' => $this->common_data['user']->id,
                    'wallet_id' => $wallet->id,
                    'amount' => $amount,
                    'amount_usd' => $input['amount'],
                    'requested' => $amount,
                    'type_id' => 3,
                    'status' => $request->get('cc', false) ? 5 : 2,
                    'source_address' => $address->wallet,
                    'payment_system_id' => $payment_system->id,
                    'created_at' => $created_at,
                    'currency_id' => $payment_system->currency_id,
                    'player_action' => 1,
                    'network_fee' => 0.79,
                ]);

                $this->insert_transaction([
                    'amount' => $amount,
                    'bonus_part' => $bonus_part,
                    'currency_id' => $payment_system->currency_id,
                    'reference_id' => $payment_id,
                    'wallet_id' => $wallet->id,
                    'player_id' => $this->common_data['user']->id,
                    'type_id' => 1,
                    'reference_type_id' => 5,
                    'amount_usd' => $input['amount']
                ]);

                $this->deposit_made($this->common_data['user']);
            }

            $svg = QrCode::format('svg')->style('dot')->gradient(99, 158, 255, 196, 47, 237, 'diagonal')->backgroundColor(255, 255, 255, 0)->size(260)->BTC($address->wallet, $amount);
            event(new NewDeposit($this->common_data['user']->id, $address, $amount, $payment_id, $wallet->id, $payment_system->id, $address->id, $bonus_amount ?? null, $payment_system->currency_id, $this->common_data['user']->affiliate_aid, $input['amount'], $freespin, $bonus_option_id));
            return response()->json(['success' => 1, 'qrcode' => $svg->toHtml(), 'address' => $address, 'payment' => $payment_id, 'amount' => $amount]);
        }
        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function ajax_cancel_deposit(Request $request)
    {
        $this->common_data = \Request::get('common_data');

        if (isset($this->common_data['user']->id)) {
            $input = $request->all();
            $validator = Validator::make($input, [
                "payment_id" => 'required'
            ]);

            if ($validator->passes()) {
                $this->update_payment($input['payment_id'], ['status' => 5]);
                return response()->json(['success' => 1]);
            }
            return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
        }
    }

    public function ajax_reject_deposit(Request $request)
    {
        $input = $request->all();
        $player = Players::find($input['user_id']);

        $payment = \App\Models\Payments::find($input['payment_id']);
        if (isset($payment->id)) {
            $this->send_email('deposit_issue', $player->email, ['email' => $player->email]);
        }
    }

    public function ajax_success_deposit(Request $request)
    {
        $input = $request->all();
        $player = Players::find($input['user_id']);

        if (isset($player->id)) {
            $this->deposit_made($player);
        }

        if (isset($player->id)) {
            $payment = \App\Models\Payments::find($input['payment_id']);
            if(isset($payment->id)){
                $this->send_email('deposit_received', $player->email, ['email' => $player->email, 'amount' => $payment->amount . ' ' . $payment->payment_system->name]);
            }

            $bonus = \App\Models\BonusIssue::find($input['bonus_id']);
            if (isset($bonus->id)){
                $this->send_email('bonus_received', $player->email, ['email' => $player->email]);
            }

            if ((float)$input['amount'] >= 500) {
                $this->send_email('deposit_more', 'magistriam@gmail.com', ['email' => $player->email, 'id' => $player->id, 'amount' => $input['amount']]);;
            }
        }
    }

    public
    function ajax_status_deposit(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        if (isset($this->common_data['user']->id)) {
            $payment = $this->get_last_payment($this->common_data['user']->id, 2, 3);
            if (isset($payment->id)) {
                $svg = QrCode::format('svg')->style('dot')->gradient(99, 158, 255, 196, 47, 237, 'diagonal')->backgroundColor(255, 255, 255, 0)->size(260)->BTC($payment->source_address, $payment->amount);
                return response()->json(['success' => 1, 'qrcode' => $svg->toHtml(), 'address' => $payment->source_address, 'payment' => $payment->id, 'payment_system_id' => $payment->payment_system_id]);
            }
            return response()->json(['success' => 1]);
        }
        return response()->json(['errors' => 1]);
    }

    public
    function ajax_swap(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        if (isset($this->common_data['user']->id)) {
            $input = $request->all();

            $validator = Validator::make($input, [
                'from' => 'required',
                'to' => 'required',
                'from_amount' => 'required',
                'to_amount' => 'required',
            ]);

            if (!$validator->passes()) {
                return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
            }

            if (!$this->check_swap_balance($this->common_data['user']->id, $input['from'], $input['from_amount'])) {
                $validator->errors()->add('amount', 'The amount exceeds your wallet limit');
            }

            $wallet_from = $this->wallet_swap($this->common_data['user']->id, $input['from']);
            $wallet_to = $this->wallet_swap($this->common_data['user']->id, $input['to']);

            $swap_id = $this->swaps($input['from'], $input['to'], $input['from_amount'], $input['to_amount']);

            $this->transactions_swap($this->common_data['user']->id, $input['from'], -1 * $input['from_amount'], 1, $wallet_from->id, $swap_id);
            $new_balance_from = (float)$wallet_from->balance - $input['from_amount'];

            $this->transactions_swap($this->common_data['user']->id, $input['to'], $input['to_amount'], 2, $wallet_to->id, $swap_id);
            $new_balance_to = (float)$wallet_to->balance + $input['to_amount'];

            $this->update_wallet([
                'balance' => $new_balance_from
            ], $wallet_from->id);

            $this->update_wallet([
                'balance' => $new_balance_to
            ], $wallet_to->id);

            event(new SwapBalance($this->common_data['user']->id, [
                'wallet_id' => $wallet_from->id,
                'balance' => $new_balance_from
            ], [
                'wallet_id' => $wallet_to->id,
                'balance' => $new_balance_to
            ]));

            return response()->json(['success' => 1]);
        }
        return response()->json(['error' => 1], 404);
    }
}
