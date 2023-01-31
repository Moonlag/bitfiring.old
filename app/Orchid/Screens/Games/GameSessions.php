<?php

namespace App\Orchid\Screens\Games;

use App\Models\Sessions;
use App\Orchid\Filters\GameSessionsFilter;
use Illuminate\Http\Request;
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

class GameSessions extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Game Sessions';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'GameSessions';

    public $permission = [
        'platform.games.session'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $game_sessions = \App\Models\GameSessions::filters()
            ->filtersApply([GameSessionsFilter::class])
            ->leftJoin('players', 'game_sessions.user_id', '=', 'players.id')
            ->select('players.email', 'players.id as player_id', 'game_sessions.id', 'game_sessions.active', 'game_sessions.ident',
                'game_sessions.last_active_at', 'game_sessions.created_at')
            ->paginate(50);

        return [
            'sessions' => $game_sessions,
            'filter' => [
                'group' => [
                    Group::make([
                        Select::make('email')
                            ->title('Player Email')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Starts with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->email),

                        Input::make('email_value')
                            ->type('text')
                            ->value($request->email_value),
                    ])->alignEnd()->render(),

                    Input::make('player_id_eq')
                        ->title('Player ID EQ')
                        ->type('number')
                        ->value($request->player_id_eq),

                    Group::make([
                        Select::make('ident')
                            ->title('Game identifier')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Starts with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->ident),

                        Input::make('ident_value')
                            ->type('text')
                            ->value($request->ident_value),
                    ])->alignEnd()->render(),

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Select::make('status')
                        ->title('Status')
                        ->options([
                            0 => 'All',
                            1 => 'Active',
                        ])
                        ->value((int)$request->status),

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
                    Layout::table('sessions', [
                        TD::make('id', 'ID')->sort(),
                        TD::make('email', 'Player')->render(function (\App\Models\GameSessions $model){
                            return Link::make($model->email)
                                ->class('link-primary')
                                ->route('platform.players.profile', $model->player_id);
                        })->sort(),
                        TD::make('active', 'Active')->render(function (\App\Models\GameSessions $model) {
                            return $model->active ? "<span class='bg-success rounded px-2 text-white'>YES</span>" : "<span class='bg-danger rounded px-2'>NO</span>";
                        })->sort(),
                        TD::make('ident', 'Game identifier')->sort(),
                        TD::make('last_active_at', 'Last activity at')->render(function (\App\Models\GameSessions $model) {
                            return $model->last_active_at;
                        })
                            ->sort(),
                        TD::make('created_at', 'Created at')
                            ->render(function (\App\Models\GameSessions $model) {
                                return $model->created_at;
                            })
                            ->sort(),
                    ]),
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.games.session');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.games.session', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
