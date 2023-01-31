<?php

namespace App\Orchid\Screens;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\Groups;
use App\Models\PaymentSystem;
use App\Models\User;
use App\Orchid\Filters\AttendancesFilter;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Attendances extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Attendances';

    public $permission = [
        'platform.attendances'
    ];

//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'Attendances';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $attendances = \App\Models\Attendances::filters()
            ->filtersApply([AttendancesFilter::class])
            ->leftJoin('users', 'attendances.staff_id', '=', 'users.id')
            ->select('attendances.id', 'users.email as admin', 'attendances.path', 'attendances.http_method as format', 'attendances.created_at')
            ->orderBy('id', 'DESC')
            ->paginate();

        return [
            'attendances' => $attendances,
            'filter' => [
        'group' => [
            Group::make([
                Select::make('path')
                    ->title('Path')
                    ->empty('No select', '0')
                    ->options([
                        1 => 'Contains',
                        2 => 'Starts with',
                        3 => 'Ends with',
                    ])
                    ->value((int)$request->path),

                Input::make('path_value')
                    ->type('text')
                    ->value($request->path_value),
            ])->alignEnd()->render(),

            Select::make('admin_user')
                ->empty('No select', 0)
                ->fromModel(User::class, 'email')
                ->title('Admin User')
                ->value((int)$request->admin_user),

            DateRange::make('created_at')
                ->title('Created at')
                ->value($request->created_at),

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
                    Layout::table('attendances', [
                        TD::make('id', 'ID')->render(function (\App\Models\Attendances $model){
                            return Link::make($model->id)->route('platform.attendance.view', $model->id)->class('link-primary');
                        })->sort(),
                        TD::make('path', 'Path')->sort(),
                        TD::make('admin', 'Admin User')->sort(),
                        TD::make('created_at', 'Created at')->render(function (\App\Models\Attendances $model){
                            return $model->created_at ?? '-';
                        })->sort(),
                    ])
                ],
            ]),

        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.attendances');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.attendances', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
