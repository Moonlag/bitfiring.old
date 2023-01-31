<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\Groups;
use App\Models\Players;
use App\Models\Transactions;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class KPIreport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'KPI Report';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'KPIreport';

    public $permission = [
        'platform.finance.kpi-report'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $request->group_by = 4;

        switch ($request->group_by) {
            case 1:
                $start = $request->date_range ? Carbon::parse($request->date_range['start']) : Carbon::now()->startOfMonth();
                $end = $request->date_range ? Carbon::parse($request->date_range['end']) : Carbon::now();
                $format = 'Y-m-d';
                break;
            case 2:
                $start = $request->date_range ? Carbon::parse($request->date_range['start'])->startofWeek() : Carbon::now()->startOfMonth();
                $end = $request->date_range ? Carbon::parse($request->date_range['end'])->endOfWeek() : Carbon::now();
                $format = 'Y';
                break;
            case 3:
                $start = $request->date_range ? Carbon::parse($request->date_range['start'])->startOfMonth() : Carbon::now()->startOfMonth();
                $end = $request->date_range ? Carbon::parse($request->date_range['end'])->endOfMonth() : Carbon::now()->endOfMonth();
                $format = 'F Y';
                break;
            case 4:
                $start = $request->date_range ? Carbon::parse($request->date_range['start'])->startOfYear() : Carbon::now()->startOfYear();
                $end = $request->date_range ? Carbon::parse($request->date_range['end'])->endOfYear() : Carbon::now()->endOfYear();
                $format = 'Y';
                break;
            default:
                $start = $request->date_range ? Carbon::parse($request->date_range['start']) : Carbon::now()->startOfMonth();
                $end = $request->date_range ? Carbon::parse($request->date_range['end']) : Carbon::now();
                $format = 'Y-m-d';
        }

        $start_month = $request->date_range ? Carbon::parse($request->date_range['start']) : Carbon::now()->startOfMonth();
        $end_month = $request->date_range ? Carbon::parse($request->date_range['end'])->endOfDay() : Carbon::now();


        $player = Players::query()
            ->leftJoin('games_bets', function ($join){
                $join->on('players.id', '=', 'games_bets.user_id');
            })
            ->whereBetween('players.created_at', [$start_month, $end_month])
            ->select('players.id', 'games_bets.user_id', 'games_bets.id as bet_id', 'players.status', 'players.created_at')
            ->groupBy('players.id')
            ->get()
            ->toArray();

        $confirmed = count(array_filter($player, function ($v, $k) {
            return $v['status'] > 0;
        }, 1));

        $registration = count($player);
        $active_players_cs = count(array_unique(array_column($player, 'bet_id')));
        $real_players_cs = $active_players_cs;

        $player = ['confirmed' => $confirmed ?? '0', 'registration' => (string)$registration ?? 0, 'active_players_cs' => (string)$active_players_cs, 'real_players_cs' => (string)$real_players_cs];

        $currency = Currency::query()->orderBy('id', 'DESC')
            ->get()->toArray();

        $registration = $player['registration'] ?? 0;

        $transaction = Transactions::query()
            ->select('transactions.id', 'transactions.amount', 'transactions.player_id', 'transactions.reference_type_id',
                'transactions.type_id', 'players.created_at as player_created_at', 'transactions.created_at', 'transactions.currency_id', 'transactions.bonus_part')
            ->leftJoin('players', 'transactions.player_id', '=', 'players.id')
            ->limit(1)
            ->get()->toArray();

        if ($request->currency) {
            $currency_request = $request->currency;
            $currency = array_filter($currency, function ($v, $k) use ($currency_request) {
                return in_array($v['id'], $currency_request);
            }, 1);
        }

        $currency = $this->set_date($start, $end, $format, $currency, $request->group_by);


        $currency = array_map(function ($currency) use ($transaction, $registration, $start, $end, $format) {
            $date = $currency['date'];
            $transaction = array_filter($transaction, function ($v, $k) use ($currency, $date, $start, $end, $format) {
                return $v['currency_id'] === $currency['id'] && Carbon::parse($v['created_at'])->format($format) === $date;
            }, 1);

            $first_unique_player = array_values(array_flip(array_unique(array_column($transaction, 'player_id', 'id'))));

            $first_deposit = array_filter($transaction, function ($v, $k) use ($first_unique_player, $date, $start, $end, $format) {
                return $v['reference_type_id'] === 5 && $v['type_id'] === 1 && in_array($v['id'], $first_unique_player) && Carbon::parse($v['player_created_at'])->format($format) === $date;
            }, 1);

            $first_deposit_count = count(array_column($first_deposit, 'id'));

            $first_deposit_sum = array_sum(array_column($first_deposit, 'amount'));

            $depositing_players_count = count(array_column(array_filter($transaction, function ($v, $k) use ($first_unique_player) {
                return $v['reference_type_id'] === 5 && $v['type_id'] === 1 && in_array($v['id'], $first_unique_player);
            }, 1), 'id'));

            $conversion_rate = 0;
            if ($registration > 0) {
                $conversion_rate = $first_deposit_count / $registration * 100;
            }

            $avg_deposit_first_amount = 0;
            if ($first_deposit_count > 0) {
                $avg_deposit_first_amount = $first_deposit_sum / $first_deposit_count;
            }

            $total_deposit = array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 5 && $v['type_id'] === 1;
            }, 1);

            $total_deposit_count = count(array_column($total_deposit, 'id'));

            $total_deposit_sum = array_sum(array_column($total_deposit, 'amount'));

            $avg_deposit_amount = 0;
            if ($total_deposit_count > 0) {
                $avg_deposit_amount = $total_deposit_sum / $total_deposit_count;
            }

            $avg_deposit_count = 0;
            if ($depositing_players_count > 0) {
                $avg_deposit_count = $total_deposit_count / $depositing_players_count;
            }

            $cashouts_sum = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 5 && $v['type_id'] === 2;
            }, 1), 'amount'));

            $hold = 0;
            if ($total_deposit_sum > 0) {
                $hold = ($total_deposit_sum - $cashouts_sum) / $total_deposit_sum * 100;
            }

            $bonus_issued = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 2 && $v['type_id'] === 2;
            }, 1), 'bonus_part'));

            $bonus_canceled = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 2 && $v['type_id'] === 4;
            }, 1), 'bonus_part'));

            $bonuses_cs = $bonus_issued - $bonus_canceled;

            $win_sum = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 4 && $v['amount'] > 0;
            }, 1), 'amount'));

            $bet_sum = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 4 && $v['amount'] < 0;
            }, 1), 'amount'));

            $ggr_cs = abs($bet_sum) - $win_sum;

            $real_bets_count_cs = count(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 4 && $v['type_id'] === 1;
            }, 1), 'id'));

            $balance_correction = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 1;
            }, 1), 'amount'));

            $ngr = $bet_sum - $win_sum - $bonuses_cs - $balance_correction;

            $bonus_cs_ggr_cs = 0;
            if ($ggr_cs !== 0) {
                $bonus_cs_ggr_cs = $bonuses_cs / $ggr_cs;
            }

            return ['date' => $currency['date'], 'currency' => $currency['code'], 'first_deposit_count' => $first_deposit_count,
                'first_deposit_sum' => $first_deposit_sum, 'total_deposit_count' => $total_deposit_count,
                'total_deposit_sum' => $total_deposit_sum, 'avg_deposit_amount' => $avg_deposit_amount,
                'conversion_rate' => $conversion_rate, 'avg_deposit_first_amount' => $avg_deposit_first_amount,
                'depositing_players_count' => $depositing_players_count, 'avg_deposit_count' => $avg_deposit_count,
                'cashouts_sum' => $cashouts_sum, 'hold' => $hold, 'bonuses_cs' => $bonuses_cs, 'bonus_cs_ggr_cs' => $bonus_cs_ggr_cs,
                'ngr' => $ngr, 'real_bets_count_cs' => $real_bets_count_cs, 'ggr_cs' => $ggr_cs];
        }, $currency);

        $currency = array_reverse($currency);
        $currency[0] = array_merge($player, $currency[0]);

