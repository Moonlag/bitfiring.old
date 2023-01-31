<?php

namespace App\Orchid\Screens\Marketing;

use App\Models\Currency;
use App\Models\GamesCats;
use App\Models\GamesProvider;
use App\Models\Groups;
use App\Orchid\Filters\GamesRelatedReportFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GamesReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Games Related Report';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'GamesReport';

    public $permission = [
        'platform.finance.games-report'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $request['sum_by'] = $request['sum_by'] ?? 2;
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


        $transaction = \App\Models\Transactions::filters()
            ->filtersApply([GamesRelatedReportFilter::class])
            ->leftJoin('games_bets', 'transactions.reference_id', '=', 'games_bets.id')
            ->leftJoin('games', 'games_bets.game_id', '=', 'games.id')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->leftJoin('games_cats', 'games.category_id', '=', 'games_cats.id')
            ->select('transactions.amount',
                'games.id',
                'transactions.created_at',
                'transactions.currency_id',
                'transactions.type_id',
                'transactions.reference_id',
                'transactions.reference_type_id',
                'transactions.bonus_part',
                'transactions.player_id',
                'game_provider.fee as provider_fee',
                'game_provider.title as provider',
                'game_provider.id as provider_id',
                'games.identer',
                'games.name as game',
                'games.devices'
            )
            ->where('transactions.reference_type_id', 4)
            ->whereIn('transactions.type_id', [1, 3])
            ->limit(10)
            ->get()
            ->toArray();

        $currency = Currency::query()->whereIn('id', [7, 9, 12, 14])->get();

        $provider = GamesProvider::query()->select('id as provider_id', 'title as provider');
        if ($request->get('provider')) {
            $provider = $provider->whereIn('id', $request->get('provider'));
        }
        $provider = $provider->orderBy('id', 'DESC')->get()->toArray();

        if ($request->currency) {
            $currency_request = $request->currency;
            $currency = array_filter($currency->toArray(), function ($v, $k) use ($currency_request) {
                return in_array($v['id'], $currency_request);
            }, 1);
        }

        $date = $this->set_date($start, $end, $format, $currency, $request->sum_by);
        $date = array_reverse($this->set_provider($provider, $date));
        $date = $this->get_game_report($date, $transaction, $format);
        return [
            'thead' => ['Currency', 'Date', 'Game Provider', 'Players Count', 'Real players count', 'Total Bet',
                'Non-bonus bets', 'Total Win', 'Bets Count', 'Real Bets Count', 'Average Bet', 'Payout', 'Gross Revenue',
                'Bonus Part of Gross Revenue'],
            'table' => $date,
            'currency' => $currency,
            'filter' => [
                'title' => 'Filter',
                'group' => [
                    DateRange::make('date_range')
                        ->title('Date Range')
                        ->value($request->date_range ?? ['start' => Carbon::now()->startOfMonth()->format($format), 'end' => Carbon::now()->format('Y-m-d')]),

                    Select::make('currency')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->multiple()
                        ->title('Currencies')
                        ->value($request->currency ? array_map(function ($currency) {
                            return (int)$currency;
                        }, $request->currency) : ''),

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

                    Select::make('player_groups')
                        ->fromModel(Groups::class, 'title')
                        ->taggable()
                        ->multiple()
                        ->title('Player groups')
                        ->value($request->player_groups ? array_map(function ($group) {
                            return (int)$group;
                        }, $request->player_groups) : ''),

                    Input::make('player')
                        ->type('text')
                        ->title('Player')
                        ->value($request->player),

                    Select::make('categories')
                        ->title('Categories')
                        ->multiple()
                        ->fromQuery(GamesCats::query()->whereNull('slug_suffix'), 'title')
                        ->value($request->categories ? array_map(function ($categories) {
                            return (int)$categories;
                        }, $request->categories) : ''),

                    Select::make('provider')
                        ->title('Provider')
                        ->fromModel(GamesProvider::class, 'title')
                        ->multiple()
                        ->value($request->provider ? array_map(function ($provider) {
                            return (int)$provider;
                        }, $request->provider) : ''),
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
                    Layout::view('orchid.marketing.game-report')
                ],
            ]),
        ];
    }


    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.games-report');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.games-report', array_filter($request->all(), function ($k, $v) {
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

    public function set_provider($provider, $date)
    {
        $result = [];
        foreach ($provider as $pay) {
            foreach ($date as $cur) {
                $result [] = array_merge($pay, $cur);
            }

        }
        return $result;
    }

    public function get_game_report($date, $transaction, $format)
    {
        return array_map(function ($currency) use ($transaction, $format) {
            $currency_id = $currency['id'];
            $date = $currency['date'];
            $provider_id = $currency['provider_id'];

            $data = array_filter($transaction, function ($v, $k) use ($currency_id, $date, $format, $provider_id) {
                return $v['currency_id'] === $currency_id && Carbon::parse($v['created_at'])->format($format) === $date
                    && $v['provider_id'] === $provider_id;
            }, 1);

            $bet = array_filter($data, function ($v, $k) {
                return $v['type_id'] === 1;
            }, 1);

            $players_count = count(array_unique(array_column($bet, 'player_id')));

            $bonus_non = array_filter($bet, function ($v, $k) {
                return (int)$v['bonus_part'] === 0;
            }, 1);

            $real_player = count(array_unique(array_column($bonus_non, 'player_id')));

            $total_bet = abs(array_sum(array_column($bet, 'amount')));

            $bonus_bets_no = abs(array_sum(array_column($bonus_non, 'amount')));

            $win = array_filter($data, function ($v, $k) {
                return $v['type_id'] === 3;
            }, 1);

            $win_bonus = array_filter($data, function ($v, $k) {
                return $v['type_id'] === 3 || $v['type_id'] === 4;
            }, 1);

            $win_bonus = array_sum(array_column($win_bonus, 'bonus_part'));

            $total_win = array_sum(array_column($win, 'amount'));

            $bet_count = count($bet);
            $real_bet_count = count($bonus_non);

            $average_bet = 0;
            if ($bet_count > 0) {
                $average_bet = $total_bet / $bet_count;
            }

            $payout = 0;
            if ($total_bet > 0) {
                $payout = $total_win / $total_bet * 100;
            }

            $gross_revenue = $total_bet - $total_win;
            $bonus_part_revenue = $win_bonus - ($total_bet - $bonus_bets_no);
            return array_merge($currency, ['players_count' => $players_count, 'real_player' => $real_player,
                'total_bet' => $total_bet, 'bonus_bets_no' => $bonus_bets_no, 'total_win' => $total_win,
                'bet_count' => $bet_count, 'real_bet_count' => $real_bet_count, 'average_bet' => $average_bet,
                'payout' => $payout, 'gross_revenue' => $gross_revenue, 'bonus_part_revenue' => $bonus_part_revenue]);
        }, $date);
    }
}
