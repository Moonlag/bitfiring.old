<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Events\UpdateBalance;
use App\Http\Resources\WinnersResource;
use App\Http\Traits\AuthTrait;
use App\Http\Traits\CryptoPaymentApi;
use App\Http\Traits\DBTrait;
use App\Http\Traits\SwapTrait;
use App\Models\Brands;
use App\Models\Campaign;
use App\Models\Countries;
use App\Models\PaymentSystem;
use App\Models\GroupPlayers;
use App\Models\Partner;
use App\Models\Payments;
use App\Models\Players;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;
use phpbb\template\twig\node\event;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Query\Expression;
use App\Http\Traits\EmailTrait;

class PlatformScreen extends Screen
{
    use CryptoPaymentApi;
    use AuthTrait;
    use DBTrait;
    use EmailTrait;
    use SwapTrait;

    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Dashboard';
    public $status = false;
//    /**
//     * Display header description.
//     *
//     * @var string
//     */
//    public $description = 'Welcome';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $palyers = Players::query()
            ->leftJoin('group_players', 'players.id', '=', 'group_players.user_id')
            ->whereIn('group_players.group_id', [18, 21])
            ->select('players.id')
            ->pluck('id');

        $deposits = Payments::query()
            ->whereHas('payment_system', function ($query) {
                $query->whereNotNull('id');
            })
            ->whereNotIn('payments.user_id', $palyers)
            ->select('currency.code', 'currency.name', 'payments.*')
            ->Join('currency', 'currency.id', '=', 'payments.currency_id')
            ->where('type_id', '=', 3)
            ->whereIn('status', [2, 5, 1])
            ->orderBy('payments.id', 'DESC')
            ->limit(22)
            ->get();

        $withdrawal = Payments::with('payment_system')
            ->whereHas('payment_system', function ($query) {
                $query->whereNotNull('id');
            })
            ->whereNotIn('payments.user_id', $palyers)
            ->select('currency.code', 'currency.name', 'payments.*')
            ->Join('currency', 'currency.id', '=', 'payments.currency_id')
            ->where('type_id', '=', 4)
            ->orderBy('payments.id', 'DESC')
            ->limit(20)
            ->get();

        $currency = PaymentSystem::query()
            ->join('currency', 'payment_system.currency_id', '=', 'currency.id')
            ->select('payment_system.*', 'currency.rate', 'currency.code')
            ->whereIn('payment_system.id', [3, 10, 11, 12, 13])
            ->orderBy('currency.id', 'ASC')
            ->get();

        $doge = Http::get('https://dogechain.info/api/v1/address/received/D5hPmBvbL7Mzo1Hsp5VBHpoPQ1tyNmhbyJ');
        $btc = Http::get('https://blockchain.info/q/addressbalance/bc1qefp9ensf5g628s5y28v4pm6h6nqvljfntsqn0z');


        $this->status = isset($this->onStatus()['success']);

