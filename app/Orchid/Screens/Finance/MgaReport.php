<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\GamesCats;
use App\Models\GamesProvider;
use App\Models\Transactions;
use App\Models\User;
use App\Orchid\Filters\MgaReportFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class MgaReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'MgaReport';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'MgaReport';

    public $permission = [
        'platform.finance.mga-report'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $request['group_by'] = $request['group_by'] ?? 2;
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

        $currency = Currency::query()
            ->Leftjoin('currency_exchange', 'currency.id', '=', 'currency_exchange.currency_id')
            ->select('currency.id', 'currency.code', 'currency_exchange.rate', 'currency.name')
            ->get();

        if ($request->currency) {
            $currency_request = $request->currency;
            $currency = array_filter($currency->toArray(), function ($v, $k) use ($currency_request) {
                return in_array($v['id'], $currency_request);
            }, 1);
        }

        $transaction = Transactions::filters()
            ->filtersApply([MgaReportFilter::class])
            ->select('transactions.id',
                'transactions.amount',
                'transactions.player_id',
                'transactions.reference_type_id',
                'transactions.type_id',
                'players.country_id',
                'transactions.created_at',
                'transactions.currency_id',
                'transactions.bonus_part',
                'game_provider.id as provider_id')
            ->leftJoin('players', 'transactions.player_id', '=', 'players.id')
            ->leftJoin('games_bets', 'transactions.reference_id', '=', 'games_bets.id')
            ->leftJoin('games', 'games_bets.game_id', '=', 'games.id')
            ->leftJoin('games_cats', 'games.category_id', '=', 'games_cats.id')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->limit(100)
            ->get()->toArray();

        $country = Countries::query()->where('status', 1)->get()->toArray();

        $provider = GamesProvider::query()->select('id as provider_id', 'title');
        if ($request->get('provider')) {
            $provider = $provider->whereIn('id', $request->get('provider'));
        }
        $provider = $provider->orderBy('id', 'DESC')->get()->toArray();

        $date = $this->set_date($start, $end, $format, $currency, $request->group_by);
        $table = $this->set_countries($country, $provider, $date, $transaction, $format);
        return [
            'table' => $table,
            'filter' => [
                'title' => 'Filter',
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
                        ->value($request->date_range ?? ['start' => Carbon::now()->startOfMonth()->format($format), 'end' => Carbon::now()->format('Y-m-d')]),

//                    DateRange::make('exchange_rate_date')
//                        ->canSee(false)
//                        ->title('Exchange Date Range')
//                        ->value($request->exchange_rate_date),

                    Select::make('provider')
                        ->title('Provider')
                        ->fromModel(GamesProvider::class, 'title')
                        ->multiple()
                        ->value($request->provider ? array_map(function ($provider) {
                            return (int)$provider;
                        }, $request->provider) : ''),

                    Select::make('currency')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->multiple()
                        ->title('Currencies')
                        ->value($request->currency ? array_map(function ($currency) {
                            return (int)$currency;
                        }, $request->currency) : ''),

                    Select::make('categories')
                        ->title('Categories')
                        ->multiple()
                        ->fromQuery(GamesCats::query()->whereNull('slug_suffix'), 'title')
                        ->value($request->categories ? array_map(function ($categories) {
                            return (int)$categories;
                        }, $request->categories) : ''),

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
                    Layout::view('orchid.finance.mga-report')

                ],
                'col_right' => [
                    Layout::view('orchid.players.scope-countres'),
                ]
            ]),
        ];
    }

    public function set_countries($country, $provider, $data, $transaction, $format)
    {
        $result = [];
        foreach ($country as $key => $con) {
            $result[$key]['name'] = $con['name'];
            $result[$key]['data'] = array_map(function ($prov) use ($transaction, $con ,$format) {
                $date = $prov['date'];
                $data = array_filter($transaction, function ($v, $k) use ($prov, $con, $format, $date) {
                    return $v['provider_id'] === $prov['provider_id'] && $v['currency_id'] === $prov['id']
                        && $v['country_id'] === $con['id'] && Carbon::parse($v['created_at'])->format($format) === $date;
                }, 1);

                $bet = array_filter($data, function ($v, $k) {
                    return $v['amount'] < 0;
                }, 1);

                $bet_sum = array_sum(array_column($bet, 'amount'));

                $win = array_filter($data, function ($v, $k) {
                    return $v['amount'] > 0;
                }, 1);

                $win_sum = array_sum(array_column($win, 'amount'));

                $bonus_bet = array_filter($data, function ($v, $k) {
                    return $v['bonus_part'] < 0;
                }, 1);

                $bonus_bet_sum = array_sum(array_column($bonus_bet, 'amount'));

                $bonus_win = array_filter($data, function ($v, $k) {
                    return $v['bonus_part'] > 0;
                }, 1);

                $bonus_win_sum = array_sum(array_column($bonus_win, 'amount'));

                $revenue = (abs($bet_sum) - $win_sum + $bonus_win_sum - $bonus_bet_sum) * $prov['rate'];

                return array_merge($prov, ['bet_sum' => abs($bet_sum), 'win_sum' => $win_sum, 'bonus_win_sum' => $bonus_win_sum, 'bonus_bet_sum' => $bonus_bet_sum, 'revenue' => $revenue]);
            }, array_reverse($this->set_provider_system($provider, $data)));
            $result[$key]['revenue'] = array_sum(array_column($result[$key]['data'], 'revenue'));
        }

        return $result;

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
                    'rate' => $cur['rate']
                ];
            }
        }
        return $data;
    }

    public function set_provider_system($payments, $date)
    {
        $result = [];
        foreach ($payments as $pay) {
            foreach ($date as $cur) {
                $result [] = array_merge($pay, $cur);
            }

        }
        return $result;
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.mga-report');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.mga-report', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
