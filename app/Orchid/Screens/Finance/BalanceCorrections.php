<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Currency;
use App\Models\Transactions;
use App\Models\User;
use App\Orchid\Filters\BalanceCorrectionsFilter;
use App\Orchid\Filters\PaymentsFilter;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
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

class BalanceCorrections extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'BalanceCorrections';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'BalanceCorrections';

    public $permission = [
        'platform.finance.balance-corrections'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $transaction = \App\Models\Transactions::filters()
            ->filtersApply([BalanceCorrectionsFilter::class])
            ->leftJoin('currency', 'transactions.currency_id', '=', 'currency.id')
            ->leftJoin('players', 'transactions.player_id', '=', 'players.id')
            ->leftJoin('payments', function ($join) {
                $join->on('transactions.reference_id', '=', 'payments.id');
            })
            ->leftJoin('users', 'payments.staff_id', '=', 'users.id')
            ->select([
                'transactions.id',
                'payments.amount',
                'currency.code as currency',
                'players.email as player',
                'users.email as staff',
                'payments.type_id as action',
                'transactions.created_at'
            ])
            ->where('transactions.reference_type_id', 1)
            ->orderBy('transactions.id', 'DESC')
            ->paginate(15);

        return [
            'table' => $transaction,
            'filter' => [
                'group' => [
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
                        ->empty('No select', 0)
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->title('Account Currency')
                        ->value((int)$request->currency),

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Input::make('email')
                        ->type('text')
                        ->title('Email')
                        ->value($request->email),

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
                        TD::make('id', 'id')->sort(),
                        TD::make('player', 'Player')->sort(),
                        TD::make('action', 'Action')->render(function (\App\Models\Transactions $model) {
                            switch ($model->action) {
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
                        })->sort(),
                        TD::make('amount', 'Amount')->sort(),
                        TD::make('currency', 'Currency')->sort(),
                        TD::make('staff', 'Admin User')->sort(),
                        TD::make('created_at', 'Created at')->render(function (Transactions $model){
                            return $model->created_at;
                        })->sort(),
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
        return redirect()->route('platform.finance.balance-corrections');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.balance-corrections', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
