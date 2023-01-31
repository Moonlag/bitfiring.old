<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Currency;
use App\Models\Players;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallets;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class IncomeStatementReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'IncomeStatementReport';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'IncomeStatementReport';

    public $permission = [
        'platform.finance.income-statement-report'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $request['date_range'] = isset($request['date_range']) ? $request['date_range'] : [
            'start' => Carbon::now()->startOfMonth()->toDateString(),
            'end' => Carbon::now()->toDateString(),
        ];

        $currency = Currency::query()
            ->join('currency_exchange', 'currency.id', '=', 'currency_exchange.currency_id')
            ->where('currency.excluded', '!=', 1)
            ->select(DB::raw('(SELECT code FROM currency WHERE id = currency_exchange.currency_to) as currency_to'), 'currency.code', 'currency_exchange.rate', 'currency_exchange.created_at')
            ->groupBy('currency.id')
            ->get();

        $currency_thead = Currency::query()->where('currency.excluded', '!=', 1)->get()->toArray();

        $palyers = Players::query()
            ->leftJoin('group_players', 'players.id', '=', 'group_players.user_id')
            ->whereIn('group_players.group_id', [18, 21, 16])
            ->select('players.id')
            ->get();

        $transaction = \App\Models\Transactions::query()
            ->leftJoin('currency', 'transactions.currency_id', '=', 'currency.id')
            ->leftJoin('games_bets', 'transactions.reference_id', '=', 'games_bets.id')
            ->leftJoin('games', 'games_bets.game_id', '=', 'games.id')
            ->leftJoin('currency_exchange', 'currency.id', '=', 'currency_exchange.currency_id')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->when(request('date_range', false), function ($query, $date_range) {
                $query->whereBetween('transactions.created_at', [$date_range['start'], $date_range['end']]);
            })
            ->leftJoin('payments', function ($join) {
                $join->on('transactions.reference_id', '=', 'payments.id');
            })
            ->leftJoin('payment_system', 'payments.payment_system_id', '=', 'payment_system.id')
            ->whereNotIn('transactions.player_id', $palyers->pluck('id'))
            ->select('currency.id as currency_id',
                'transactions.amount',
                'transactions.reference_type_id',
                'payments.type_id',
                'currency_exchange.rate',
                'game_provider.fee as provider_fee',
                'payment_system.fee as network_fee',
                'payments.network_fee as fuel')
            ->get()
            ->toArray();


        $wallet = Wallets::query()
            ->leftJoin('currency_exchange', 'wallets.currency_id', '=', 'currency_exchange.currency_id')
            ->get()
            ->toArray();

        $total_revenue = [
            'set_bets' => 'Bets',
            'set_win' => 'Wins',
            'set_ggr' => 'GGR',
            'set_extra' => 'Extra',
            'set_bc_positive' => 'Balance Correction',
            'set_bonuses' => 'Bonuses',
            'set_third_party_fee' => 'Third Party Fee',
            'set_fuel' => 'Fuel',
            'set_fuel_fee' => 'Fuel Fee',
        ];
        $total_revenue = $this->set_data($total_revenue, $currency_thead, $transaction);

//        $due_to_pay = [
//            'set_bonuses' => 'Bonuses',
//            'set_third_party_fee' => 'Third Party Fee',
//            'set_payment_fee' => 'Payment Processors Fee',
//            'set_platform_provider_fee' => 'Platform Provider Fee',
//            'set_jackpot_result' => 'Jackpot Result',
//            'set_affiliate_payments' => 'Affiliate Payments',
//            'set_bc_positive' => 'Positive Balance Correction',
//            'set_gifts' => 'Gifts',
//            'set_due_to_pay_to_licensee' => 'Due to Pay to Licensee'
//        ];
//
//        $due_to_pay = array_merge(['set_total_revenue' => $total_revenue['set_total_revenue']], $this->set_data($due_to_pay, $currency_thead, $transaction, ['set_total_revenue' => $total_revenue['set_total_revenue']]));

//        $amount_to_distribution = ['set_amount_to_distribution' => 'Amount to Distribution'];
//        $amount_to_distribution = array_merge([
//            'set_third_party_fee' => $due_to_pay['set_third_party_fee'],
//            'set_jackpot_result' => $due_to_pay['set_jackpot_result'],
//            'set_platform_provider_fee' => $due_to_pay['set_platform_provider_fee'],
//            'set_due_to_pay_to_licensee' => $due_to_pay['set_due_to_pay_to_licensee']
//        ], $this->set_data($amount_to_distribution, $currency_thead, $transaction, [
//            'set_third_party_fee' => $due_to_pay['set_third_party_fee'],
//            'set_jackpot_result' => $due_to_pay['set_jackpot_result'],
//            'set_platform_provider_fee' => $due_to_pay['set_platform_provider_fee'],
//            'set_due_to_pay_to_licensee' => $due_to_pay['set_due_to_pay_to_licensee']]));
//
//        $player_balance_reserve_changes = ['set_player_balance' => 'Player Balance',
//            'set_player_bonus_balance' => 'Player Bonus Balance',
//            'set_player_real_balance' => 'Player Real Balance',
//            'set_bankroll' => 'Bankroll Should Be',
//            'set_existing' => 'Existing Bankroll',
//            'set_balance_reserve_change' => 'Player Balance Reserve Change',
//            'set_bank_transfers' => 'Bank Transfers'
//        ];
//        $player_balance_reserve_changes = $this->set_data($player_balance_reserve_changes, $currency_thead, $wallet);

        $start  = Carbon::parse('2021-09-01 00:00:00');
        $end    = Carbon::now();
        $diff = $end->diffInMonths($start);

        $groups = [
            DateRange::make('date_range')
                ->title('Date Range')
                ->value($request->date_range),
        ];

        $range = [];

        for ($i = 0; $i <= $diff; $i++){
            $copy = $start->copy();
            $range[] = Button::make($copy->monthName . ' ' . $copy->yearIso)
                ->class('btn link-primary btn-sm')
                ->parameters(
                    [
                        'range[start]' => $copy->startOfMonth()->toDateString(),
                        'range[end]' => $copy->endOfMonth()->toDateString()
                    ]
                )
                ->method('apply_filter');

            $start->addMonth();
        }

        $full[] = Group::make($range)->autoWidth()->alignCenter()->render();

        return [
            'table' => $currency,
            'currency_thead' => $currency_thead,
            'total_revenue' => [
                'title' => 'Total Revenue = GGR + Casino Payments Fee + Extra + Negative Balance Correction',
                'table' => $total_revenue
            ],
//            'due_to_pay' => [
//                'title' => 'Due to Pay to Licensee = Total Revenue - Bonuses - Third Party Fee - Payments Processors Fee - Platform Provider Fee - Jackpot Result - Affiliate Payments - Gifts - Positive Balance Correction',
//                'table' => $due_to_pay
//            ],
//            'amount_to_distribution' => [
//                'title' => 'Amount to Distribution = Third Party Fee + Jackpot Result + Platform Provider Fee + Due to Pay to Licensee',
//                'table' => $amount_to_distribution
//            ],
//            'player_balance_reserve_changes' => [
//                'title' => 'Player Balance Reserve Changes = Bankroll Should Be - Existing Bankroll',
//                'table' => $player_balance_reserve_changes
//            ],
            'filter' => [
                'title' => 'Filter',
                'group' => $groups,
                'full' => $full,
                'action' => [
                    Button::make('Filter')
                        ->method('apply_filter')
                        ->vertical()
                        ->icon('filter')
                        ->class('btn btn-primary btn-sm btn-block'),

                    Button::make('Clear')
                        ->icon('refresh')
                        ->method('clear_filter')
                        ->class('btn btn-sm btn-dark')
                        ->vertical(),
                ]
            ],
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::wrapper('orchid.wrapper-col2', [
                'col_left' => [
                    Layout::view('orchid.filter'),
                ],
                'col_right' => [
                    Layout::view('orchid.players.scope-countres'),
                ],
            ]),
            Layout::view('orchid.finance.income')
        ];
    }

    public function set_data($args, $currency, $transaction, $other_args = null)
    {

        $set_result = [];
        foreach ($args as $key => $value) {
            if ($key === 'set_total_revenue') {
                $args[$key] = array_map(function ($negative, $ggr) use ($value) {
                    return !is_string($negative) ? $negative + $ggr : $value;
                }, $args['set_bc_negative'], $args['set_ggr']);
                continue;
            }

            if ($key === 'set_due_to_pay_to_licensee') {
                $args[$key] = array_map(function ($total_revenue, $bonuses, $party_fee, $payment_fee, $provider_fee, $jackpot_result, $affiliate_payments, $gifts, $bc_positive) use ($value) {
                    return !is_string($total_revenue) ? $total_revenue - $party_fee - $bonuses - $payment_fee - $provider_fee - $jackpot_result - $affiliate_payments - $gifts - $bc_positive : $value;
                }, $other_args['set_total_revenue'], $args['set_bonuses'], $args['set_third_party_fee'], $args['set_payment_fee'],
                    $args['set_platform_provider_fee'], $args['set_jackpot_result'], $args['set_affiliate_payments'], $args['set_gifts'], $args['set_bc_positive']);
                continue;
            }

            if ($key === 'set_amount_to_distribution') {
                $args[$key] = array_map(function ($party_fee, $jackpot_result, $provider_fee, $due_to_pay) use ($value) {
                    return !is_string($party_fee) ? $party_fee + $jackpot_result + $provider_fee + $due_to_pay : $value;
                }, $other_args['set_third_party_fee'], $other_args['set_jackpot_result'], $other_args['set_platform_provider_fee'], $other_args['set_due_to_pay_to_licensee']);
                continue;
            }


            $result = array_map(function ($currency) use ($key, $transaction) {
                return array_values($this->$key($transaction, $currency));
            }, $currency);


            $total = usdt_helper(array_sum(array_column($result, 1)), 'USDT');

            if(empty($set_result)){
                $set_result = array_column($result, 0);
            }else{
                foreach ($set_result as $k => $v){
                    $set_result[$k] += array_column($result, 0)[$k];
                }
            }

            $args[$key] = array_merge(['title' => $value], array_column($result, 0), ['total' => $total]);
        }
        $args['set_result'] = array_merge(['title' => 'Result'], $set_result, ['total' => array_sum(array_map(function ($v){
            return $v['total'];
        }, $args))]);
        return $args;
    }


    public function set_bets($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['amount'] < 0 && $v['currency_id'] === $currency['id'] && $v['reference_type_id'] === 4;
        }, 1);

        $amount = array_column($data, 'amount');
        $rate = array_column($data, 'rate');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total'] ?? 0.00];
    }

    public function set_win($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['amount'] > 0 && $v['currency_id'] === $currency['id'] && $v['reference_type_id'] === 4;
        }, 1);

        $amount = array_column($data, 'amount');
        $rate = array_column($data, 'rate');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total'] ?? 0.00];
    }

    public function set_extra($transaction, $currency)
    {
        return [0.00, 0.00, 0.00];
    }

    public function set_ggr($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'] && $v['reference_type_id'] === 4;
        }, 1);

        $amount = array_column($data, 'amount');
        $rate = array_column($data, 'rate');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total'] ?? 0.00];
    }

    public function set_bc_negative($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'] && $v['reference_type_id'] === 1 && ($v['type_id'] === 2 || $v['type_id'] === 4);
        }, 1);

        $amount = array_column($data, 'amount');
        $rate = array_column($data, 'rate');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'] ?? 0.00, $value['total'] ?? 0.00];
    }

    public function set_bc_positive($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'] && $v['reference_type_id'] === 1 && ($v['type_id'] === 1 || $v['type_id'] === 3 || $v['type_id'] === 5 || $v['type_id'] === 6 || $v['type_id'] === 7 || $v['type_id'] === 8);
        }, 1);

        $amount = array_column($data, 'amount');
        $rate = array_column($data, 'rate');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_fuel($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'] && $v['reference_type_id'] === 5 && $v['type_id'] === 4;
        }, 1);

        $amount = array_column($data, 'fuel');
        $rate = array_column($data, 'rate');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_fuel_fee($transaction, $currency)
    {

        $amount = [15 * (float)$currency['rate']];
        $rate = 0;

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_gifts($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'] && $v['type_id'] === 5 && $v['reference_type_id'] === 1;
        }, 1);


        $amount = array_column($data, 'amount');
        $rate = array_column($data, 'rate');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_bonuses($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'] && $v['reference_type_id'] === 2;
        }, 1);

        $amount = array_column($data, 'amount');
        $rate = array_column($data, 'rate');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_payment_fee($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'] && $v['reference_type_id'] === 1 || $v['reference_type_id'] === 5;
        }, 1);

        $amount = array_column($data, 'amount');
        $network_fee = array_column($data, 'network_fee');
        $rate = array_column($data, 'rate');

        $amount = array_map(function ($amount, $network_fee) {
            return $amount * ((int)$network_fee / 100);
        }, $amount, $network_fee);

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_jackpot_result($transaction, $currency)
    {
        return [0, 0, 0];
    }

    public function set_platform_provider_fee($transaction, $currency)
    {
        return [0, 0, 0];
    }

    public function set_affiliate_payments($transaction, $currency)
    {
        return [0, 0, 0];
    }

    public function set_third_party_fee($transaction, $currency)
    {
        $data = array_filter($transaction, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'] && $v['reference_type_id'] === 4;
        }, 1);

        $amount = array_column($data, 'amount');
        $provider_fee = array_column($data, 'provider_fee');
        $rate = array_column($data, 'rate');

        $amount = array_map(function ($amount, $provider_fee) {
            return $amount * ((int)$provider_fee / 100);
        }, $amount, $provider_fee);

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_player_balance($wallet, $currency)
    {
        $data = array_filter($wallet, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'];
        }, 1);

        $rate = array_column($data, 'rate');
        $balance = array_column($data, 'balance');
        $bonus_balance = array_column($data, 'bonus_balance');
        $amount = array_map(function ($balance, $bonus_balance) {
            return $balance + $bonus_balance;
        }, $balance, $bonus_balance);

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_player_bonus_balance($wallet, $currency)
    {
        $data = array_filter($wallet, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'];
        }, 1);

        $rate = array_column($data, 'rate');
        $amount = array_column($data, 'bonus_balance');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_player_real_balance($wallet, $currency)
    {
        $data = array_filter($wallet, function ($v, $k) use ($currency) {
            return $v['currency_id'] === $currency['id'];
        }, 1);

        $rate = array_column($data, 'rate');
        $amount = array_column($data, 'balance');

        $value = $this->set_amount($amount, $rate, $currency);
        return [$value['amount'], $value['total']];
    }

    public function set_bankroll($wallet, $currency)
    {
        return [0.00, 0.00, 0.00];
    }

    public function set_existing($wallet, $currency)
    {
        return [0.00, 0.00, 0.00];
    }

    public function set_balance_reserve_change($wallet, $currency)
    {
        return [0.00, 0.00, 0.00];
    }

    public function set_bank_transfers($wallet, $currency)
    {
        return [0.00, 0.00, 0.00];
    }

    public function set_amount($amount, $rate, $currency)
    {
        $total = abs(usdt_helper(array_sum($amount), $currency['code']));

        $total_bets = $total / (float)$currency['rate'];

        return ['amount' => $total, 'total' => $total_bets ?? 0];

    }

    public function apply_filter(Request $request)
    {
        $input = $request->all();
        Toast::success(__('Filter apply'));
        if (isset($input['range'])){
            $input['date_range'] = $input['range'];
        }

        return redirect()->route('platform.finance.income-statement-report', array_filter($input, function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function clear_filter(Request $request)
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.income-statement-report');
    }
}