        return [
            'status' => $this->status,
            'currency' => $currency,
            'deposits' => $deposits,
            'withdrawal' => $withdrawal,
            'balance' => $this->onBalance(),
            'doge' => $doge->json(),
            'btc' => ['balance' => $btc->json() / 100000000]
        ];
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [

        ];
    }

    /**
     * Views.
     *
     * @return Layout[]
     */
    public function layout(): array
    {

        return [
            Layout::view('orchid.dashboard'),
            Layout::modal('Approve?', [
                Layout::rows([
                    Input::make('code')->title('2fa code'),
                ])
            ]),
            Layout::modal('Cancel?', [
                Layout::rows([
                    Input::make('code')->title('2fa code'),
                ])
            ]),
        ];
    }

    public function withdrawal_cancel(Payments $payments, Request $request)
    {
        $input = $request->all();

        $confirmed = false;
        if ($input['code']) {
            $confirmed = $request->user()->confirmTwoFactorAuth($input['code']);
        }

        if (!$confirmed) {
            return back()->withErrors('Invalid Two Factor Authentication code');
        }

        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        $payment = Payments::find($payments->id);

        if (!$this->check_withdraw_cancel($payment->user_id, $input['id'])) {
            $validator->errors()->add('withdraw', 'withdrawals completed');
        }

        if (count($validator->errors()->all()) == 0 && $validator->passes()) {
            $payment->status = 3;
            $payment->finished_at = Carbon::now();
            $payment->save();

            $bonus_part = 0;

            $this->insert_transaction([
                'amount' => -$payment->amount,
                'bonus_part' => $bonus_part,
                'currency_id' => $payment->currency_id,
                'reference_id' => $payment->id,
                'wallet_id' => $payment->wallet_id,
                'player_id' => $payment->user_id,
                'type_id' => 5,
                'reference_type_id' => 5,
            ]);

            $this->update_wallet(['balance' => new Expression('balance + ' . abs($payment->amount))], $payment->wallet_id);
            if($payment->currency_id != 14){
                $this->handler_swap($payment->user_id, $payment->currency_id, 14, abs($payment->amount));
            }
            $player = \App\Models\Players::find($payment->user_id);
            $this->cashout_canceled_email($player->email);
            event(new UpdateBalance($payment->user_id));
            Toast::info(__('Success'));
        }
    }

    public function onBalance()
    {
        $email = config('cryptopaymentapi.email');
        $password = config('cryptopaymentapi.password');
        $device_name = config('app.name');

        $this->csrf_auth();


        $token = $this->get_token($email, $password, $device_name);

        if (!isset($token['token'])) {
            throw new \Exception('Token not found');
        }


        $body = $this->balance($token['token']);
        
        return $body;
    }

    public function onStatus()
    {
        $email = config('cryptopaymentapi.email');
        $password = config('cryptopaymentapi.password');
        $device_name = config('app.name');

        $this->csrf_auth();


        $token = $this->get_token($email, $password, $device_name);

        if (!isset($token['token'])) {
            throw new \Exception('Token not found');
        }


        $body = $this->status($token['token']);

        return $body;
    }

    public function withdrawal_approve(Payments $payments, Request $request)
    {

        $input = $request->all();
        $confirmed = false;
        if ($input['code']) {
            $confirmed = $request->user()->confirmTwoFactorAuth($input['code']);
        }

        if (!$confirmed) {
            return back()->withErrors('Invalid Two Factor Authentication code');
        }

        try {
            if ($payments->status === 2) {
                $payment = \App\Models\Payments::find($payments->id);
                $payment->status = 1;
                $payment->save();

                $email = config('cryptopaymentapi.email');
                $password = config('cryptopaymentapi.password');
                $device_name = config('app.name');
                $this->csrf_auth();
                $token = $this->get_token($email, $password, $device_name);
                if (!isset($token['token'])) {
                    throw new \Exception('Token not found');
                }

                $amount = abs((float)$payments->amount) - (float)$payments->network_fee;
                $source_address = $payments->source_address;

                switch ($payments->payment_system_id) {
                    case 3:
                    {
                        $this->transfer_DOGE($token['token'], $amount, $source_address, $payments->id);
                        break;
                    }
                    case 10:
                    {
                        $this->transfer_ETH($token['token'], $amount, $source_address, $payments->id);
                        break;
                    }
                    case 11:
                    {
                        $this->transfer_BTC($token['token'], $amount, $source_address, $payments->id);
                        break;
                    }
                    case 12:
                    {
                        $this->transfer_usdtTRC20($token['token'], $amount, $source_address, $payments->id);
                        break;
                    }
                    case 13:
                    {
                        $this->transfer_usdtERC20($token['token'], $amount, $source_address, $payments->id);
                        break;
                    }
                    default:
                        throw new \Exception('Payment System not found');
                }
                $player = \App\Models\Players::find($payment->user_id);
                $this->cashout_canceled_email($player->email);

                Toast::info(__('Success'));
            }
        } catch (\Exception $e) {
            DB::table('payments')->where('id', $payments->id)->update(['status' => 2]);
            Toast::error(__('Error'));
        }
    }
}
