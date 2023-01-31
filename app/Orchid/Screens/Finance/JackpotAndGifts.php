<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\Groups;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class JackpotAndGifts extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'JackpotAndGifts';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'JackpotAndGifts';

    public $permission = [
        'platform.finance.jackpot-and-gifts'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        return [
            'table' => User::query()->get(),
            'filter' => [
                'group' => [
                    Select::make('group_by')
                        ->empty('No select', 0)
                        ->title('Group by')
                        ->value((int)$request->group_by),

                    DateRange::make('date_range')
                        ->title('Date Range')
                        ->value($request->date_range),

                    DateRange::make('exchange_rate_date')
                        ->title('Exchange Date Range')
                        ->value($request->exchange_rate_date),

                    Select::make('currency')
                        ->empty('No select', 0)
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->title('Currencies')
                        ->value((int)$request->currency),

                    Select::make('country')
                        ->title('Country')
                        ->empty('No select', '0')
                        ->fromModel(Countries::class, 'name')
                        ->value((int)$request->country),

                    Button::make('Filter')
                        ->vertical()
                        ->icon('filter')
                        ->class('btn btn-success btn-block')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->icon('refresh')
                        ->method('clear_filter')
                        ->class('btn btn-default btn-block')
                        ->vertical(),
                ],
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
                        TD::make('date', 'Date')->sort(),
                        TD::make('currency', 'Currency')->sort(),
                        TD::make('jackpot_contribution', 'Jackpot Contribution')->sort(),
                        TD::make('gifts', 'Gifts')->sort(),
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
        return redirect()->route('platform.players');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.players', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
