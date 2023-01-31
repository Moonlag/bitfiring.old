<?php

namespace App\Orchid\Screens\Players;

use App\Models\Sessions;
use App\Orchid\Filters\SuspicionsFilter;
use App\Orchid\Filters\UsersSessionsFilter;
use Faker\Generator as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UsersSessions extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'User Sessions';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'UsersSessions';

    public $permission = [
        'platform.players.sessions'
    ];

    /**
     * Query data.
     *
     * @param Request $request
     * @return array
     */
    public function query(Request $request): array
    {
        $sessions = Sessions::filters()
            ->filtersApply([UsersSessionsFilter::class])
            ->select('sessions.user_agent', 'sessions.device_type',
                'sessions.ip', 'sessions.device', 'sessions.created_at', 'sessions.country', 'sessions.city', 'players.email', 'players.id')
            ->leftJoin('players', 'sessions.user_id', '=', 'players.id')
            ->orderBy('sessions.id', 'DESC')
            ->paginate(20);

        return [
            'sessions' => $sessions,
            'filter' => [
                'title' => 'Filter',
                'group' => [
                    Input::make("player_email")
                        ->type('text')
                        ->title("User's email_contains")
                        ->value($request->player_email),

                    Input::make('player_id_eq')
                        ->type('number')
                        ->title('Player id EQ')
                        ->value($request->player_id_eq),

                    Select::make('device_type')
                        ->title('Device type')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Desktop',
                            2 => 'Mobile',
                        ])
                        ->value((int)$request->device_type),

                    DateRange::make('closed_at')
                        ->title('Closed at')
                        ->value($request->closed_at),

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
                        TD::make('email', 'User')->render(function (Sessions $model){
                            return $model->email ? Link::make($model->email)
                                ->class('link-primary')
                                ->route('platform.players.profile', $model->id) : '';
                        })->sort(),
                        TD::make('created_at', 'Date')
                            ->render(function (Sessions $model) {
                                return $model->created_at;
                            })->sort(),
                        TD::make('country', 'Country')->sort(),
                        TD::make('city', 'City')->sort(),
                        TD::make('user_agent', 'User agent')->sort(),
                        TD::make('device', 'Device')->sort(),
                        TD::make('device_type', 'Device type')->render(function (Sessions $model){
                            switch ($model->device_type){
                                case 1:
                                    return 'desktop';
                                case 2:
                                    return 'mobile';
                                default:
                                    return '-';
                            }
                        })->sort(),
                        TD::make('ip', 'IP')->sort(),
                        TD::make('action', '')->render(function () {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Button::make(__('view'))->class('dropdown-item disabled')
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
        return redirect()->route('platform.players.sessions');
    }

    public function apply_filter(Request $request){
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.players.sessions', array_filter($request->all(), function ($k, $v){
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function fake_table(Faker $faker, $count)
    {
        $players = DB::table('players')->select('id')->latest('id')->first()->id ?? 0;
        $countries = DB::table('countries')->select('id')->latest('id')->first()->id ?? 0;
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            switch (rand(1, 3)) {
                case 1:
                    $platform = $faker->windowsPlatformToken;
                    break;
                case 2:
                    $platform = $faker->linuxPlatformToken;
                    break;
                case 3:
                    $platform = $faker->macPlatformToken;
                    break;
                default:
                    $platform = $faker->macPlatformToken;
            }
            switch (rand(1, 5)) {
                case 1:
                    $browser = $faker->chrome;
                    break;
                case 2:
                    $browser = $faker->opera;
                    break;
                case 3:
                    $browser = $faker->safari;
                    break;
                case 4:
                    $browser = $faker->firefox;
                    break;
                default:
                    $browser = $faker->internetExplorer;
            }
            $data[] = [
                'user_id' => rand(0, $players),
                'user_agent' => $faker->userAgent,
                'device_type' => rand(1, 3),
                'country' => rand(0, $countries),
                'ip' => $faker->ipv4,
                'device' => 'webkit',
                'platform' => $platform,
                'browser' => $browser,
                'closed_at' => $faker->dateTimeBetween('-5 months', '-1 day'),
                'created_at' => $faker->dateTimeBetween('-11 months', '-5 day'),
                'updated_at' => $faker->dateTimeBetween('-11 months', '-5 day')
            ];
        }
        return $data;
    }
}
