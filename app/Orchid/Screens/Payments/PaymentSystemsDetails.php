<?php

namespace App\Orchid\Screens\Payments;

use App\Models\PaymentSystem;
use App\Orchid\Filters\PaymentsSystemDetailsFilter;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PaymentSystemsDetails extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Payment Systems Details';

//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'PaymentSystemsDetails';

    public $permission = [
        'platform.payments.systems'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $psd = \App\Models\PaymentSystemsDetails::filters()
        ->filtersApply([PaymentsSystemDetailsFilter::class])->leftJoin('currency', 'payment_systems_details.currency_id', '=', 'currency.id')
            ->leftJoin('payment_system', 'payment_systems_details.ps_id', '=', 'payment_system.id')
            ->select('currency.code as currency', 'payment_systems_details.ps_id', 'payment_systems_details.deposit_min', 'payment_systems_details.deposit_max',
                'payment_systems_details.cashout_min', 'payment_systems_details.cashout_max')
            ->orderBy('payment_systems_details.ps_id')
            ->get()->toArray();

        $payment_id = PaymentSystem::query()->select('id', 'name', 'active')->get()->toArray();

        $data = [];
        foreach ($payment_id as $id) {
            if(in_array($id['id'], array_column($psd, 'ps_id'))){
                $data[] = array_merge($id, ['table' => array_values(array_filter($psd, function ($value, $key) use ($id) {
                    return $value['ps_id'] === $id['id'];
                }, 1))]);
            }

        }

        return [
            'payments' => $data,
            'filter' => [
                'title' => 'Filter',
                'group' => [
                    Select::make('system')
                        ->fromModel(PaymentSystem::class, 'name')
                        ->empty('No Select', 0)
                        ->title('System')
                        ->value((int)$request->system),

                    Select::make('status')
                        ->options([
                            0 => 'All',
                            1 => 'Active',
                            2 => 'Disabled'
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
            Link::make('Payment system sorting')
                ->route('platform.payments.systems.sorting')
                ->class('btn btn-secondary')
                ->icon('sort-amount-asc')
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
                    Layout::view('orchid.payment.systems_details')
                ]]
            )
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.payments.systems');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.payments.systems', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
