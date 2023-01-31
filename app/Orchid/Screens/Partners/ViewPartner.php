<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Partners;

use App\Models\Brands;
use App\Models\Commission;
use App\Models\Currency;
use App\Models\Partner;
use App\Models\Payments;
use App\Models\PaymentSystem;
use App\Models\Players;
use App\Models\Statement;
use App\Models\Tags;
use App\Models\Wallets;
use App\Orchid\Filters\BalanceCorrectionsFilter;
use App\Orchid\Filters\BalanceTransactionsFilter;
use App\Orchid\Layouts\Examples\ChartLineExample;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Radio;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;
use PHPUnit\Runner\Exception;


class ViewPartner extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'ViewPartner';
    public $exist = false;
    public $id;
    public $counter;
    /**
     * Display header description.
     *
     * @var string
     */
    public $description = 'ViewPartner';

    /**
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Partner $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
        }
        if (!empty(session('emulated'))) {
            Auth::guard('partner')->logout();
            session()->forget('emulated');
            Alert::info('You have successfully logout session partner.');
        }

        $visits_count = DB::table('visits')
            ->select(DB::raw('UNIX_TIMESTAMP(created_at) as count'))
            ->where('partner_id', $model->id)
            ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [Carbon::now()->startOfYear()->subYear(1)->timestamp, Carbon::now()->timestamp])
            ->groupBy('created_at')
            ->orderBy('created_at', 'DESC')
            ->get()->toArray();

        $opens_count = DB::table('opens')
            ->select(DB::raw('UNIX_TIMESTAMP(created_at) as count'))
            ->where('partner_id', $model->id)
            ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [Carbon::now()->startOfYear()->subYear(1)->timestamp, Carbon::now()->timestamp])
            ->groupBy('created_at')
            ->orderBy('created_at', 'DESC')
            ->get()->toArray();

        $deposits_count = DB::table('players')
            ->select(DB::raw('UNIX_TIMESTAMP(deposits.created_at) as count'), 'deposits.amount as sum')
            ->Join('deposits', function ($join) {
                $join->on('players.id', '=', 'deposits.player_id');
                $join->on('deposits.id', '=', DB::raw('(select id from deposits where player_id = players.id order by id asc limit 1)'));
            })
            ->whereBetween(DB::raw('UNIX_TIMESTAMP(deposits.created_at)'), [Carbon::now()->startOfYear()->subYear(1)->timestamp, Carbon::now()->timestamp])
            ->where('players.partner_id', $model->id)
            ->groupBy('deposits.id')
            ->get()->toArray();


        $users = Players::filters()->where('partner_id', $model->id)->orderBy('id', 'desc')->paginate(10);

        $players = Players::filters()->where('partner_id', $model->id)->orderBy('id', 'desc')->limit(10)->get();

        $tags = Tags::query()
            ->leftJoin('tag_item', 'tags.id', '=', 'tag_item.tag_id')
            ->where('item_id', $model->id)
            ->select('tags.name')
            ->get()->toArray();


        $brands = Brands::filters()
            ->Join('brand_partners', 'brands.id', '=', 'brand_partners.brand_id')
            ->where('brand_partners.partner_id', $model->id)
            ->select('brands.brand',
                DB::raw('COUNT(players.id) as players_count'),
                DB::raw('COUNT(DISTINCT campaigns.id) as campaigns_count'))
            ->leftJoin('campaigns', 'brands.id', '=', 'campaigns.brand_id')
            ->leftJoin('players', 'campaigns.id', '=', 'players.campaign_id')
            ->groupBy('brands.id')
            ->orderBy('brand', 'desc')
            ->paginate(10);

        $transactions =\App\Models\Payments::filters()
            ->filtersApply([BalanceTransactionsFilter::class])
            ->leftJoin('partners', 'payments.partner_id', '=', 'partners.id')
            ->leftJoin('brands', 'payments.brand_id', '=', 'brands.id')
            ->leftJoin('users', 'payments.staff_id', '=', 'users.id')
            ->leftJoin('currency', 'payments.currency_id', '=', 'currency.id')
            ->where('payments.partner_id', $model->id)
            ->select('partners.id',
                DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'),
                'brands.brand', 'users.email as operator', 'payments.staff_id', 'payments.id', 'payments.amount', 'payments.partner_id',
                'payments.type_id as action', 'currency.name as currency', 'payments.kind_id as kind', 'payments.created_at',
                'payments.report_period_from', 'payments.report_period_to')
            ->orderBy('payments.id', 'asc')
            ->paginate(10);



        $withdraw = Payments::query()->where([['partner_id', '=', $model->id], ['type_id', '=', 3]])->paginate(10);

        $wallets = Wallets::query()
            ->leftJoin('currency', 'wallets.currency_id', '=','currency.id')
            ->where('user_id', $model->id)
            ->select('wallets.id', 'currency.name as code', 'wallets.balance')
            ->get();

        $this->counter = [
            'visits' => $this->filter_query($visits_count, ['count']) ?? '',
            'opens' => $this->filter_query($opens_count, ['count']) ?? '',
            'deposits' => $this->filter_query($deposits_count, ['count', 'sum']) ?? '',
            'players' => $this->deposit_counter($model->id) ?? '',
        ];

        $balance_correction = [];
        foreach ($wallets as $wallet){
            $balance_correction[] =  [
                'currency' => $wallet->code,
                'balance' => $wallet->balance,
                'action' => ModalToggle::make('Change')
                    ->modal('Balance correction')
                    ->modalTitle("Balance correction for $wallet->code")
                    ->parameters(['correction.wallet_id' => $wallet->id])
                    ->method('balance_correction')
                    ->icon('full-screen'),
            ];
        }
        switch ($model->status_state) {
            case '1':
                $state_title = 'Verified';
                break;
            case '2':
                $state_title = 'Not verified';
                break;
            case '3':
                $state_title = 'Blocked';
                break;
            default:
                $state_title = 'Not verified';
        }

        return [
            'latest_cashouts' => [],
            'players' => $players,
            'users' => $users,
            'brands' => $brands,
            'transactions' => $transactions,
            'withdraw' => $withdraw,
            'overview' => [
                'basic_info' => [
                    'header' => [
                        'title' => 'Basic info',
                        'action' => [
                            Link::make('Edit')->class('btn btn-outline-info')
                                ->icon('pencil')
                                ->route('platform.partners.edit', $model->id),
                        ]
                    ],
                    'card_content' => [
                        'Company name' => $model->company ?? $model->firstname . ' ' . $model->lastname,
                        'Address' => $model->address ?? '',
                        'Phone' => $model->phone ?? '',
                        'Traffic sources' => $model->traffic ?? '',
                        'KYC verified' => Button::make()
                            ->method('kyc_change')
                            ->parameters([
                                'partner_id' => $model->id,
                                'kyc' => !$model->kyc
                            ])
                            ->icon($model->kyc ? 'check' : 'close')
                            ->style('font-size: 15px')
                            ->class($model->kyc ? 'btn-outline-info btn kyc' : 'btn-outline-danger btn kyc'),
                        'State' => DropDown::make($state_title)
                            ->icon('arrow-down')->class('btn btn-outline-info')
                            ->list([
                                Button::make(__('Verified'))->class($state_title === 'Verified' ? 'btn btn-link disabled' : 'btn btn-link')
                                    ->method('status_state_change')
                                    ->confirm(__('Are you sure you want to change status state?'))
                                    ->parameters([
                                        'partner_id' => $model->id,
                                        'partner_status_state' => 1
                                    ]),
                                Button::make(__('Not verified'))->class($state_title === 'Not verified' ? 'btn btn-link disabled' : 'btn btn-link')
                                    ->method('status_state_change')
                                    ->confirm(__('Are you sure you want to change status state?'))
                                    ->parameters([
                                        'partner_id' => $model->id,
                                        'partner_status_state' => 2
                                    ]),
                                Button::make(__('Blocked'))->class($state_title === 'Blocked' ? 'btn btn-link disabled' : 'btn btn-link')
                                    ->method('status_state_change')
                                    ->confirm(__('Are you sure you want to change status state?'))
                                    ->parameters([
                                        'partner_id' => $model->id,
                                        'partner_status_state' => 3
                                    ]),
                            ]),
                        'Dedicated operator' => Button::make('Edit'),
                        'Tags' => join(', ', array_column($tags, 'name'))
                    ]
                ],
                'account' => [
                    'title' => 'Accounts',
                    'table' => $balance_correction
                ],
                'quick_stats' => [
                    'title' => 'Quick stats',
                    'action' => Link::make('Full statistics')
                        ->route('platform.finance.partner-statistics', ['partner_id' => $model->id, 'quick_stats' => 1]),
                ],
                'quick_charts' => [
                    'title' => 'Quick charts',
                    'action' => Select::make('partner.state')
                        ->options([
                            1 => 'Visit count',
                        ]),
                ],
            ],
            'charts' => [
                [
                    'name' => 'Some Data',
                    'values' => [25, 40, 30, 35, 8, 52, 17],
                    'labels' => ['12am-3am', '3am-6am', '6am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm'],
                ],
                [
                    'name' => 'Another make',
                    'values' => [25, 50, -10, 15, 18, 32, 27],
                    'labels' => ['12am-3am', '3am-6am', '6am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm'],
                ],
                [
                    'name' => 'Yet Another',
                    'values' => [15, 20, -3, -15, 58, 12, -17],
                    'labels' => ['12am-3am', '3am-6am', '6am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm'],
                ],
                [
                    'name' => 'And Last',
                    'values' => [10, 33, -8, -3, 70, 20, -34],
                    'labels' => ['12am-3am', '3am-6am', '6am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm'],
                ],
            ],
            'transaction' => [
                'filter' => [
                    Select::make('currency')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'name')
                        ->empty('No select', '0')
                        ->title('Currency')
                        ->value((int)$this->request->get('currency')),

                    Group::make([
                        Input::make('amount_from')
                            ->type('number')
                            ->title('Amount')
                            ->value($this->request->get('amount_from')),
                        Input::make('amount_to')
                            ->type('number')
                            ->value($this->request->get('amount_to'))
                            ->class('form-control align-end'),
                    ])->alignEnd()->render(),

                    DateRange::make('correction_created_at')
                        ->title('Correction created at')
                        ->value($this->request->get('correction_created_at')),

                    Select::make('account_type')
                        ->options([
                            1 => 'Partner',
                            2 => 'Bills clip',
                        ])
                        ->empty('No select', '0')
                        ->title('Account type')
                        ->value((int)$this->request->get('account_type')),

                    Group::make([
                        Select::make('reference')
                            ->empty('No select', '')
                            ->title('Reference')
                            ->value($this->request->get('reference')),

                        Input::make('reference_id')
                            ->type('number')
                            ->placeholder('ID')
                            ->value($this->request->get('reference_id'))
                            ->class('form-control align-end'),
                    ])->alignEnd()->render(),

                    Group::make([
                        Select::make('actor')
                            ->options([
                                1 => 'All',
                                2 => 'Operator'
                            ])
                            ->title('Actor')
                            ->value($this->request->get('actor')),

                        Input::make('actor_id')
                            ->type('number')
                            ->placeholder('ID')
                            ->value($this->request->get('actor_id'))
                            ->class('form-control align-end'),
                    ])->alignEnd()->render(),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->parameters(['id' => $model->id])
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->method('clear_filter')
                        ->parameters(['id' => $model->id])
                        ->class('btn btn-default')
                        ->vertical()
                ]
            ]
        ];
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make('Sign in as Affiliate')
                ->icon('user')
                ->method('sign_affiliate')
                ->parameters(['partner_id' => $this->id]),
            Link::make('Edit')
                ->icon('pencil')
                ->route('platform.partners.edit', $this->id),
        ];
    }

    /**
     * Views.
     *
     * @return Layout[]
     */
    public function layout(): array
    {
        return [
            Layout::tabs([
                'Overview' => [
                    Layout::view('orchid.partners.overview.basic-info'),
                    Layout::view('orchid.partners.overview.accounts'),
                    Layout::wrapper('orchid.partners.overview.quick-stats', [
                        'collapse' => Layout::tabs([
                            'Week' => Layout::view('orchid.partners.overview.quick-card', ['card' => [
                                [
                                    'title' => 'Visits count',
                                    'count' => $this->counter['visits']['week']['count'] ?? 0
                                ],
                                [
                                    'title' => 'Registration count',
                                    'count' => $this->counter['opens']['week']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits count',
                                    'count' => $this->counter['deposits']['week']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits sum',
                                    'count' => $this->counter['deposits']['week']['sum'] ?? 0
                                ],
                                [
                                    'title' => 'NGR',
                                    'count' => 3
                                ],
                                [
                                    'title' => 'Depositing players count',
                                    'count' => $this->counter['players']['week'] ?? 0
                                ],
                            ]
                            ]),
                            'Last Week' => Layout::view('orchid.partners.overview.quick-card', ['card' => [
                                [
                                    'title' => 'Visits count',
                                    'count' => $this->counter['visits']['last_week']['count'] ?? 0
                                ],
                                [
                                    'title' => 'Registration count',
                                    'count' => $this->counter['opens']['last_week']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits count',
                                    'count' => $this->counter['deposits']['last_week']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits sum',
                                    'count' => $this->counter['deposits']['last_week']['sum'] ?? 0
                                ],
                                [
                                    'title' => 'NGR',
                                    'count' => 3
                                ],
                                [
                                    'title' => 'Depositing players count',
                                    'count' => $this->counter['players']['last_week'] ?? 0
                                ],
                            ]
                            ]),
                            'Month' => Layout::view('orchid.partners.overview.quick-card', ['card' => [
                                [
                                    'title' => 'Visits count',
                                    'count' => $this->counter['visits']['month']['count'] ?? 0
                                ],
                                [
                                    'title' => 'Registration count',
                                    'count' => $this->counter['opens']['month']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits count',
                                    'count' => $this->counter['deposits']['month']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits sum',
                                    'count' => $this->counter['deposits']['month']['sum'] ?? 0
                                ],
                                [
                                    'title' => 'NGR',
                                    'count' => 3
                                ],
                                [
                                    'title' => 'Depositing players count',
                                    'count' => $this->counter['players']['month'] ?? 0
                                ],
                            ]
                            ]),
                            'Last Month' => Layout::view('orchid.partners.overview.quick-card', ['card' => [
                                [
                                    'title' => 'Visits count',
                                    'count' => $this->counter['visits']['last_month']['count'] ?? 0
                                ],
                                [
                                    'title' => 'Registration count',
                                    'count' => $this->counter['opens']['last_month']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits count',
                                    'count' => $this->counter['deposits']['last_month']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits sum',
                                    'count' => $this->counter['deposits']['last_month']['sum'] ?? 0
                                ],
                                [
                                    'title' => 'NGR',
                                    'count' => 3
                                ],
                                [
                                    'title' => 'Depositing players count',
                                    'count' => $this->counter['players']['last_month'] ?? 0
                                ],
                            ]
                            ]),
                            'Year' => Layout::view('orchid.partners.overview.quick-card', ['card' => [
                                [
                                    'title' => 'Visits count',
                                    'count' => $this->counter['visits']['year']['count'] ?? 0
                                ],
                                [
                                    'title' => 'Registration count',
                                    'count' => $this->counter['opens']['year']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits count',
                                    'count' => $this->counter['deposits']['year']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits sum',
                                    'count' => $this->counter['deposits']['year']['sum'] ?? 0
                                ],
                                [
                                    'title' => 'NGR',
                                    'count' => 3
                                ],
                                [
                                    'title' => 'Depositing players count',
                                    'count' => $this->counter['players']['year'] ?? 0
                                ],
                            ]
                            ]),
                            'Last Year' => Layout::view('orchid.partners.overview.quick-card', ['card' => [
                                [
                                    'title' => 'Visits count',
                                    'count' => $this->counter['visits']['last_year']['count'] ?? 0
                                ],
                                [
                                    'title' => 'Registration count',
                                    'count' => $this->counter['opens']['last_year']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits count',
                                    'count' => $this->counter['deposits']['last_year']['count'] ?? 0
                                ],
                                [
                                    'title' => 'First deposits sum',
                                    'count' => $this->counter['deposits']['last_year']['sum'] ?? 0
                                ],
                                [
                                    'title' => 'NGR',
                                    'count' => 1
                                ],
                                [
                                    'title' => 'Depositing players count',
                                    'count' => $this->counter['players']['last_year'] ?? 0
                                ],
                            ]
                            ]),
                        ])
                    ]),
                    Layout::wrapper('orchid.partners.overview.quick-charts', [
                        'collapse' => Layout::tabs([
                            'Week' => ChartLineExample::class,
                            'Last Week' => ChartLineExample::class,
                            'Month' => ChartLineExample::class,
                            'Last Month' => ChartLineExample::class,
                            'Year' => ChartLineExample::class,
                            'Last Year' => ChartLineExample::class,
                        ])
                    ]),
                    Layout::wrapper('orchid.partners.overview.latest', [
                        'header' => Layout::view('orchid.partners.overview.latest-header', ['title' => 'Latest requested cashouts', 'action' => Link::make('All payments')]),
                        'table' => Layout::table('withdraw', [
                            TD::make('id','ID')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('amount','Amount')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('Created at')->render(function (Payments $model){
                             return   $model->created_at;
                            })->sort()->align(TD::ALIGN_CENTER),
                        ])
                    ]),
                    Layout::wrapper('orchid.partners.overview.latest', [
                        'header' => Layout::view('orchid.partners.overview.latest-header', ['title' => 'Latest issued bills', 'action' => Link::make('All bills')]),
                        'table' => Layout::table('latest_cashouts', [
                            TD::make('ID')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('Amount')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('Created at')->sort()->align(TD::ALIGN_CENTER),
                        ])
                    ]),
                    Layout::wrapper('orchid.partners.overview.latest', [
                        'header' => Layout::view('orchid.partners.overview.latest-header', ['title' => 'Latest players', 'action' => Link::make('All players')]),
                        'table' => Layout::table('players', [
                            TD::make('id', 'ID')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('email', 'Email')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('Brand')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('campaign_id', 'Campaign ID')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('Promo code')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('created_at', 'Sign up at')->render(function (Players $players){
                                return $players->created_at;
                            })->sort()->align(TD::ALIGN_CENTER),
                            TD::make('FTD date')->sort()->align(TD::ALIGN_CENTER),
                        ])
                    ]),
                    Layout::view('orchid.partners.overview.bonus', ['title' => 'Bonus codes', 'action' => Link::make('All bonus codes'), 'description' => 'No bonus codes yet'])
                ],
                'Players' => Layout::wrapper('admin.wrapperTable', [
                    'header' => Layout::view('admin.headerTable', ['title' => 'Players',
                        'action' => Link::make('New User')->class('btn btn-outline-info')]),
                    'table' => Layout::table('users', [
                        TD::make('email', 'Email')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('firstname', 'Nickname')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('Full name')->render(function (Players $model) {
                            return $model->firstname . ' ' . $model->lastname;
                        })->sort()->align(TD::ALIGN_CENTER),
                        TD::make('Skype')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('Telegram')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('Blocked')->render(function () {
                            return 'No';
                        })->sort()->align(TD::ALIGN_CENTER),
                        TD::make('action', '')
                            ->render(function () {
                                return Link::make('Edit');
                            })->sort()->align(TD::ALIGN_CENTER),
                    ])
                ]),
                'Brands' => Layout::wrapper('admin.wrapperTable', [
                    'header' => Layout::view('admin.headerTable', ['title' => 'Brands',]),
                    'table' => Layout::table('brands', [
                        TD::make('brand', 'Brand')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('campaigns_count', 'Campaigns count')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('players_count', 'Registered players')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('action', '')
                            ->render(function () {
                                return Link::make('More info');
                            })->sort()->align(TD::ALIGN_CENTER),
                    ])
                ]),
                'Documents' => Layout::wrapper('admin.wrapperTable', [
                    'header' => Layout::view('admin.headerTable', ['title' => 'Documents', 'action' => RadioButtons::make('radioButtons')
                        ->options([
                            1 => 'Active',
                            2 => 'Archived',
                        ]),
                    ]),
                ]),
                'Transactions' => Layout::wrapper('admin.mainWrapper', [
                    'col_left' => Layout::view('orchid.partners.transactions.filter', ['title' => 'Filter']),
                    'col_right' => [
                        Layout::view('orchid.partners.transactions.header-content', [
                            'title' => 'Balance transactions',
                            'action' => Button::make('ExportCSV')
                                ->class('btn btn-outline-info')
                        ]),
                        Layout::table('transactions', [
                            TD::make('id', 'ID')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('created_at', 'Created at')
                                ->render(function (Payments $model) {
                                    return $model->created_at ?? '-';
                                })
                                ->sort()->align(TD::ALIGN_CENTER),
                            TD::make('amount', 'Amount')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('currency', 'Currency')->sort()->align(TD::ALIGN_CENTER),
                            TD::make('Account type')->render(function (Payments $model){
                                switch ($model->reference){
                                    case '1':
                                        return 'partner';
                                    case '2':
                                        return "bills clip";
                                }
                            })->sort()->align(TD::ALIGN_CENTER),
                            TD::make('Reference')->render(function () {
                                return '-';
                            })->sort()->align(TD::ALIGN_CENTER),
                            TD::make('name','Actor')
                                ->render(function (Payments $model){
                                    return Link::make($model->name)->icon('user');
                                })
                                ->sort()
                                ->align(TD::ALIGN_CENTER),
                        ])
                    ]
                ]),
                'Subaffiliates' => Layout::wrapper('admin.wrapperTable', [
                    'header' => Layout::view('admin.headerTable', [
                        'title' => 'Subaffiliates',
                    ]),
                ]),
                'Marketings' => Layout::wrapper('admin.wrapperTable', [
                    'header' => Layout::view('admin.headerTable', [
                        'title' => 'Marketings',
                    ]),
                ]),
                'Comments' => Layout::wrapper('admin.wrapperTable', [
                    'header' => Layout::view('admin.headerTable', [
                        'title' => 'Comments', 'action' => Button::make('New comment')->class('btn btn-outline-info')
                    ]),
                ]),
            ]),
            Layout::modal('Balance correction', [
                Layout::rows([
                    Input::make('correction.amount')
                        ->title('Amount'),

                    Radio::make('correction.action')->value(1)->title('Action')->placeholder(Payments::ACTION[1]),
                    Radio::make('correction.action')->value(2)->placeholder(Payments::ACTION[2]),

                    Radio::make('correction.type_id')->value(1)->title('Kind')->placeholder(Payments::TYPE[1]),
                    Radio::make('correction.type_id')->value(2)->placeholder(Payments::TYPE[2]),
                    Radio::make('correction.type_id')->value(3)->placeholder(Payments::TYPE[3]),
                    Radio::make('correction.type_id')->value(4)->placeholder(Payments::TYPE[4]),
                    Radio::make('correction.type_id')->value(5)->placeholder(Payments::TYPE[5]),

                    TextArea::make('correction.comment')
                        ->rows(10)
                        ->title('Comment'),

                    Select::make('correction.brand')
                        ->title('Brand')
                        ->empty('No select', '0')
                        ->fromModel(Brands::class, 'brand'),

                    DateRange::make('correction.report')
                        ->title('Reporting period')
                ]),
            ]),
        ];
    }

    public function status_state_change(Request $request)
    {
        Partner::where('id', $request->partner_id)->update(['status_state' => $request->partner_status_state]);
        Alert::info('You have successfully change status state.');
        return redirect()->route('platform.partners.view', $request->partner_id);
    }

    public function kyc_change(Request $request)
    {
        Partner::where('id', $request->partner_id)->update(['kyc' => $request->kyc]);
        Alert::info('You have successfully change KYC verified.');
    }

    public function deposit_counter($id)
    {
        $now = Carbon::now()->timestamp;
        $subWeek = Carbon::now()->startOfWeek()->timestamp;
        $lastStartWeek = Carbon::now()->startOfWeek()->subWeek(1)->timestamp;
        $lastEndWeek = Carbon::now()->endOfWeek()->subWeek(1)->timestamp;

        $subMonth = Carbon::now()->startOfMonth()->timestamp;
        $lastStartMonth = Carbon::now()->startOfMonth()->subMonth(1)->timestamp;
        $lastEndMonth = Carbon::now()->endOfMonth()->subMonth(1)->timestamp;

        $subYear = Carbon::now()->startOfYear()->timestamp;
        $lastStartYear = Carbon::now()->startOfYear()->subYear(1)->timestamp;
        $lastEndYear = Carbon::now()->endOfYear()->subYear(1)->timestamp;
        $depositing = DB::table('deposits')
            ->leftJoin('players', 'deposits.player_id', '=', 'players.id')
            ->select(
                DB::raw("(SELECT id FROM deposits WHERE player_id = players.id AND UNIX_TIMESTAMP(created_at) BETWEEN $subWeek AND $now order by id asc limit 1) as week"),
                DB::raw("(SELECT id FROM deposits WHERE player_id = players.id AND UNIX_TIMESTAMP(created_at) BETWEEN $lastStartWeek AND $lastEndWeek order by id asc limit 1) as last_week"),
                DB::raw("(SELECT id FROM deposits WHERE player_id = players.id AND UNIX_TIMESTAMP(created_at) BETWEEN $subMonth AND $now order by id asc limit 1) as month"),
                DB::raw("(SELECT id FROM deposits WHERE player_id = players.id AND UNIX_TIMESTAMP(created_at) BETWEEN $lastStartMonth AND $lastEndMonth order by id asc limit 1) as last_month"),
                DB::raw("(SELECT id FROM deposits WHERE player_id = players.id AND UNIX_TIMESTAMP(created_at) BETWEEN $subYear AND $now order by id asc limit 1) as year"),
                DB::raw("(SELECT id FROM deposits WHERE player_id = players.id AND UNIX_TIMESTAMP(created_at) BETWEEN $lastStartYear AND $lastEndYear order by id asc limit 1) as last_year")
            )
            ->where('players.partner_id', $id)
            ->distinct()
            ->groupBy('deposits.id')
            ->get()->toArray();
        return
            [
                'week' => count(array_filter(array_column($depositing, 'week'))),
                'last_week' => count(array_filter(array_column($depositing, 'last_week'))),
                'month' => count(array_filter(array_column($depositing, 'month'))),
                'last_month' => count(array_filter(array_column($depositing, 'last_month'))),
                'year' => count(array_filter(array_column($depositing, 'year'))),
                'last_year' => count(array_filter(array_column($depositing, 'last_year'))),
            ];
    }

    public function sign_affiliate(Request $request)
    {
        $loggedInUser = Auth::guard('partner')->loginUsingId($request->partner_id);
        if (!$loggedInUser) {
            throw new Exception('Single SignOn: User Cannot be Signed In');
        }
        session()->put('emulated', $request->partner_id);
        return redirect()->route('dashboard');
    }

    public function filter_query($query, $kye)
    {
        $filter = ['week' => [], 'last_week' => [], 'month' => [], 'last_month' => [], 'year' => [], 'last_year' => []];

        foreach ($filter as $k => $value) {
            $end = Carbon::now()->timestamp;
            switch ($k) {
                case 'week':
                    $start = Carbon::now()->startOfWeek()->timestamp;
                    break;
                case 'last_week':
                    $start = Carbon::now()->startOfWeek()->subWeek(1)->timestamp;
                    $end = Carbon::now()->endOfWeek()->subWeek(1)->timestamp;
                    break;
                case 'month':
                    $start = Carbon::now()->startOfMonth()->timestamp;
                    break;
                case 'last_month':
                    $start = Carbon::now()->startOfMonth()->subMonth(1)->timestamp;
                    $end = Carbon::now()->endOfMonth()->subMonth(1)->timestamp;
                    break;
                case 'year':
                    $start = Carbon::now()->startOfYear()->timestamp;
                    break;
                case 'last_year':
                    $start = Carbon::now()->startOfYear()->subYear(1)->timestamp;
                    $end = Carbon::now()->endOfYear()->subYear(1)->timestamp;
                    break;
                default:
                    return [];
            }
            foreach ($kye as $type) {
                if ($type === 'count') {
                    $filter[$k][$type] = count(array_column(array_filter($query, function ($k, $v) use ($start, $end) {
                        return $k->count >= $start && $k->count <= $end;
                    }, ARRAY_FILTER_USE_BOTH), $type));

                } elseif ($type === 'sum') {
                    $filter[$k][$type] = array_sum(array_column(array_filter($query, function ($k, $v) use ($start, $end) {
                        return $k->count >= $start && $k->count <= $end;
                    }, ARRAY_FILTER_USE_BOTH), $type));
                }

            }

        }

        return $filter;
    }

    public function clear_filter(Request $request)
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.partners.view', $request->id);
    }

    public function apply_filter(Request $request)
    {
        Alert::success('Filter apply');
        return redirect()->route('platform.partners.view', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function balance_correction(Request $request, \App\Models\Partner $model)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'correction.action' => 'required',
            'correction_wallet_id' => 'required|integer|not_in:0',
            'correction.type_id' => 'required',
            'correction.amount' => 'required'
        ]);

        if ($validator->fails()) {
            \Orchid\Support\Facades\Toast::error("Validation Error, " . array_keys($validator->failed())[0] . " ");
        } else {
            $currency_id = $this->wallet_correction($input['correction']['action'], $input['correction_wallet_id'], $input['correction']['amount']);
            $this->payment_add($input['correction']['action'], $input['correction']['type_id'], $input['correction_wallet_id'], $model, $currency_id, $input['correction']['amount'], $input['correction']['brand'], $input['correction']['report'],);
            \Orchid\Support\Facades\Toast::success('Success, Balance Correction');
        }
    }

    public function wallet_correction($type, $wallet_id, $amount)
    {
        $balance = Wallets::query()->where('id', '=', $wallet_id)->select('balance', 'currency_id as currency')->first()->toArray();
        if ($type === '1') {
            if ($balance['balance']) {
                $balance['balance'] = (float)$balance['balance'] + (float)$amount;
            }
        }
        if ($type === '2') {
            if ($balance['balance']) {
                $balance['balance'] = (float)$balance['balance'] - (float)$amount;
            }
        }

        Wallets::query()->where('id', '=', $wallet_id)->update(['balance' => $balance['balance']]);
        return $balance['currency'];
    }

    public function payment_add($type, $kind, $wallet_id, $partner, $currency_id, $amount, $brand_id, $report): int
    {

        if ($type === '2') {
            $amount = -(float)$amount;
        }

        $staff = Auth::user()->id;
        $now = Carbon::now();
        $data = [
            'staff_id' => $staff,
            'wallet_id' => $wallet_id,
            'partner_id' => $partner->id,
            'currency_id' => $currency_id,
            'amount' => $amount,
            'type_id' => $type,
            'kind_id' => $kind,
            'brand_id' => $brand_id,
            'status' => 2,
            'approved_at' => $now,
            'finished_at' => $now,
        ];

        if($report && isset($report['start']) && isset($report['end'])){
            $data = array_merge($data, ['report_period_from' => $report['start'], 'report_period_to' => $report['end']]);
        }

        if ($partner->commission_id) {
//            $fee = Commission::query()->where('id', $partner->commission_id)->select('fee')->first()->fee ?? 0;
//            $data = array_merge($data, ['payment_system_id' => $payment_system, 'network_fee' => abs($amount) * ($fee / 100)]);
        }
        return Payments::query()->insertGetId($data);

        Payments::query()->insert($data);
    }

}
