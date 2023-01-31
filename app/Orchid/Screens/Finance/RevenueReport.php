<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\EventTypes;
use App\Models\Payments;
use App\Models\PaymentSystem;
use App\Models\Transactions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class RevenueReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Revenue Report';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'RevenueReport';

    public $permission = [
        'platform.finance.revenue-report'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();
        $format = 'Y-m-d';

        if ($request->sum_by) {
            switch ($request->sum_by) {
                case 1:
                    $start = $request->date_range ? Carbon::parse($request->date_range['start']) : Carbon::now()->startOfMonth();
                    $end = $request->date_range ? Carbon::parse($request->date_range['end']) : Carbon::now();
                    $format = 'Y-m-d';
                    break;
                case 2:
                    $start = $request->date_range ? Carbon::parse($request->date_range['start'])->startOfWeek() : Carbon::now()->startOfMonth();
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
        }


        $transaction = \App\Models\Transactions::query()
            ->leftJoin('games_bets', 'transactions.reference_id', '=', 'games_bets.id')
            ->leftJoin('games', 'games_bets.game_id', '=', 'games.id')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->leftJoin('payments', function ($join) {
                $join->where('transactions.reference_type_id', 1);
                $join->on('transactions.reference_id', '=', 'payments.id');
            })
            ->select('transactions.amount',
                'transactions.created_at',
                'transactions.currency_id',
                'transactions.type_id',
                'transactions.reference_type_id',
                'game_provider.fee as provider_fee',
                'payments.network_fee',
            )->whereIn('transactions.reference_type_id', [1, 4, 5])->groupBy('transactions.id')
            ->limit(100)->get()->toArray();


        $currency = Currency::query()->where('excluded', '=', 0)->get();

        $check_box_filter = [];
        foreach ($currency as $kye => $value) {
            if ($kye === 0) {
                $check_box_filter[] = CheckBox::make("currency[$value->id]")
                    ->placeholder($value->code . ' - ' . $value->name)
                    ->value($request->currency[$value->id] ?? false)
                    ->title('Currency');
            } else {
                $check_box_filter[] = CheckBox::make("currency[$value->id]")
                    ->placeholder($value->code . ' - ' . $value->name)
                    ->value($request->currency[$value->id] ?? false);
            }
        }

        if ($request->currency) {
            $currency_request = $request->currency;
            $currency = array_filter($currency->toArray(), function ($v, $k) use ($currency_request) {
                return in_array($v['id'], array_keys($currency_request));
            }, 1);
        }

        $ngr = $this->set_date($start, $end, $format, $currency, $request->sum_by);

        $ngr = $this->ngr($ngr, $transaction, $format);

        return [
            'ngr1' => [
                'title' => 'NGR1 = GGR - Bonuses - Balance Correction + No Deposit Bonuses',
                'table' => array_reverse(array_column($ngr, 'ngr1'))
            ],
            'ngr2' => [
                'title' => 'NGR2 = NGR1 - Game Providers Fee - Payment Systems Fee + Casino Payment Fee',
                'table' => array_reverse(array_column($ngr, 'ngr2'))
            ],
            'ngr3' => [
                'title' => 'NGR3 = NGR2 - No Deposit Bonuses - Gifts',
                'table' => array_reverse(array_column($ngr, 'ngr3'))
            ],
            'filter' => [
                'group' => array_merge([
                    DateRange::make('date_range')
                        ->title('Date Range')
                        ->value($request->date_range ?? ['start' => Carbon::now()->startOfMonth()->format($format), 'end' => Carbon::now()->format('Y-m-d')]),

                        Select::make('sum_by')
                            ->title('Sum By')
                            ->options([
                                1 => 'Day',
                                2 => 'Week',
                                3 => 'Month',
                                4 => 'Year'
                            ])
                            ->empty('No select', 0)
                            ->value((int)$request->sum_by),

                ], $check_box_filter),
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
                    Layout::view('orchid.finance.reveno')
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.revenue-report');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.revenue-report', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
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

    public function ngr($ngr, $transaction, $format)
    {
        return array_map(function ($el) use ($transaction, $format) {
            $date = $el['date'];

            $transaction = array_filter($transaction, function ($v, $k) use ($el, $date, $format) {
                return Carbon::parse($v['created_at'])->format($format) === $date && $el['id'] === $v['currency_id'];
            }, 1);

            $balance_correction =array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 1;
            }, 1), 'amount'));

            $bet_sum = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 4 && $v['amount'] < 0;
            }, 1), 'amount'));

            $win_sum = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 4 && $v['amount'] > 0;
            }, 1), 'amount'));

            $ggr = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 4;
            }, 1), 'amount'));

            $ngr1 = $ggr - $balance_correction;

            $payment_fee = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 1 || $v['reference_type_id'] === 5;
            }, 1), 'network_fee'));

            $game_fee = array_sum(array_map(function ($v) {
                return abs($v['amount'] * ($v['provider_fee'] / 100));
            }, array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 4 && $v['amount'];
            }, 1)));

            $ngr2 = $ngr1 - $payment_fee - $game_fee;

            $gifts = array_sum(array_column(array_filter($transaction, function ($v, $k) {
                return $v['reference_type_id'] === 1 || $v['type_id'] === 5;
            }, 1), 'amount'));

            return [
                'ngr1' => array_merge($el, ['bet_sum' => $bet_sum, 'gross_revenue' => $ggr, 'win_sum' => $win_sum, 'balance_correction' => $balance_correction, 'net_revenue' => $ggr - $balance_correction, 'ngr1' => $ngr1]),
                'ngr2' => array_merge($el, ['ngr1' => $ngr1, 'payment_fee' => $payment_fee, 'game_fee' => $game_fee, 'ngr2' => $ngr2]),
                'ngr3' => array_merge($el, ['ngr2' => $ngr2, 'gifts' => $gifts, 'ngr3' => $ngr2 - $gifts])
            ];
        }, $ngr);
    }
}
