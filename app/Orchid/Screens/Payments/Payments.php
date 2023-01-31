<?php

namespace App\Orchid\Screens\Payments;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\Games;
use App\Models\GamesCats;
use App\Models\GroupPlayers;
use App\Models\Groups;
use App\Models\PaymentSystem;
use App\Models\Players;
use App\Models\User;
use App\Orchid\Filters\PaymentsFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Payments extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Payments';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'Payments';

    public $permission = [
        'platform.payments'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $payments = \App\Models\Payments::filters()
            ->filtersApply([PaymentsFilter::class])
            ->leftJoin('currency', function ($join) {
                $join->on('payments.currency_id', '=', 'currency.id');
            })
            ->leftJoin('users', 'payments.staff_id', '=', 'users.id')
            ->Join('players', 'payments.user_id', '=', 'players.id')
            ->leftJoin('partners', 'players.partner_id', '=', 'partners.id')
            ->leftJoin('comments', function ($join) {
                $join->on('payments.id', '=', 'comments.user_id');
                $join->where('comments.section_id', '=', 2);
            })
            ->leftjoin('payment_system', 'payments.payment_system_id', '=', 'payment_system.id')
            ->select('payments.id', 'payments.source_address as wallet', 'payments.email', 'payments.user_id as player_id', 'payments.amount', 'payments.requested', 'currency.code as currency', 'currency.rate', 'payments.type_id as player_action',
                'payments.status', 'payments.finished_at', 'payments.created_at', 'payments.admin_id', 'users.email as admin', DB::raw('COUNT(comments.id) as comment'), 'payment_system.name as source', 'payment_system.link_address as link', 'partners.email as partner')
            ->groupBy('payments.id')
            ->orderBy('id', 'DESC')
            ->paginate(20);

        $groups = GroupPlayers::query()
            ->leftJoin('groups', 'group_players.group_id', '=', 'groups.id')
            ->select('group_players.user_id as id', 'groups.title', 'groups.color')->get()->toArray();

        $total = \App\Models\Payments::query()->select('status')->get()->toArray();

        foreach ($payments->items() as $item) {
            $item->group = array_values(array_filter($groups, function ($k, $v) use ($item) {
                return $item->player_id === $k['id'];
            }, 1));
        }


        $transactions_total = \App\Models\Payments::query()->RightJoin('currency', function ($join) {
            $join->on('payments.currency_id', '=', 'currency.id');
        })->select('currency.code', DB::raw('SUM(payments.amount) as amount'))
            ->groupBy('currency.code')->get();

        return [
            'payments' => $payments,
            'transactions_total' => $transactions_total,
            'filter' => [
                'title' => 'Filter',
                'group' => [
                    Select::make('payment_system')
                        ->empty('No select', 0)
                        ->fromModel(PaymentSystem::class, 'name')
                        ->title('Payment system')
                        ->value((int)$request->payment_system),

//                    Select::make('child_system')
//                        ->empty('No select', 0)
//                        ->title('Child system')
//                        ->options([
//                            'bank' => 'Bank (bank)',
//                            'neosurf' => 'NeoSurf (neosurf)',
//                            'paysafecard' => 'Paysafecard (paysafecard)',
//                            'skrill' => 'Skrill (skrill)',
//                            'neteller' => 'Neteller (neteller)',
//                            'online_bank_transfer' => 'Online Bank Transfer (online_bank_transfer)',
//                            'eco_payz' => 'EcoPayZ (eco_payz)',
//                            'creditcard' => 'Credit Card (creditcard)',
//                            'qiwi' => 'Qiwi (qiwi)',
//                            'idebit' => 'iDebit (idebit)',
//                            'pugglepay' => 'Zimpler (pugglepay)',
//                            'vcc' => 'Virtual Card Payouts (vcc)',
//                            'bankintl' => 'Bank International (bankintl)',
//                            'interac' => 'Interac (interac)',
//                            'mobile_commerce' => 'Mobile Commerce (mobile_commerce)',
//                            'card' => 'Card payments (card)',
//                            'iwallet' => 'iWallet (iwallet)',
//                            'sticpay' => 'SticPay (sticpay)',
//                            'banklocal' => 'Bank domestic withdrawal (banklocal)',
//                            'siru' => 'Siru (siru)',
//                            'mifinity' => 'MiFinity (mifinity)',
//                            'muchbetter' => 'MuchBetter (muchbetter)',
//                            'skrillqco' => 'Skrill Quick Checkout (skrillqco)',
//                        ])
//                        ->value($request->child_system)
//                        ->canSee(false),

                    Select::make('action')
                        ->empty('No select', '0')
                        ->options([
                            0 => 'All',
                            1 => 'Add',
                            2 => 'Subtract',
                            3 => 'Deposit',
                            4 => 'Cashout',
                            5 => 'Gifts',
                            6 => 'Chargeback',
                            7 => 'Refund',
                            8 => 'Reversal',
                        ])
                        ->title('Action')
                        ->value((int)$request->action),

                    Select::make('currency')
                        ->empty('No select', '0')
                        ->fromQuery(Currency::query()->where('parent_id', '=', 0), 'code')
                        ->value((int)$request->currency)
                        ->title('Currency'),

                    Group::make([
                        Select::make('amount_cents')
                            ->title('Amount cents')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Equals',
                                2 => 'Greater than',
                                3 => 'Less than',
                            ])
                            ->value((int)$request->amount_cents),

                        Input::make('amount_cents_value')
                            ->type('text')
                            ->value($request->amount_cents_value),
                    ])->alignEnd()->render(),

                    Input::make('email')
                        ->type('text')
                        ->title('Email')
                        ->value($request->email),

                    Input::make('partner')
                        ->type('text')
                        ->value($request->partner)
                        ->title('Partner Email:'),

                    Select::make('user_groups')
                        ->fromModel(Groups::class, 'title')
                        ->taggable()
                        ->multiple()
                        ->title('User groups')
                        ->value($request->user_groups),

                    Select::make('admin_user')
                        ->empty('No select', '0')
                        ->fromModel(User::class, 'name')
                        ->value((int)$request->admin_user)
                        ->title('Admin user'),

                    Group::make([
                        Select::make('payment_code')
                            ->title('Payment code')
                            ->empty('No select', 0)
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Starts with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->payment_code),

                        Input::make('payment_code_value')
                            ->type('text')
                            ->value($request->payment_code_value),
                    ])->alignEnd()->render(),

                    DateRange::make('finished_at')
                        ->title('Finished at')
                        ->value($request->finished_at),

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Select::make('country')
                        ->fromModel(Countries::class, 'name', 'code')
                        ->empty('No select', '0')
                        ->value($request->country)
                        ->title('Country'),

                    Select::make('status')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Approved',
                            2 => 'Pending',
                            3 => 'Rejected',
                            4 => 'Error',
                        ])
                        ->title('Status')
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
            ],
            'scope' => [
                'title' => 'Scope Counters',
                'thead' => [
                    'Scope Name' => 'Count'
                ],
                'table' => [
                    'All' => count($total),
                    'Approved' => count(array_filter($total, function ($v, $k) {
                        return $v['status'] === 1;
                    }, 1)),
                    'Pending' => count(array_filter($total, function ($v, $k) {
                        return $v['status'] === 2;
                    }, 1)),
                    'Rejected' => count(array_filter($total, function ($v, $k) {
                        return $v['status'] === 3;
                    }, 1)),
                    'Error' => count(array_filter($total, function ($v, $k) {
                        return $v['status'] === 4;
                    }, 1)),
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
                    Layout::table('payments', [
                        TD::make('id', 'ID')->render(function (\App\Models\Payments $model) {
                            return Link::make($model->id)
                                ->route('platform.payments.view', $model->id)
                                ->class('link-primary');
                        })
                            ->sort(),
                        TD::make('email', 'E-mail')->render(function (\App\Models\Payments $payments) {
                            $model = \App\Models\Players::find($payments->player_id);
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
                        TD::make('amount', 'Amount')
                            ->render(function (\App\Models\Payments $model) {
                                $amount = $model->currency !== 'USDT' ? usdt_helper($model->amount / $model->rate, '') : false;
                                $balance = usdt_helper($model->amount ?? '0.00', $model->currency);
                                $subbalance = ($amount ? " ( " . usdt_helper($amount, 'USDT') . " USDT)" : '');
                                return "<div class='d-flex flex-column'>
                    <span>$balance</span>
                    <span style='font-size: 12px'>$subbalance</span>
                </div>";
                            })
                            ->sort(),
                        TD::make('requested', 'Requested')
                            ->render(function (\App\Models\Payments $model) {
                                $amount = $model->currency !== 'USDT' ? usdt_helper($model->requested / $model->rate, '') : false;
                                $balance = usdt_helper($model->requested ?? '0.00', $model->currency);
                                $subbalance = ($amount ? " ( " . usdt_helper($amount, 'USDT') . " USDT)" : '');
                                return "<div class='d-flex flex-column'>
                    <span>$balance</span>
                    <span style='font-size: 12px'>$subbalance</span>
                </div>";
                            })
                            ->sort(),
                        TD::make('wallet', 'Recipient wallet')->render(function (\App\Models\Payments $model){
                            return Link::make($model->wallet)
                                ->class('link-primary')
                                ->target('_blank')
                                ->href($model->link.$model->wallet);
                        })
                            ->sort(),
                        TD::make('currency', 'Currency')->sort(),
                        TD::make('player_action', 'Action')
                            ->render(function (\App\Models\Payments $model) {
                                switch ($model->player_action) {
                                    case 1:
                                        return 'Add';
                                    case 2:
                                        return 'Subtract';
                                    case 3:
                                        return 'Deposit';
                                    case 4:
                                        return 'Cashout';
                                    case 5:
                                        return 'Gifts';
                                    case 6:
                                        return 'Chargeback';
                                    case 7:
                                        return 'Refund';
                                    case 8:
                                        return 'Reversal';
                                    default:
                                        return '-';
                                }
                            })
                            ->sort(),
                        TD::make('source', 'Source')->width(90)->render(function (\App\Models\Payments $model) {
                            return $model->source ?? 'Manual';
                        })->sort(),
                        TD::make('status', 'Status')->render(function (\App\Models\Payments $model) {
                            $class = '';
                            switch ($model->status) {
                                case 1:
                                    $class = 'badge-success';
                                    break;
                                case 2:
                                    $class = 'badge-primary';
                                    break;
                                case 3:
                                    $class = 'badge-info';
                                    break;
                                case 4:
                                    $class = 'badge-danger';
                                    break;
                                case 5:
                                    $class = 'badge-info';
                                    break;
                            }
                            return "<span class='badge light $class'>" . $model::STATUS[$model->status] . "</span>";
                        })->sort(),
                        TD::make('created_at', 'Date')->render(function (\App\Models\Payments $model) {
                            return $model->created_at ?? '-';
                        })->width(100)->sort(),
                        TD::make('admin', 'Admin user')->render(function (\App\Models\Payments $model) {
                            return $model->admin ?? '-';
                        })->sort(),
                        TD::make('partner', 'Partner Email')->render(function (\App\Models\Payments $model){
                            return $model->partner ?? '-';
                        })->sort(),
                    ]),
                ],
                'col_right' => [
                    Layout::view('orchid.players.scope-countres'),
                    Layout::wrapper('orchid.wrapper-table', [
                        'title' => Layout::view('orchid.table-header', ['title' => 'Transactions total']),
                        'table' => Layout::table('transactions_total', [
                            TD::make('code', 'Currency'),
                            TD::make('amount', 'Transaction total')->render(function (\App\Models\Payments $model) {
                                return $model->amount ?? '0.00';
                            }),
                        ])
                    ]),
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.payments');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.payments', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
