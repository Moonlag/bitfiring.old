<?php

namespace App\Orchid\Screens\Players;

use App\Models\Currency;
use App\Models\GamesBets;
use App\Models\SuspicionTypes;
use App\Orchid\Filters\PlayersBetsFilter;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PlayersBets extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'PlayersBets';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'PlayersBets';

    public $permission = [
        'platform.players'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $bets = GamesBets::filters()
            ->filtersApply([PlayersBetsFilter::class])
            ->leftJoin('players', 'games_bets.user_id', '=', 'players.id')
            ->leftJoin('currency', 'games_bets.currency', '=', 'currency.id')
            ->select('currency.name as currency', 'games_bets.balance_before',  'games_bets.balance_after',
                'games_bets.bet_sum', 'games_bets.payoffs_sum', 'games_bets.payoffs_sum', 'games_bets.profit', 'games_bets.bonus_issue',
                'games_bets.jackpot_win', 'games_bets.created_at', 'games_bets.finished_at', 'players.email'
            )
            ->paginate();

        return [
            'bets' => $bets,
            'filter' => [
                'title' => 'Filter',
                'group' => [
                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Select::make('finished')
                        ->title("Finished")
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->value((int)$request->finished),

                    Select::make('provider')
                        ->title("Provider")
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->value((int)$request->provider),

                    Select::make('currency')
                        ->title("Currency")
                        ->empty('No select', 0)
                       ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->value((int)$request->currency),

                    Input::make('user_id')
                        ->type('number')
                        ->title('User ID')
                        ->value($request->user_id),

                    Input::make('game_table')
                        ->type('number')
                        ->title('Game Table')
                        ->value($request->game_table),

                    Group::make([
                        Select::make('bets_sum')
                            ->title("Bets sum")
                            ->empty('No select', 0)
                            ->options([
                                1 => 'Equals',
                                2 => 'Greater than',
                                3 => 'Less than',
                            ])
                            ->value((int)$request->bets_sum),

                        Input::make('bets_sum_value')
                            ->type('number')
                            ->value($request->bets_sum_value),
                    ])->alignEnd()->render(),

                    Group::make([
                        Select::make('payoffs_sum')
                            ->title("Payoffs sum")
                            ->empty('No select', 0)
                            ->options([
                                1 => 'Equals',
                                2 => 'Greater than',
                                3 => 'Less than',
                            ])
                            ->value((int)$request->payoffs_sum),

                        Input::make('payoffs_sum_value')
                            ->type('number')
                            ->value($request->payoffs_sum_value),
                    ])->alignEnd()->render(),

                    Select::make('issued_bonus')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->title('Issued bonus'),

                    Select::make('jackpot_win')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->title('Jackpot win'),

                    Group::make([
                        Select::make('external')
                            ->title("External")
                            ->empty('No select', 0)
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Starts with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->external),

                        Input::make('external_value')
                            ->type('number')
                            ->value($request->payoffs_sum_value),
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
            ]
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
                    Layout::table('bets', [
                        TD::make('email', 'Player')->sort(),
                        TD::make('title', 'Games')->sort(),
                        TD::make('currency', 'Currency')->sort(),
                        TD::make('balance_before', 'Balance before')->sort(),
                        TD::make('balance_after', 'Balance after')->sort(),
                        TD::make('bets_sum', 'Bets sum')->sort(),
                        TD::make('payoffs_sum', 'Payoffs sum')->sort(),
                        TD::make('profit', 'Profit')->sort(),
                        TD::make('bonus_issue', 'Bonus issue')->sort(),
                        TD::make('jackpot_win', 'Jackpot win')->sort(),
                        TD::make('created_at', 'Created at')
                            ->render(function (GamesBets $model) {
                                return $model->created_at;
                            })->sort(),
                        TD::make('finished_at', 'Finished at')
                            ->render(function (GamesBets $model) {
                                return $model->finished_at;
                            })->sort(),
                        TD::make('action', '')->render(function () {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Button::make(__('view'))->class('btn btn-link disabled')
                                        ->class('dropdown-item')
                                        ->confirm(__('Are you sure you want to change status state?')),
                                ])->class('btn sharp btn-primary tp-btn');
                        })
                    ]),
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.players.players_bets');
    }

    public function apply_filter(Request $request){
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.players.players_bets', array_filter($request->all(), function ($k, $v){
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
