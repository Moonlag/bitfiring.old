<?php

namespace App\Orchid\Screens\Players;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\FeedExports;
use App\Models\GroupPlayers;
use App\Models\Groups;
use App\Models\Languages;
use App\Models\Payments;
use App\Models\Sessions;
use App\Models\Tags;
use App\Models\Wallets;
use App\Orchid\Filters\PlayersFilter;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;

class Players extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Players';

    public $export = 'players';
    public $staff;

    public $permission = [
        'platform.players'
    ];
    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'Players';

    /**
     * Query data.
     *
     * @param Request $request
     * @return array
     */
    public function query(Request $request): array
    {
        $this->staff = $request->user();
        dd(\Auth::user()->hasAccess('platform.main'));

        $players = \App\Models\Players::query()
            ->filtersApply([PlayersFilter::class])
            ->leftJoin('countries', 'players.country_id', '=', 'countries.id')
            ->leftJoin('languages', 'players.language_id', '=', 'languages.id')
            ->leftJoin('partners', 'players.partner_id', '=', 'partners.id')
            ->whereNull('players.deleted_at')
            ->select('players.id', 'players.email', 'players.fullname', 'countries.name as country', 'players.created_at',
                'players.deposit_count', 'players.deposit_sum', 'players.bonus_count', 'players.ggr',
                'players.balance', 'players.status', 'languages.name as language', 'players.mail_verified', 'partners.email as partner')
            ->orderBy('id', 'DESC')
            ->paginate(100);

//        foreach ($players->items() as $item) {
//            $item->group = array_values(array_filter($groups, function ($k, $v) use ($item) {
//                return $item->id === $k['id'];
//            }, 1));
//        }
        $now = Carbon::now()->subHour(3)->format('Y-m-d H:i:S');
        $all = \App\Models\Players::query()
            ->whereNull('players.deleted_at')
            ->get()->count();

        $online = Sessions::query()
            ->where('sessions.created_at', '>=', $now)
            ->leftJoin('players', 'sessions.user_id', '=', 'players.id')
            ->whereNull('players.deleted_at')
            ->select('players.id')
            ->get()
            ->unique('id')
            ->count();

        $real = Payments::query()
            ->where([['payments.type_id', '=', 3], ['payments.status', '=', 1]])
            ->rightJoin('players', 'payments.user_id', '=', 'players.id')
            ->whereNull('players.deleted_at')
            ->select('players.id')
            ->get()
            ->unique('id')
            ->count();

        return ['players' => $players,
            'filter' => [
                'group' => [
                    Input::make('email_contains')
                        ->type('text')
                        ->title('Email contains:')
                        ->value($request->email_contains),

                    Input::make('partner')
                        ->type('text')
                        ->value($request->partner)
                        ->title('Partner Email:'),

                    Group::make([
                        Select::make('phone')
                            ->title('Phone:')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Starts with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->phone),

                        Input::make('phone_value')
                            ->type('text')
                            ->value($request->phone_value),
                    ])->alignEnd()->render(),

                    Select::make('tags[]')
                        ->fromModel(Tags::class, 'slug')
                        ->multiple()
                        ->value($request->tags ? array_map(function ($tag) {
                            return (int)$tag;
                        }, $request->tags) : '')
                        ->title('Tags:'),

                    Select::make('groups[]')
                        ->fromModel(Groups::class, 'title')
                        ->multiple()
                        ->value($request->groups ? array_map(function ($tag) {
                            return (int)$tag;
                        }, $request->groups) : '')
                        ->title('Groups:'),

                    Input::make('user_groups')
                        ->type('text')
                        ->title('User groups:')
                        ->value($request->user_groups),

                    Select::make('currency')
                        ->empty('No select', '0')
                        ->fromQuery(Currency::query()->where('parent_id', '=', 0), 'code', 'id')
                        ->value((int)$request->currency)
                        ->title('Currency:'),

                    Group::make([
                        Select::make('firstname')
                            ->title('Firstname:')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Starts with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->firstname),

                        Input::make('firstname_value')
                            ->type('text')
                            ->value($request->firstname_value),
                    ])->alignEnd()->render(),

                    Group::make([
                        Select::make('lastname')
                            ->title('Lastname:')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Starts with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->lastname),

                        Input::make('lastname_value')
                            ->type('text')
                            ->value($request->lastname_value),
                    ])->alignEnd()->render(),

                    Select::make('country')
                        ->fromModel(Countries::class, 'name')
                        ->empty('No select', '0')
                        ->value((int)$request->country)
                        ->title('Country:'),


                    Select::make('confirmed')
                        ->options([
                                'no' => 'No',
                                'yes' => 'Yes'
                            ]
                        )
                        ->empty('No select', '0')
                        ->value($request->confirmed)
                        ->title('Confirmed:'),

                    DateRange::make('sign_up')
                        ->title('Sign up:')
                        ->value($request->sign_up),

                    DateRange::make('locked_at')
                        ->title('Locked at:')
                        ->value($request->locked_at),

                    Input::make('current_sign_in_ip')
                        ->type('text')
                        ->title('Current sign in ip:')
                        ->value($request->current_sign_in_ip),

                    Input::make('ip')
                        ->type('text')
                        ->title('IP:')
                        ->value($request->ip),

//                    Select::make('language')
//                        ->fromModel(Languages::class, 'name', 'code')
//                        ->empty('No select', '0')
//                        ->value($request->language)
//                        ->title('Language:')->canSee(false),

                    Select::make('receive_email_promos')
                        ->options([
                                'no' => 'No',
                                'yes' => 'Yes'
                            ]
                        )
                        ->empty('No select', '0')
                        ->value($request->receive_email_promos)
                        ->title('Receive email promos:'),

                    Select::make('receive_sms_promos')
                        ->options([
                                'no' => 'No',
                                'yes' => 'Yes'
                            ]
                        )
                        ->empty('No select', '0')
                        ->value($request->receive_sms_promos)
                        ->title('Receive sms promos:'),

                    Select::make('state')
                        ->options([
                                0 => 'All',
                                1 => 'Active',
                                2 => 'Disabled',
                                3 => 'Suspended',
                            ]
                        )
                        ->value((int)$request->state)
                        ->title('State:'),

                    Select::make('disable_reason')
                        ->options([
                                0 => 'All',
                                1 => 'Antifraud lock',
                                2 => 'Auth Duplicate',
                                3 => 'Chargeback',
                                4 => 'License Rules',
                                5 => 'Manual',
                                6 => 'Negative Balance',
                                7 => 'Personal ID Duplicate',
                                8 => 'Phone Number Duplicate',
                                9 => 'Registration Duplicate',
                            ]
                        )
                        ->value((int)$request->disable_reason)
                        ->title('Disable reason:'),

                    Select::make('invited_affiliate')
                        ->options([
                                0 => 'All',
                                1 => 'No',
                                2 => 'Yes',
                            ]
                        )
                        ->value((int)$request->invited_affiliate)
                        ->title('Invited by an affiliate:'),

                    Select::make('scope')
                        ->options([
                                3 => 'All',
                                1 => 'Online',
                                2 => 'Real',
                            ]
                        )
                        ->value((int)$request->scope)
                        ->title('Scope:'),


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
            'scope' => [
                'title' => 'Scope Counters',
                'thead' => [
                    'Scope Name' => 'Count'
                ],
                'table' => [
                    'All' => $all,
                    'Online' => $online,
                    'Real' => $real,
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

            Link::make('New Player')
                ->icon('plus')
                ->class('btn btn-secondary')
                ->route('platform.players.new_player'),

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
                    Layout::table('players', [
                        TD::make()->render(function (\App\Models\Players $model) {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Link::make('Edit')
                                        ->route('platform.players.edit', ['id' => $model->id, 'status' => 1])
                                        ->class('dropdown-item')
                                        ->icon('pencil'),
                                    Link::make('View')
                                        ->route('platform.players.profile', $model->id)
                                        ->class('dropdown-item')
                                        ->icon('user'),
                                ])->class('btn sharp btn-primary tp-btn');
                        })->align(TD::ALIGN_LEFT)->sort(),
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
                        TD::make('id', 'ID')->sort(),
                        TD::make('country', 'Country')->sort(),
                        TD::make('created_at', 'Sign Up')
                            ->render(function (\App\Models\Players $model) {
                                return $model->created_at;
                            })->sort(),
                        TD::make('deposit_count', 'Total Deposits')->canSee(false)->sort(),
                        TD::make('deposit_sum', 'Total Amount Deposits')->canSee(false)->sort(),
                        TD::make('bonus_count', 'Bonuses')->canSee(false)->sort(),
                        TD::make('ggr', 'GGR')->canSee(false)->sort(),
                        TD::make('balance', 'Balance')->render(function (\App\Models\Players $model) {
                            $wallets = DB::table('wallets')
                                ->where('user_id', $model->id)
                                ->join('currency', 'wallets.currency_id', '=', 'currency.id')
                                ->select('wallets.balance', 'currency.rate')
                                ->get();
                            $balance = $wallets->map(function ($item, $key) {
                                return ((float)$item->balance / (float)$item->rate);
                            })->sum();
                            return number_format($balance, 2);
                        })->sort(),
                        TD::make('Deposits Sum')->render(function (\App\Models\Players $model) {
                            $wallets = \App\Models\Payments::query()
                                ->where('user_id', $model->id)
                                ->where('type_id', 3)
                                ->where('status', 1)
                                ->join('currency', 'payments.currency_id', '=', 'currency.id')
                                ->select('payments.amount', 'payments.amount_usd', 'currency.rate')
                                ->get();
                            $balance = $wallets->map(function ($item, $key) {
                                if ($item->amount_usd) {
                                    return $item->amount_usd;
                                }
                                return ((float)$item->balance / (float)$item->rate);
                            })->sum();;
                            return number_format($balance, 2);
                        })->sort(),
                        TD::make('status', 'Status')->render(function (\App\Models\Players $model) {
                            switch ($model->mail_verified) {
                                case 0:
                                    return 'not confirmed';
                                case 1:
                                    return 'active';
                                case 2:
                                    return 'disabled';
                                default:
                                    return '-';
                            }
                        })->sort(),
                        TD::make('partner', 'Partner Email')->render(function (\App\Models\Players $model) {
                            return $model->partner ?? '-';
                        })->sort(),
                    ]),
                ],
                'col_right' => [
                    Layout::view('orchid.players.scope-countres'),
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.players');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));

        return redirect()->route('platform.players', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function add_export(Request $request)
    {
        $fileName = 'players-' . Carbon::now()->format('Y-m-d') . '.csv';

        $tasks = \App\Models\Players::query()
            ->filtersApply([PlayersFilter::class])
            ->leftJoin('countries', 'players.country_id', '=', 'countries.id')
            ->leftJoin('languages', 'players.language_id', '=', 'languages.id')
            ->leftJoin('partners', 'players.partner_id', '=', 'partners.id')
            ->whereNull('players.deleted_at')
            ->select('players.id', 'players.email', 'players.fullname', 'countries.name as country', 'players.created_at',
                'players.deposit_count', 'players.deposit_sum', 'players.bonus_count', 'players.ggr',
                'players.balance', 'players.status', 'languages.name as language', 'players.mail_verified', 'partners.email as partner')
            ->orderBy('id', 'DESC')
            ->get();

        $header = $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName"
        ];

        $columns = array('E-mail', 'ID', 'Country', 'Sign Up', 'Balance', 'Deposit Sum', 'Bonuses', 'GGR', 'Status', 'Partner Email');



        $callback = function () use ($tasks, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);

            function getStatus($status = '')
            {
                switch ($status) {
                    case 0:
                        return 'not confirmed';
                    case 1:
                        return 'active';
                    case 2:
                        return 'disabled';
                    default:
                        return '-';
                }
            }

            foreach ($tasks as $task) {

                $wallets = DB::table('wallets')
                    ->where('user_id', $task->id)
                    ->join('currency', 'wallets.currency_id', '=', 'currency.id')
                    ->select('wallets.balance', 'currency.rate')
                    ->get();
                $balance = $wallets->map(function ($item, $key) {
                    return ((float)$item->balance / (float)$item->rate);
                })->sum();

                $wallets = \App\Models\Payments::query()
                    ->where('user_id', $task->id)
                    ->where('type_id', 3)
                    ->where('status', 1)
                    ->join('currency', 'payments.currency_id', '=', 'currency.id')
                    ->select('payments.amount', 'payments.amount_usd', 'currency.rate')
                    ->get();
                $deposit_sum = $wallets->map(function ($item, $key) {
                    if ($item->amount_usd) {
                        return $item->amount_usd;
                    }
                    return ((float)$item->balance / (float)$item->rate);
                })->sum();;

                fputcsv($stream, [
                    'E-mail' => $task->email,
                    'ID' => $task->id,
                    'Country' => $task->country,
                    'Sign Up' => $task->created_at,
                    'Balance' => $balance,
                    'Deposit Sum' => $deposit_sum,
                    'Status' => getStatus($task->status),
                    'Partner Email' => $task->partner,
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
