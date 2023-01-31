<?php

namespace App\Orchid\Screens\Payments;

use App\Models\BtcAddress;
use App\Models\Currency;
use App\Orchid\Filters\BitcoinAddressesFilter;
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

class BitcoinAddresses extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Bitcoin Addresses';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'BitcoinAddresses';

    public $permission = [
        'platform.payments.crypto'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $btc = BtcAddress::filters()
            ->filtersApply([BitcoinAddressesFilter::class])
            ->leftJoin('players', 'btc_address.user_id', '=', 'players.id')
            ->select('players.email', 'players.id as player_id','btc_address.id', 'btc_address.address', 'btc_address.address_source', 'btc_address.currency',
                'btc_address.account')
            ->paginate();

        return [
            'btc' => $btc,
            'filter' => [
                'title' => 'Filter',
                'group' => [
                    Group::make([
                        Select::make('email')
                            ->title('Player Email')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Starts with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->email),

                        Input::make('email_value')
                            ->type('text')
                            ->value($request->email_value),
                    ])->alignEnd()->render(),

                    Select::make('currency')
                        ->title('Account Currency')
                        ->empty('No select', '0')
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code', 'code')
                        ->value($request->currency),

                    Group::make([
                        Select::make('address')
                            ->title('Address')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Equals',
                                3 => 'Starts with',
                                4 => 'Ends with',
                            ])
                            ->value((int)$request->address),

                        Input::make('address_value')
                            ->type('text')
                            ->value($request->address_value),
                    ])->alignEnd()->render(),

                    Select::make('address_source')
                        ->title('Address Source')
                        ->options([
                            1 => 'From pool',
                            2 => 'Generated',
                            3 => 'CoinsPaid',
                            4 => 'CoinsPaid Old',
                            5 => 'Utorg',
                            6 => 'Alphapo',
                            7 => 'Cubits Channel',
                            8 => 'Cubits Invoice',
                        ])
                        ->empty('No select', '0')
                        ->value((int)$request->address_source),

                    Group::make([
                        Select::make('amount_center')
                            ->title('Amount center')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Equals',
                                2 => 'Greater than',
                                3 => 'Less than',
                            ])
                            ->value((int)$request->amount_center),

                        Input::make('amount_center_value')
                            ->type('text')
                            ->value($request->amount_center_value),
                    ])->alignEnd()->render(),

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    DateRange::make('updated_at')
                        ->title('Updated at')
                        ->value($request->updated_at),

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
                    Layout::table('btc', [
                        TD::make('address', 'Address')->render(function (BtcAddress $model){
//                            'https://www.blockchain.com/btc/address/'
                            return Link::make($model->address)->class('link-primary');
                        })->sort(),
                        TD::make('address_source', 'Address Source')->sort(),
                        TD::make('currency', 'Currency')->sort(),
                        TD::make('email', 'Player')->render(function (BtcAddress $model){
                            return Link::make($model->email)
                                ->route('platform.players.profile', $model->player_id)
                                ->class('link-primary');
                        })->sort(),
                        TD::make('account', 'Account')->sort(),
                        TD::make('action')->render(function (BtcAddress $model) {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Link::make('View')
                                        ->route('platform.payments.btc_address.view', $model->id)
                                        ->class('dropdown-item')
                                        ->icon('bitcoin'),
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
        return redirect()->route('platform.payments.btc_address');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.payments.btc_address', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
