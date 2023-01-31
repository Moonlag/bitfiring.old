<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Currency;
use App\Models\Partner;
use App\Models\Statement;
use App\Models\Tags;
use App\Orchid\Filters\BalanceCorrectionsFilter;
use App\Orchid\Filters\BillsFilter;
use App\Orchid\Filters\PaymentsFilter;
use App\Traits\DbPaymentsTestingTrait;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class Payments extends Screen
{
    use DbPaymentsTestingTrait;
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Payments';

    public $status = [
        0 => 0,
        1 => 0,
        2 => 0,
        3 => 0,
    ];
    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'Payments';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.finance.payments'
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
    public function query(Faker $faker): array
    {
        $payments = \App\Models\Payments::filters()
            ->filtersApply([PaymentsFilter::class])
            ->leftJoin('partners', 'payments.partner_id', '=', 'partners.id')
            ->leftJoin('currency', 'payments.currency_id', '=', 'currency.id')
            ->select('payments.id as id', 'payments.amount', 'payments.commission_amount',
                DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'),
                'payments.status', 'payments.approved_at', 'payments.finished_at', 'payments.created_at', 'currency.name as currency')
            ->groupBy('payments.id')
            ->orderBy('id', 'desc')
            ->paginate(15);


        $counter =  \App\Models\Payments::query()->select('status')->get()->toArray();
        $this->status[0] = count($counter);
        $this->status[1] = count(array_filter($counter, function ($v, $k){
            return $v['status'] === 1;
        }, 1));
        $this->status[2] = count(array_filter($counter, function ($v, $k){
            return $v['status'] === 2;
        }, 1));
        $this->status[3] = count(array_filter($counter, function ($v, $k){
            return $v['status'] === 3;
        }, 1));

        return [
            'payment' => $payments,
            'filter' => [
                'group' => [
                    Input::make('partner_id')
                        ->type('text')
                        ->title('Partner ID')
                        ->value($this->request->partner_id)
                        ->placeholder('Partner ID'),

                    Input::make('partner_email')
                        ->type('text')
                        ->title('Partner email')
                        ->value($this->request->partner_email)
                        ->placeholder('Partner email'),

                    Select::make('partner_tags')
                        ->fromModel(Tags::class, 'name')
                        ->empty('No select', '0')
                        ->multiple()
                        ->title('Partner tags')
                        ->value($this->request->partner_tags),

                    Select::make('currency')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'name')
                        ->value((int)$this->request->get('currency'))
                        ->empty('All', 0)
                        ->title('Currency'),

                    Group::make([
                        Input::make('amount_from')
                            ->type('number')
                            ->title('Amount')
                            ->placeholder('from')
                            ->value($this->request->get('amount_from')),
                        Input::make('amount_to')
                            ->type('number')
                            ->placeholder('to')
                            ->value($this->request->get('amount_to')),
                    ])->alignEnd()->render(),

                    Select::make('payments_system')
                        ->options([
                            'all' => 'All',
                            'noindex' => 'No index',
                        ])
                        ->title('Payments system')->canSee(false),

                    DateRange::make('created_at')
                        ->title('Bills created at')
                        ->value($this->request->get('created_at')),

                    DateRange::make('finished_at')
                        ->title('Bills finished at')
                        ->value($this->request->get('finished_at')),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->parameters([
                            'status' => $this->request->status ?? 0,
                            'alert' => 1
                        ])
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
                    Select::make('fiat_currency')
                        ->options([
                            'all' => 'Batch actions',
                            'noindex' => 'No index',
                        ])->disabled(true),

                    Button::make('Export to CVS')
                        ->method('export_Payments')
                        ->rawClick()
                        ->class('btn btn-info'),
                ],
                'title' => 'Payments'
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

            Button::make("All(" . $this->status[0] . ")")
                ->class(!$this->request->status ? 'btn btn-info active' : 'btn')
                ->parameters([
                    'status' => 0,
                    'alert' => 0
                ])
                ->method('apply_filter'),

            Button::make("Pending(" . $this->status[1] . ")")
                ->class($this->request->status === '1' ? 'btn btn-info active' : 'btn')
                ->parameters([
                    'status' => 1,
                    'alert' => 0
                ])
                ->method('apply_filter'),

            Button::make("Success(" . $this->status[2] . ")")
                ->class($this->request->status === '2' ? 'btn btn-info active' : 'btn')
                ->parameters([
                    'status' => 2,
                    'alert' => 0
                ])
                ->method('apply_filter'),

            Button::make("Canceled(" . $this->status[3] . ")")
                ->class($this->request->status === '3' ? 'btn btn-outline-info active' : 'btn')
                ->method('apply_filter')
                ->parameters([
                    'status' => 3,
                    'alert' => 0
                ]),

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
            Layout::wrapper('admin.mainWrapper', [
                'col_left' =>
                    Layout::view('admin.filter'),
                'col_right' => [
                    Layout::view('admin.navTable'),
                    Layout::table('payment', [
                        TD::make('id', 'ID')->render(function (\App\Models\Payments $model){
                            return Link::make($model->id)->route('platform.finance.payment.view', $model->id)->class('btn btn-primary');
                        }),
                        TD::make('partner', 'Partner')->render(function (\App\Models\Payments $model){
                            return Link::make($model->partner)->route('platform.partners.view', $model->id)->class('btn btn-primary');
                        }),
                        TD::make('amount', 'Amount'),
                        TD::make('commission_amount', 'Commissions amount'),
                        TD::make('currency', 'Currency'),
                        TD::make('created_at', 'Created at')->render(function (\App\Models\Payments $model) {
                            return $model->created_at;
                        }),
                        TD::make('approved_at', 'Approved at'),
                        TD::make('finished_at', 'Finished at'),
                        TD::make('status', 'Status')->render(function (\App\Models\Payments $model){
                            return \App\Models\Payments::STATUS[$model->status];
                        }),
                    ]),
                ]]),
        ];
    }

    public function export_Payments(Request $request){
        $payments = \App\Models\Payments::filters()
            ->filtersApply([PaymentsFilter::class])
            ->leftJoin('partners', 'payments.partner_id', '=', 'partners.id')
            ->select('payments.id as id', 'payments.amount', 'payments.commission_amount',
                DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'),
                'payments.status', 'payments.approved_at', 'payments.finished_at', 'payments.created_at')
            ->groupBy('payments.id')
            ->orderBy('id', 'desc')
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=payments-'. Carbon::now()->format('Y-m-d') .'.csv'
        ];

        $columns = ['ID', 'Partner', 'Amount', 'Commissions amount', 'Payment system', 'Created at', 'Approved at', 'Finished at', 'Status'];

        $callback = function () use ($payments, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);

            foreach ($payments as $payment) {
                fputcsv($stream, [
                    'ID' => $payment->id,
                    'Partner'   => $payment->partner,
                    'Amount' => $payment->amount,
                    'Commissions amount' => $payment->commission_amount,
                    'Payment system'   => 'CoinPaid',
                    'Created at'  => $payment->created_at,
                    'Approved at'  => $payment->approved_at,
                    'Finished at'  => $payment->finished_at,
                    'Status'  => \App\Models\Payments::STATUS[$payment->status],
                ]);
            }
            fclose($stream);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function clear_filter(){
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.payments');
    }

    public function apply_filter(Request $request){
        if($request->alert){
            Toast::info(__('Filter apply'));
        }
        return redirect()->route('platform.finance.payments', array_filter($request->all(), function ($k, $v){
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