//        (SELECT SUM(amount) FROM transactions WHERE reference_type_id = 5 AND type_id = 1 AND UNIX_TIMESTAMP(created_at) BETWEEN $start_month AND $end_month) as total_deposit_sum,
        return [
            'table' => $currency,
            'thead' => ['Date', 'Registration', 'Confirmed', 'Active players CS', 'Real Players CS', 'Conversion rate',
                'ARPU CS', 'Currency', 'First deposit count', 'First deposit sum', 'Avg first deposit amount',
                'Total deposits count', 'Depositing players count', 'AVG deposit count', 'Total deposits sum',
                'AVG deposit amount', 'Cashouts sum', 'Hold', 'Bonuses CS', 'Bonuses CS GGR CS', 'NGR', 'Real Bets Count CS', 'GGR CS'],
            'filter' => [
                'group' => [
                    Select::make('group_by')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Day',
                            2 => 'Week',
                            3 => 'Month',
                            4 => 'Year'
                        ])
                        ->title('Group by')
                        ->value((int)$request->group_by),

                    DateRange::make('date_range')
                        ->title('Date Range')
                        ->value($request->date_range ?? ['start' => Carbon::now()->format($format), 'end' => Carbon::now()->format('Y-m-d')]),

                    Select::make('currency')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->multiple()
                        ->title('Currencies')
                        ->value($request->currency ? array_map(function ($currency) {
                            return (int)$currency;
                        }, $request->currency) : ''),


                    Select::make('player_groups')
                        ->fromModel(Groups::class, 'title')
                        ->taggable()
                        ->multiple()
                        ->title('Player groups')
                        ->value($request->player_groups),

                    Group::make([
                        Select::make('country')
                            ->title('Country')
                            ->empty('No select', '0')
                            ->fromModel(Countries::class, 'name')
                            ->value((int)$request->country),

                        CheckBox::make('country_checkbox')
                            ->placeholder('Group By Country')
                            ->value($request->country_checkbox),
                    ])->alignEnd()->render(),

                    Input::make('email')
                        ->type('text')
                        ->title('Email')
                        ->value($request->email),


                ],
                'action' => [
                    Button::make('Filter')
                        ->vertical()
                        ->icon('filter')
                        ->class('btn btn-primary btn-sm btn-block')
                        ->method('apply_filter'),

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
                    Layout::view('orchid.finance.kpi_report')
                ],
                'col_right' => [
                    Layout::view('orchid.players.scope-countres'),
                ]
            ]),
        ];
    }

    public function set_date($start, $end, $format, $currency, $type = 0)
    {
        $data = [];
        while ($start->lte($end)) {
            $date = $start->copy()->format($format);
            switch ($type) {
                case 1:
                    $start->addDay();
                    break;
                case 2:
                    $date = $start->copy()->day . ' ' . $start->copy()->format('F') . ' - ' . $start->copy()->endOfWeek()->day . '  ' . $start->copy()->endOfWeek()->format('F') . ' ' . $date;
                    $start->addWeek();
                    break;
                case 3:
                    $start->addMonth();
                    break;
                case 4:
                    $start->addYear();
                    break;
                default:
                    $start->addDay();
            }
            foreach ($currency as $cur) {
                $data[] = [
                    'date' => $date,
                    'id' => $cur['id'],
                    'code' => $cur['code'],
                ];
            }

        }
        return $data;
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.kpi-report');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.kpi-report', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
