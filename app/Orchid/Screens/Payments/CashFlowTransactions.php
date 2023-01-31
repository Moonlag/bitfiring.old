<?php

namespace App\Orchid\Screens\Payments;

use App\Models\BtcAddress;
use App\Models\Currency;
use App\Models\FeedExports;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class CashFlowTransactions extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Cash Flow Transactions';

//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'CashFlowTransactions';

    public $permission = [
        'platform.payments.cash_flow_transactions'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $exports = FeedExports::query()
            ->leftJoin('users', 'feed_exports.staff_id', '=', 'users.id')
            ->select('feed_exports.id', 'users.email as admin', 'feed_exports.type_name',
                'feed_exports.status', 'feed_exports.created_at')
            ->paginate();

        return [
            'exports' => $exports,
            'filter' => [
                'title' => 'Filter',
                'group' => [


                        DateRange::make('processed_at')
                            ->title('Processed at')
                            ->value($request->processed_at),

                        Select::make('action')
                            ->title('Action')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Payment',
                                2 => 'Commission',
                                3 => 'Client',
                                4 => 'Correction',
                                6 => 'Royalty',
                                7 => 'Other',
                            ])
                            ->value((int)$request->action),

                        TextArea::make('cash_flow')
                            ->title('Cash Flow')
                            ->value($request->cash_flow),

                        TextArea::make('config_child_system')
                            ->title('Configured child system')
                            ->value($request->config_child_system),


                    Select::make('currency')
                        ->title('Account Currency')
                        ->empty('No select', '0')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code', 'code')
                        ->value($request->currency),

                    Input::make('payment_id')
                        ->title('Payment ID')
                        ->value($request->payment_id),

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
                    Layout::table('exports', [
                        TD::make('cash_flow', 'Cash Flow')->sort(),
                        TD::make('action', 'Action')->sort(),
                        TD::make('change_amount', 'Change Amount')->sort(),
                        TD::make('amount_after_change', 'Amount After Change')->sort(),
                        TD::make('subject', 'Subject')->sort(),
                        TD::make('processed_at', 'Processed at')->sort(),
                        TD::make('')->render(function (){
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Link::make('View')
                                        ->class('dropdown-item')
                                        ->icon('pencil'),
                                ])->class('btn sharp btn-primary tp-btn');
                        })->sort(),
                    ]),
                ],
            ]),
        ];
    }
}
