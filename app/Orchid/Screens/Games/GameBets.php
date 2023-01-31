<?php

namespace App\Orchid\Screens\Games;

use App\Models\Currency;
use App\Models\FeedExports;
use App\Models\GamesBets;
use App\Models\GamesProvider;
use App\Models\User;
use App\Orchid\Filters\GamesBetFilter;
use App\Orchid\Filters\PlayersFilter;
use App\Orchid\Filters\TransactionsPlayersFilter;
use App\Orchid\Layouts\ViewPlayerNetTotalTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Carbon\Carbon;

class GameBets extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Bets';
    public $staff;

    public $export = 'bets';
//
//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'GameBets';

    public $permission = [
        'platform.games.bets'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $this->staff = $request->user();

        $games = GamesBets::filters()
            ->filtersApply([GamesBetFilter::class])
            ->Join('wallets', 'games_bets.wallet_id', '=', 'wallets.id')
            ->Join('currency', 'wallets.currency_id', '=', 'currency.id')
            ->Join('players', 'games_bets.user_id', '=', 'players.id')
            ->Join('games', 'games_bets.game_id', '=', 'games.id')
            ->select(
                'currency.code as currency',
                'games_bets.bet_sum',
                'games_bets.id',
                'games.name as title',
                'games.id as games_id',
                'games_bets.payoffs_sum',
                'games_bets.balance_before',
                'games_bets.balance_after',
                'games_bets.profit',
                'games_bets.bonus_issue',
                'games_bets.jackpot_win',
                'players.id as player_id',
                'players.email as player',
                'games_bets.created_at',
                'games_bets.status',
                'games_bets.updated_at'
            )
            ->orderBy('games_bets.id', 'DESC')
            ->paginate(100);

        return [
            'transactions' => $games,
            'filter' => [
                'group' => [
                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Select::make('finished')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->title('Finished')
                        ->value((int)$request->finished),

                    Select::make('base_type')
                        ->empty('No select', 0)
                        ->fromModel(GamesProvider::class, 'title')
                        ->title('Base type')
                        ->value((int)$request->base_type),

                    Select::make('currency')
                        ->empty('No select', '0')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->value((int)$request->currency)
                        ->title('Currency'),

                    Input::make('player')
                        ->type('number')
                        ->title('Player')
                        ->value($request->player),

                    Input::make('game_table')
                        ->type('number')
                        ->title('Game table')
                        ->value($request->game_table),

                    Group::make([
                        Select::make('bet_sum')
                            ->title('Bet SUM')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Equals',
                                2 => 'Greater than',
                                3 => 'Less than',
                            ])
                            ->value((int)$request->bet_sum),

                        Input::make('bet_sum_value')
                            ->type('text')
                            ->value($request->bet_sum_value),
                    ])->alignEnd()->render(),

                    Group::make([
                        Select::make('payoff_sum')
                            ->title('Payoff SUM')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Equals',
                                2 => 'Greater than',
                                3 => 'Less than',
                            ])
                            ->value((int)$request->payoff_sum),

                        Input::make('payoff_sum_value')
                            ->type('text')
                            ->value($request->payoff_sum_value),
                    ])->alignEnd()->render(),

                    Select::make('issued_bonus')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->title('Issued bonus')
                        ->value((int)$request->issued_bonus),

                    Select::make('jackpot_win')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->title('Jackpot win')
                        ->value((int)$request->jackpot_win),

                    Group::make([
                        Select::make('external')
                            ->title('External')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Start with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->external),

                        Input::make('external_value')
                            ->type('text')
                            ->value($request->external_value),
                    ])->alignEnd()->render(),

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
        return [
            Button::make('Export')
                ->rawClick()
                ->method('add_export')
                ->parameters(
                    [
                        'export' => [
                            'type_name' => $this->export,
                            'staff_id' => $this->staff->id,
                            'status' => 1,
                        ]
                    ]
                )
                ->icon('share-alt')
                ->class('btn btn-outline-secondary'),
        ];
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
                    Layout::table('transactions', [
                        TD::make('id', 'ID')->render(function (GamesBets $model){
                            return Link::make($model->id)
                                ->route('platform.games.bet.view', $model->id)
                                ->class('link-primary');
                        })
                            ->sort(),
                        TD::make('title', 'Title')->render(function (GamesBets $model){
                            return Link::make($model->title)
                                ->route('platform.games.view', $model->games_id)
                                ->class('link-primary');
                        })
                            ->sort(),
                        TD::make('currency', 'Currency')->sort(),
                        TD::make('balance_before', 'Balance before')->render(function (GamesBets $model){
                            return usdt_helper($model->balance_before, $model->currency) ?? '0.00';
                        })->sort(),
                        TD::make('balance_after', 'Balance after')->sort(),
                        TD::make('bet_sum', 'Bets sum')->sort(),
                        TD::make('payoffs_sum', 'Payoff sum')->sort(),
                        TD::make('profit', 'Profit')->sort(),
                        TD::make('player', 'Player')->render(function (GamesBets $model) {
                            return Link::make($model->player)->route('platform.players.profile', $model->player_id)->class('link-primary');
                        })->sort(),
                        TD::make('status', 'Status')->render(function (GamesBets $model) {
                            return $model->status ? GamesBets::STATUS[$model->status] : '-';
                        })->sort(),
                        TD::make('created_at', 'Created at')->render(function (GamesBets $model) {
                            return $model->created_at ?? '-';
                        })->sort(),
                        TD::make('finished_at', 'Finished at')->render(function (GamesBets $model) {
                            return $model->finished_at ?? '-';
                        })->sort(),
                        TD::make()->render(function (GamesBets $model) {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Link::make('View')
                                        ->class('dropdown-item')
                                        ->route('platform.games.bet.view', $model->id),
                                    Link::make('Force finish')
                                        ->class('dropdown-item')->canSee(false),
                                ])->class('btn sharp btn-primary tp-btn');
                        })->align(TD::ALIGN_RIGHT)->sort(),
                    ]),
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.games.bets');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.games.bets', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function add_export(Request $request)
    {
        $fileName = 'bets-' . Carbon::now()->format('Y-m-d') . '.csv';

        $tasks = GamesBets::filters()
            ->filtersApply([GamesBetFilter::class])
            ->Join('wallets', 'games_bets.wallet_id', '=', 'wallets.id')
            ->Join('currency', 'wallets.currency_id', '=', 'currency.id')
            ->Join('players', 'games_bets.user_id', '=', 'players.id')
            ->Join('games', 'games_bets.game_id', '=', 'games.id')
            ->select(
                'currency.code as currency',
                'games_bets.bet_sum',
                'games_bets.id',
                'games.name as title',
                'games.id as games_id',
                'games_bets.payoffs_sum',
                'games_bets.balance_before',
                'games_bets.balance_after',
                'games_bets.profit',
                'games_bets.bonus_issue',
                'games_bets.jackpot_win',
                'players.id as player_id',
                'players.email as player',
                'games_bets.created_at',
                'games_bets.updated_at'
            )
            ->orderBy('games_bets.id', 'DESC')
            ->get();

        $header = $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName"
        ];

        $columns = array('ID', 'Title', 'Currency', 'Balance before', 'Balance After', 'Bets sum', 'Payoff sum', 'Profit', 'Player', 'Created at', 'Finished at');



        $callback = function () use ($tasks, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);


            foreach ($tasks as $task) {

                fputcsv($stream, [
                    'ID' => $task->id,
                    'Title' => $task->title,
                    'Currency' => $task->currency,
                    'Balance Before' => $task->balance_before,
                    'Balance After' => $task->balance_after,
                    'Bets sum' => $task->bet_sum,
                    'Payoff sum' => $task->payoffs_sum,
                    'Profit' => $task->profit,
                    'Player' => $task->player,
                    'Created at' => $task->created_at,
                    'Finished at' => $task->created_at,
                ]);

            }
            fclose($stream);
        };

        $data = array_merge($request->export, ['url' => $fileName]);
        FeedExports::query()->insert($data);
        Toast::info(__('Success'));
        return response()->stream($callback, 200, $headers);
    }
}
