<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Brands;
use App\Models\Currency;
use App\Models\Tags;
use App\Models\Transactions;
use App\Orchid\Filters\BankrollTransactionsFilter;
use App\Orchid\Filters\BillsFilter;
use App\Traits\DbPlayersTestingTrait;
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
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;

class Bills extends Screen
{
    use DbPlayersTestingTrait;

    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Bills';
    public $pending;
    public $paid;
    public $canceled;
    public $all;
    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'Bills';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.finance.bills'
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
    public function query(Request $request): array
    {

        $bills = \App\Models\Bills::filters()
            ->filtersApply([BillsFilter::class])
            ->leftJoin('partners', 'bills.partner_id', '=', 'partners.id')
            ->Join('brand_partners', 'partners.id', '=', 'brand_partners.partner_id')
            ->Join('brands', 'brand_partners.brand_id', '=', 'brands.id')
            ->leftJoin('users', 'bills.user_id', '=', 'users.id')
            ->select('bills.id as id',
                'brands.brand as brand',
                'bills.fiat_amount', 'bills.coin_amount', DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'),
                'bills.state', 'users.name as operator', 'bills.reporting_start', 'bills.reporting_end', 'bills.processed_at', 'bills.created_at')
            ->groupBy('bills.id')
            ->orderBy('id', 'desc')
            ->paginate(15);


        $this->pending = 0;
        $this->paid = 0;
        $this->canceled = 0;
        $this->all = 0;

        return [
            'bills' => $bills,
            'filter' => [
                'group' => [
                    Select::make('brand')
                        ->empty('No select', '0')
                        ->fromModel(Brands::class, 'brand')
                        ->value((int)$this->request->brand)
                        ->title('Brand'),

                    Input::make('strategy')
                        ->type('text')
                        ->title('Strategy')
                        ->value($this->request->strategy)
                        ->placeholder('Strategy'),

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

                    Select::make('fiat_currency')
                        ->options([
                            0 => 'All',
                            1 => 'AUD',
                        ])
                        ->value((int)$this->request->fiat_currency)
                        ->title('Fiat currency'),

                    Group::make([
                        Input::make('fiat_amount_from')
                            ->type('number')
                            ->value($this->request->get('fiat_amount_from'))
                            ->title('Fiat amount'),
                        Input::make('fiat_amount_to')
                            ->type('number')
                            ->value($this->request->get('fiat_amount_to')),
                    ])->alignEnd()->render(),

                    Select::make('currency')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'name')
                        ->empty('All', 0)
                        ->value((int)$this->request->crypto_currency)
                        ->title('Currency'),

                    Group::make([
                        Input::make('crypto_amount_from')
                            ->type('number')
                            ->value($this->request->get('crypto_amount_from'))
                            ->title('Crypto amount'),
                        Input::make('crypto_amount_to')
                            ->type('number')
                            ->value($this->request->get('crypto_amount_to')),
                    ])->alignEnd()->render(),

                    DateRange::make('created_at')
                        ->title('Bills created at')
                        ->value($this->request->get('created_at')),

                    DateRange::make('finished_at')
                        ->title('Bills finished at')
                        ->value($this->request->get('finished_at')),

                    DateRange::make('reporting_period')
                        ->title('Reporting period')
                        ->value($this->request->get('reporting_period')),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->method('clear_filter')
                        ->class('btn btn-default')
                        ->vertical()
                ],
            ],
            'navTable' => [
                'group' => [
                    Select::make('fiat_currency')
                        ->options([
                            'all' => 'Batch actions',
                            'noindex' => 'No index',
                        ])->disabled(true),

                    Button::make('Export to CVS')
                        ->rawClick()
                        ->method('export_Bills')
                        ->class('btn btn-info'),
                ],
                'title' => 'Bills'
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
            Button::make("All($this->all)")
                ->class(!$this->request->status_state ? 'btn btn-info active' : 'btn')
                ->parameters([
                    'status_state' => 0
                ])
                ->method('apply_filter'),

            Button::make("To pay($this->pending)")
                ->class($this->request->status_state === '1' ? 'btn btn-info active' : 'btn')
                ->parameters([
                    'status_state' => 1
                ])
                ->method('apply_filter'),

            Button::make("Paid($this->paid)")
                ->class($this->request->status_state === '2' ? 'btn btn-info active' : 'btn')
                ->parameters([
                    'status_state' => 2
                ])
                ->method('apply_filter'),

            Button::make("Canceled($this->canceled)")
                ->class($this->request->status_state === '3' ? 'btn btn-outline-info active' : 'btn')
                ->method('apply_filter')
                ->parameters([
                    'status_state' => 3
                ]),

            Button::make("BILL")
                ->class('btn btn-outline-info active')
                ->method('bill')
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
                    Layout::table('bills', [
                        TD::make('id', 'ID'),
                        TD::make('brand', 'Brand'),
                        TD::make('commission', 'Commission')->render(function (\App\Models\Bills $model) {
                            return $model->commission ? $model->commission . '%' : '-';
                        }),
                        TD::make('fiat_amount', 'Fiat amount'),
                        TD::make('coin_amount', 'Coin amount'),
                        TD::make('partner', 'Partner'),
                        TD::make('state', 'State')->render(function (\App\Models\Bills $model) {
                            return \App\Models\Bills::STATE[$model->state];
                        }),
                        TD::make('operator', 'Operator'),
                        TD::make('processed_at', 'Processed at'),
                        TD::make('created_at', 'Created at')->render(function (\App\Models\Bills $model) {
                            return $model->created_at;
                        }),
                        TD::make('reporting_start', 'Reporting period start date'),
                        TD::make('reporting_end', 'Reporting period end date'),
                    ]),
                ]]),
        ];
    }

    public function export_Bills(Request $request)
    {
        $bills = \App\Models\Bills::filters()
            ->filtersApply([BillsFilter::class])
            ->leftJoin('partners', 'bills.partner_id', '=', 'partners.id')
            ->Join('brand_partners', 'partners.id', '=', 'brand_partners.partner_id')
            ->Join('brands', 'brand_partners.brand_id', '=', 'brands.id')
            ->leftJoin('users', 'bills.user_id', '=', 'users.id')
            ->select('bills.id as id',
                'brands.brand as brand',
                'bills.fiat_amount', 'bills.coin_amount', DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'),
                'bills.state', 'users.name as operator', 'bills.reporting_start', 'bills.reporting_end', 'bills.processed_at', 'bills.created_at')
            ->groupBy('bills.id')
            ->orderBy('id', 'desc')
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=bills-' . Carbon::now()->format('Y-m-d') . '.csv'
        ];

        $columns = ['ID', 'Brand', 'Commission', 'Fiat amount', 'Coin amount', 'Partner', 'State', 'Operator', 'Processed at', 'Created at', 'Reporting period start date', 'Reporting period end date'];

        $callback = function () use ($bills, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);

            foreach ($bills as $bill) {
                fputcsv($stream, [
                    'ID' => $bill->id,
                    'Brand' => $bill->brand,
                    'Commission' => $bill->commission,
                    'Fiat amount' => $bill->fiat_amount,
                    'Coin amount' => $bill->coin_amount,
                    'Partner' => $bill->partner,
                    'State' => \App\Models\Bills::STATE[$bill->state],
                    'Operator' => $bill->operator,
                    'Processed at' => $bill->processed_at,
                    'Created at' => $bill->created_at,
                    'Reporting period start date' => $bill->reporting_start,
                    'Reporting period end date' => $bill->reporting_end,
                ]);
            }
            fclose($stream);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function clear_filter(){
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.bills');
    }

    public function apply_filter(Request $request){
        Alert::success('Filter apply');
        return redirect()->route('platform.finance.bills', array_filter($request->all(), function ($k, $v){
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function bill(){

            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $get_null_bill = DB::table('brand_partners')
                ->leftJoin('bills', function ($join) use ($start, $end){
                    $join->on('bills.brand_id', '=', 'brand_partners.brand_id')
                        ->on('bills.partner_id', '=', 'brand_partners.partner_id')
                        ->whereBetween('bills.created_at', [$start, $end]);
                })
                ->select('brand_partners.partner_id', 'brand_partners.brand_id')
                ->whereNull('bills.id')
                ->groupBy('brand_partners.id')
                ->first();
            
        if($get_null_bill){
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $now = Carbon::now();
            $bp = DB::table('brand_partners')
                ->leftJoin('partners', 'brand_partners.partner_id', '=', 'partners.id')
                ->where('brand_partners.partner_id', '=', $get_null_bill->partner_id)
                ->where('brand_partners.brand_id', '=', $get_null_bill->brand_id)
                ->select('brand_partners.brand_id', 'brand_partners.partner_id', 'partners.commission_id',
                    DB::raw("(SELECT SUM(amount) FROM deposits WHERE partner_id = brand_partners.partner_id AND brand_id = brand_partners.brand_id AND UNIX_TIMESTAMP(created_at) BETWEEN $start->timestamp AND $end->timestamp ) as fiat_amount"))
                ->groupBy('brand_partners.id')
                ->first();

            dd(array_merge((array)$bp, ['state' => rand(1, 3), 'reporting_start' => $start, 'reporting_end' => $end, 'processed_at' => $now, 'created_at' => $now]));
        }
    }
}
