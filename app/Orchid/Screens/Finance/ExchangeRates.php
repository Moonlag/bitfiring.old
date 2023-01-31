<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Currency;
use App\Models\User;
use App\Orchid\Filters\ExchangesFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ExchangeRates extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'ExchangeRates';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'ExchangeRates';

    public $permission = [
        'platform.finance.exchange-rates'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $currency = Currency::filters()
            ->filtersApply([ExchangesFilter::class])
            ->join('currency_exchange_history', 'currency.id', '=', 'currency_exchange_history.currency_id')
            ->select(DB::raw('(SELECT code FROM currency WHERE id = currency_exchange_history.currency_to) as currency_to'), 'currency.code as currency_from', 'currency_exchange_history.rate',
                'currency_exchange_history.created_at', 'currency_exchange_history.source')
            ->get();

        return [
            'table' => $currency,
            'filter' => [
                'group' => [

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Select::make('from')
                        ->empty('No select', 0)
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->title('From EQ')
                        ->value((int)$request->from),

                    Select::make('to')
                        ->empty('No select', 0)
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->title('To EQ')
                        ->value((int)$request->to),

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
                        TD::make('currency_from', 'From')->sort(),
                        TD::make('currency_to', 'To')->sort(),
                        TD::make('rate', 'Rate')->sort(),
                        TD::make('source', 'Rate Source')->sort(),
                        TD::make('created_at', 'Created at')->render(function (Currency $model){
                            return $model->created_at;
                        })->sort(),
                        TD::make('timestamp', 'Timestamp')->render(function (Currency $model){
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
        return redirect()->route('platform.finance.exchange-rates');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.exchange-rates', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
