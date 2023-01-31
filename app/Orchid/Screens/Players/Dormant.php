<?php

namespace App\Orchid\Screens\Players;

use App\Models\Currency;
use App\Models\GamesBets;
use App\Models\GameSessions;
use App\Models\GroupPlayers;
use App\Models\Payments;
use App\Models\Events;
use App\Models\Sessions;
use App\Orchid\Filters\DormantFilter;
use App\Orchid\Filters\PlayersFilter;
use Carbon\Carbon;
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

class Dormant extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Dormant Players';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'Dormant';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $start = Carbon::now();
        $end = Carbon::now()->subDays(7);

        $payments = Payments::query()->whereBetween('payments.created_at', [$end, $start])->select(DB::raw('distinct(user_id)'))->pluck('user_id');
        $game_sessions = GameSessions::query()->whereBetween('game_sessions.created_at', [$end, $start])->select(DB::raw('distinct(user_id)'))->pluck('user_id');
        $sessions = Sessions::query()->whereBetween('sessions.created_at', [$end, $start])->select(DB::raw('distinct(user_id)'))->pluck('user_id');
        $not_id = [...$payments, ...$game_sessions, ...$sessions];

        $players = \App\Models\Players::filters()
            ->filtersApply([DormantFilter::class])
            ->whereNotIn('players.id', $not_id)
            ->select('players.id', 'players.email', 'players.balance', 'players.created_at as sign_up')
            ->groupBy('players.id')
            ->paginate(10);

        return [
            'players' => $players,
            'filter' => [
                'title' => 'Filter',
                'group' => [
                    DateRange::make('payment_at')
                        ->title('Payment Activity')
                        ->value($request->payment_at),

                    DateRange::make('game_at')
                        ->title('Game Activity')
                        ->value($request->game_at),

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
                    Layout::table('players', [
                        TD::make('email', 'E-mail')->render(function (\App\Models\Players $model) {
                            $link = Link::make($model->email)->class('link-primary')
                                ->route('platform.players.profile', $model->id);
                            $groups = '';
                            foreach ($model->groups as $group) {
                                $color = $group->color;
                                $title = $group->title;
                                $groups .= "<li style='background-color: $color;'>$title</li>";
                            }
                            return "<div>$link
                                    <ul class='groups'>$groups</ul>
                                </div>";
                        })->sort(),
                        TD::make('balance', 'Balance')->sort(),
                        TD::make('sign_up', 'Sign Up')->sort(),
                        TD::make('last_login_at', 'Last Login at')
                            ->render(function (\App\Models\Players $model){
                                return $model->last_session->created_at ?? '-';
                            })
                            ->sort(),
                        TD::make('last_payment_activity', 'Last payment activity')
                            ->render(function (\App\Models\Players $model){
                                return $model->last_payment->created_at ?? '-';
                            })
                            ->sort(),
                        TD::make('last_game_activity', 'Last game activity')
                            ->render(function (\App\Models\Players $model){
                                return $model->last_game_sessions->created_at ?? '-';
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
        return redirect()->route('platform.players.dormant');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.players.dormant', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
