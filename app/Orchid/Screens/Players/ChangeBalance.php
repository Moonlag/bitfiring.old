<?php

namespace App\Orchid\Screens\Players;

use App\Http\Traits\AffiliateTrait;
use App\Http\Traits\SwapTrait;
use App\Models\Comments;
use App\Models\Currency;
use App\Models\Payments;
use App\Models\PaymentSystem;
use App\Models\Wallets;
use App\Traits\ChangesTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Radio;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ChangeBalance extends Screen
{
    use ChangesTrait, AffiliateTrait, SwapTrait;

    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'ChangeBalance';

    public $section_id = 2;
    public $id;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'ChangeBalance';

    public $permission = [
        'platform.players.balance'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
        }

        return ['collapse' => [
            [
                'title' => 'Balance Correction',
                'form' => [
                    Radio::make('balance[type_id]')
                        ->value(1)
                        ->formmethod('balance_correction')
                        ->placeholder('Add'),
                    Radio::make('balance[type_id]')
                        ->value(2)
                        ->formmethod('balance_correction')
                        ->placeholder('Subtract'),
                    Select::make('balance[currency]')
                        ->empty('No Select', 0)
                        ->fromQuery(Wallets::query()->leftJoin('currency', 'wallets.currency_id', '=', 'currency.id')
                            ->where('wallets.user_id', $this->id)->select('wallets.id', DB::Raw("CONCAT(currency.code, ' - ', wallets.balance) as code")), 'code')
                        ->title('Currency'),
                    Input::make('balance[amount]')
                        ->title('Amount'),
                    TextArea::make('balance[comment]')
                        ->rows(5)
                        ->title('Add a comment'),
                    Input::make('balance[auth_code]')
                        ->title('Authentication code'),
                ],
                'action' => Button::make('Make transaction')
                    ->class('btn btn-rounded btn-primary')
                    ->method('balance_correction')
                    ->parameters(['id' => $this->id]),
            ],
            [
                'title' => 'Payments and Gifts',
                'form' => [
                    Radio::make('payments[type_id]')
                        ->value(3)
                        ->placeholder('Deposit'),
                    Radio::make('payments[type_id]')
                        ->value(4)
                        ->placeholder('Cashout'),
                    Radio::make('payments[type_id]')
                        ->value(5)
                        ->placeholder('Gifts')
                        ->cansee(false),
                    Input::make('payments[amount]')
                        ->title('Amount'),
                    Select::make('payments[payment_system]')
                        ->empty('No Select', 0)
                        ->fromQuery(PaymentSystem::query()->where('active', '=', 1)->select('name', 'id'), 'name')
                        ->title('Payment system'),
                    TextArea::make('payments[comment]')
                        ->rows(5)
                        ->title('Add a comment'),
                    Input::make('payments[auth_code]')
                        ->title('Authentication code'),

                ],
                'action' => Button::make('Make transaction')
                    ->class('btn btn-rounded btn-primary')
                    ->method('payment_gift')
                    ->parameters(['id' => $this->id]),
            ]
        ]];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make('Return')
                ->class('btn btn-outline-secondary mb-2')
                ->icon('left')
                ->route('platform.players.profile', $this->id)];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::view('orchid.collapse'),
        ];
    }

    public function balance_correction(Request $request, \App\Models\Players $model)
    {

        $validator = Validator::make($request->balance, [
            'type_id' => 'required',
            'currency' => 'required|integer|not_in:0',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            \Orchid\Support\Facades\Toast::error("Validation Error, " . array_keys($validator->failed())[0] . " ");
        } else {

            $confirmed = false;
            if($request->balance['auth_code']){
                $confirmed = $request->user()->confirmTwoFactorAuth($request->balance['auth_code']);
            }

            if (!$confirmed) {
                return back()->withErrors('Invalid Two Factor Authentication code');
            }

            $currency_id = $this->wallet_correction($request->balance['type_id'], $request->balance['currency'], $request->balance['amount']);
            $payment = $this->payment_add($request->balance['type_id'], $request->balance['currency'], $request->user()->id, $request->id, $currency_id, $request->balance['amount'], $model->email);
            $payment_id = $payment->id;
            $this->transaction_add($request->balance['type_id'], $request->balance['currency'], $request->id, $currency_id, $request->balance['amount'], $payment_id, $request->session()->getId());

            if($currency_id !== 14){
                $this->handler_swap($request->id, $currency_id, 14, (float)$request->balance['amount']);
            }

            if (!empty($request->balance['comment'])) {
                $this->add_comment($request->user()->id, $payment_id, $request->balance['comment']);
            }
        }
    }

    public function payment_gift(Request $request, \App\Models\Players $model)
    {

        $validator = Validator::make($request->payments, [
            'type_id' => 'required',
            'payment_system' => 'required|integer|not_in:0',
            'amount' => 'required'
        ]);
        if ($validator->fails()) {
            \Orchid\Support\Facades\Toast::error("Validation Error, " . array_keys($validator->failed())[0] . " ");
        } else {
            $confirmed = false;
            if($request->payments['auth_code']){
                $confirmed = $request->user()->confirmTwoFactorAuth($request->payments['auth_code']);
            }

            if (!$confirmed) {
                return back()->withInput($request->all())->withErrors('Invalid Two Factor Authentication code');
            }

            $payment_system = \App\Models\PaymentSystem::find($request->payments['payment_system']);

            $wallet = \App\Models\Wallets::query()->where([
                ['user_id', '=', $request->id],
                ['currency_id', '=', $payment_system->currency_id]
            ])->first();

            if (empty($wallet)) {
                $wallet = new \App\Models\Wallets();
                $wallet->primary = 0;
                $wallet->currency_id = $payment_system->currency_id;
                $wallet->user_id = $request->id;
                $wallet->balance = 0;
                $wallet->save();
            }

            $currency_id = $this->wallet_correction($request->payments['type_id'], $wallet->id, $request->payments['amount']);
            $payment =  $this->payment_add($request->payments['type_id'], $wallet->id, $request->user()->id, $request->id, $currency_id, $request->payments['amount'], $model->email, $request->payments['payment_system']);
            $payment_id = $payment->id;
            $this->transaction_add($request->payments['type_id'], $wallet->id, $request->id, $currency_id, $request->payments['amount'], $payment_id, $request->session()->getId());

            if($currency_id !== 14){
                $this->handler_swap($request->id, $currency_id, 14, (float)$request->payments['amount']);
            }

            if($request->payments['type_id'] === '3' && !!$model->affiliate_aid){
                $amount =  $payment->amount * $payment->currency->rate;
                $this->affiliate_deposit($model->affiliate_aid, $model->id, $amount, $payment_id,);
            }

            if (!empty($request->payments['comment'])) {
                $this->add_comment($request->user()->id, $payment_id, $request->payments['comment']);
            }
        }
    }

    public function wallet_correction($type, $wallet_id, $amount)
    {
        $balance = Wallets::query()->where('id', '=', $wallet_id)->select('balance', 'currency_id as currency')->first()->toArray();
        if ($type === '1' || $type === '3' || $type === '5') {
            if ($balance['balance']) {
                $balance['balance'] = (float)$balance['balance'] + (float)$amount;
            }
        }
        if ($type === '2' || $type === '4') {
            if ($balance['balance']) {
                $balance['balance'] = (float)$balance['balance'] - (float)$amount;
            }
        }

        Wallets::query()->where('id', '=', $wallet_id)->update(['balance' => $balance['balance']]);
        \Orchid\Support\Facades\Toast::success('Success, Balance Correction');
        return $balance['currency'];
    }

    public function payment_add($type, $wallet, $staff, $user_id, $currency_id, $amount, $user_email, $payment_system = null)
    {
        if ($type === '2' || $type === '4') {
            $amount = -(float)$amount;
        }

        $currency = \App\Models\Currency::find($currency_id);

        $data = [
            'staff_id' => $staff,
            'wallet_id' => $wallet,
            'user_id' => $user_id,
            'currency_id' => $currency_id,
            'amount' => $amount,
            'amount_usd' => $amount / $currency->rate,
            'type_id' => $type,
            'status' => 1,
            'email' => $user_email
        ];

        if ($payment_system) {
            $fee = PaymentSystem::query()->where('id', $payment_system)->select('fee')->first()->fee ?? 0;
            $data = array_merge($data, ['payment_system_id' => $payment_system, 'network_fee' => abs($amount) * ($fee / 100)]);
        }
        return Payments::create($data);
    }

    public function transaction_add($type, $wallet, $player_id, $currency_id, $amount, $payment_id, $session)
    {
        if ($type === '2' || $type === '4') {
            $amount = -(float)$amount;
        }

        $currency = \App\Models\Currency::find($currency_id);

        \App\Models\Transactions::query()->insert([
            'wallet_id' => $wallet,
            'bonus_part' => 0,
            'reference_id' => $payment_id,
            'reference_type_id' => 1,
            'player_id' => $player_id,
            'token' => $session,
            'currency_id' => $currency_id,
            'amount_usd' => $amount / $currency->rate,
            'amount' => $amount,
            'type_id' => $type,
        ]);
    }

    public function add_comment($staff_id, $payment_id, $comment)
    {
        $args = [
            'staff_id' => $staff_id,
            'user_id' => $payment_id,
            'created_at' => Carbon::now()->format('Y-m-d H:m:s'),
            'section_id' => $this->section_id,
            'comment' => $comment
        ];
        Comments::query()->insert($args);
    }

    public function set_changes($request, \App\Models\Players $model)
    {
        $changes = [];
        foreach (array_keys($request) as $key) {
            if ($request[$key] != $model->$key) {
                $changes[] = [
                    'request_name' => $key,
                    'request' => json_encode([$request[$key]]),
                    'user_id' => $model->id,
                ];
            }
        }
        if (!empty($changes)) {
            $this->prepare($model->id);
            $this->insert_changes($changes);
        }
    }

}
