<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\GroupPlayers;
use App\Models\Groups;
use App\Models\PaymentSystem;
use App\Models\User;
use App\Orchid\Filters\PaymentsFilter;
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

class Gifts extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Gifts';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Gifts';

    public $permission = [
        'platform.finance.gifts'
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
            ->leftJoin('comments', function ($join) {
                $join->on('payments.id', '=', 'comments.user_id');
                $join->where('comments.section_id', '=', 2);
            })
            ->leftjoin('payment_system', 'payments.payment_system_id', '=', 'payment_system.id')
            ->where('payments.type_id', 5)
            ->select('payments.id', 'payments.email', 'payments.user_id as player_id', 'payments.amount', 'currency.code as currency', 'payments.type_id as player_action',
                'payments.status', 'payments.finished_at', 'payments.admin_id', 'users.email as admin', DB::raw('COUNT(comments.id) as comment'), 'payment_system.name as source')
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


                    Select::make('currency')
                        ->empty('No select', '0')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->value((int)$request->currency)
                        ->title('Currency'),

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Select::make('admin_user')
                        ->empty('No select', '0')
                        ->fromModel(User::class, 'email')
                        ->value((int)$request->admin_user)
                        ->title('Admin Email'),

                    Input::make('email')
                        ->type('text')
                        ->title('Player Email')
                        ->value($request->email),

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
                    Layout::table('payments', [
                        TD::make('id', 'ID')->render(function (\App\Models\Payments $model) {
                            return Link::make($model->id)
                                ->route('platform.payments.view', $model->id)
                                ->class('link-primary');
                        })
                            ->sort(),
                        TD::make('email', 'User')
                            ->render(function (\App\Models\Payments $model) {
                                $link = Link::make($model->email)->class('link-primary')
                                    ->route('platform.players.profile', $model->player_id);
                                $group = '';
                                foreach ($model->group as $kye) {
                                    $color = $kye['color'];
                                    $title = $kye['title'];
                                    $group .= "<li style='background-color: $color;'><span style='border-color: transparent transparent transparent $color;'></span>$title</li>";
                                }
                                return "<div>$link
                                    <ul class='groups'>$group</ul>
                                </div>";
                            })->sort(),
                        TD::make('amount', 'Amount')
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
                            switch ($model->status) {
                                case 1:
                                    return "<span class='bg-success px-2 pb-1 rounded'>approved</span>";
                                case 2:
                                    return "<span class='bg-primary px-2 pb-1 rounded'>pending</span>";
                                case 3:
                                    return "<span class='bg-warning px-2 pb-1 rounded'>reject</span>";
                                case 4:
                                    return "<span class='bg-danger px-2 pb-1 rounded'>error</span>";
                                default:
                                    return '-';
                            }
                        })->sort(),
                        TD::make('comment', 'Comments')->sort(),
                        TD::make('finished_at', 'Finished at')->render(function (\App\Models\Payments $model) {
                            return $model->finished_at ?? '-';
                        })->sort(),
                        TD::make('admin', 'Admin user')->render(function (\App\Models\Payments $model) {
                            return $model->admin ?? '-';
                        })->sort(),
                        TD::make('action')->render(function (\App\Models\Payments $model) {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Link::make('View')
                                        ->route('platform.payments.view', $model->id)
                                        ->class('dropdown-item')
                                        ->icon('money'),
                                ])->class('btn sharp btn-primary tp-btn');
                        })->sort(),
                    ]),
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.gifts');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.gifts', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
