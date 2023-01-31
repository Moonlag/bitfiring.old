<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Currency;
use App\Models\Transactions;
use App\Orchid\Filters\BankrollTransactionsFilter;
use App\Orchid\Filters\BillsFilter;
use App\Orchid\Filters\PaymentsFilter;
use App\Traits\DbTransactionsTestingTrait;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;

class BankrollTransactions extends Screen
{

    use DbTransactionsTestingTrait;

    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Bankroll Transactions';

    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'BankrollTransactions';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.finance.bankroll-transactions'
    ];

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
    public function query(): array
    {

        $transactions = Transactions::filters()
            ->filtersApply([BankrollTransactionsFilter::class])
            ->leftJoin('partners', 'transactions.user_id', '=', 'partners.id')
            ->leftJoin('currency', 'transactions.currency_id', '=', 'currency.id')
            ->select('transactions.id', 'transactions.action', 'transactions.amount', 'currency.name as currency',
                'transactions.reference_type as reference', 'transactions.transaction', 'transactions.created_at')
            ->groupBy('transactions.id')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return [
            'transactions' => $transactions,
            'filter' => [
                'group' => [
                    Select::make('reference_type')
                        ->options(Transactions::REFERENCE)
                        ->empty('All', 0)
                        ->title('Reference type')
                        ->value((int)$this->request->reference_type),

                    Select::make('currency')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'name')
                        ->empty('All', 0)
                        ->value((int)$this->request->currency)
                        ->title('Currency'),

                    Group::make([
                        Input::make('amount_from')
                            ->type('number')
                            ->value($this->request->get('amount_from'))
                            ->title('Amount'),
                        Input::make('amount_to')
                            ->type('number')
                            ->value($this->request->get('amount_to')),
                    ])->alignEnd()->render(),

                    DateRange::make('transaction')
                        ->title('Transaction')
                        ->value($this->request->get('transaction')),

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($this->request->get('created_at')),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->method('clear_filter')
                        ->class('btn btn-default')
                        ->vertical()

                ],
                'title' => 'Filter'
            ],
            'navTable' => [
                'group' => [
                    Button::make('Export to CVS')
                        ->method('export_Payments')
                        ->rawClick()
                        ->class('btn btn-info'),
                ],
                'title' => 'Bankroll transactions'
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
        return [];
    }

    /**
     * Views.
     *
     * @return Layout[]
     */
    public function layout(): array
    {
        return [
            Layout::wrapper('admin.mainWrapper', [
                'col_left' =>
                    Layout::view('admin.filter'),
                'col_right' => [
                    Layout::view('admin.navTable'),
                    Layout::table('transactions', [
                        TD::make('id', 'ID'),
                        TD::make('action', 'Action')->render(function (Transactions $model) {
                            return Transactions::ACTIONS[$model->action];
                        }),
                        TD::make('amount', 'Amount'),
                        TD::make('currency', 'Currency'),
                        TD::make('reference', 'Reference')->render(function (Transactions $model) {
                            return Transactions::REFERENCE[$model->reference];
                        }),
                        TD::make('actor', 'Actor'),
                        TD::make('created_at', 'Created')->render(function (Transactions $model) {
                            return $model->created_at ?? '-';
                        }),
                    ]),
                ]]),
        ];
    }

    public function export_Payments(Request $request)
    {
        $transactions = Transactions::filters()
            ->filtersApply([BankrollTransactionsFilter::class])
            ->leftJoin('users', 'transactions.users_id', '=', 'users.id')
            ->select('transactions.id', 'transactions.action', 'users.name as actor', 'transactions.amount', 'transactions.currency_id as currency',
                'transactions.reference_type as reference', 'transactions.transaction', 'transactions.created_at')
            ->groupBy('transactions.id')
            ->orderBy('id', 'desc')
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=transaction-' . Carbon::now()->format('Y-m-d') . '.csv'
        ];

        $columns = ['ID', 'Action', 'Amount', 'Currency', 'Reference', 'Actor', 'Created'];

        $callback = function () use ($transactions, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);

            foreach ($transactions as $transaction) {
                fputcsv($stream, [
                    'ID' => $transaction->id,
                    'Action' => Transactions::REFERENCE[$transaction->action],
                    'Amount' => $transaction->amount,
                    'Currency' => $transaction->currency,
                    'Reference' => Transactions::REFERENCE[$transaction->reference],
                    'Actor' => $transaction->actor,
                    'Created' => $transaction->created_at,
                ]);
            }
            fclose($stream);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.bankroll-transactions');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.bankroll-transactions', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
