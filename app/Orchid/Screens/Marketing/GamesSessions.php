<?php

namespace App\Orchid\Screens\Marketing;

use App\Models\Currency;
use App\Models\Games;
use App\Models\GameSessions;
use App\Models\Groups;
use App\Models\User;
use App\Orchid\Filters\GamesSessionsFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GamesSessions extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'GamesSessions';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'GamesSessions';

    public $permission = [
        'platform.finance.games-sessions-report'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $table = GameSessions::filters()
            ->filtersApply([GamesSessionsFilter::class])
            ->leftJoin('games', 'game_sessions.game_id', '=', 'games.id')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->select(DB::raw('COUNT(game_sessions.id) as session_count, CONCAT(game_provider.title, ":", game_sessions.ident) as ident, COUNT(DISTINCT(game_sessions.user_id)) as player_count'))
            ->orderBy('session_count', 'DESC')
            ->groupBy('games.id')
            ->paginate(15);

        return [
            'table' => $table,
            'filter' => [
                'title' => 'Filter',
                'group' => [
                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Select::make('player_groups')
                        ->fromModel(Groups::class, 'title')
                        ->taggable()
                        ->multiple()
                        ->title('Player groups')
                        ->value($request->player_groups ? array_map(function ($group) {
                            return (int)$group;
                        }, $request->player_groups) : ''),

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
                    Layout::table('table', [
                        TD::make('ident', 'Game identifier')->sort(),
                        TD::make('session_count', 'Session Count')->sort(),
                        TD::make('player_count', 'Players Count')->sort(),
                    ])

                ],
                'col_right' => [
                    Layout::view('orchid.players.scope-countres'),
                ]
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.games-sessions-report');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.games-sessions-report', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
