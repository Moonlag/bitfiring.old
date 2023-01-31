<?php

namespace App\Orchid\Screens;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\Languages;
use App\Models\SuspicionTypes;
use App\Models\Tags;
use App\Orchid\Filters\SuspicionsFilter;
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

class Suspicions extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Suspicions';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'Suspicions';


    public $permission = [
        'platform.suspicions'
    ];
    /**
     * Query data.
     *
     * @param Request $request
     * @return array
     */
    public function query(Request $request): array
    {
        $suspicions = \App\Models\Suspicions::filters()
            ->filtersApply([SuspicionsFilter::class])
            ->select('players.email', 'players.id as player_id', 'suspicion_types.suspicion_name', 'suspicions.created_at', 'suspicions.updated_at')
            ->leftJoin('players', 'suspicions.user_id', '=', 'players.id')
            ->leftJoin('suspicion_types', 'suspicions.reason_id', '=', 'suspicion_types.id')
            ->paginate(20);

        return [
            'suspicions' => $suspicions,
            'filter' => [
                'group' => [
                    Input::make('player_id')
                        ->type('number')
                        ->title('Player id')
                        ->value($request->player_id),

                    Select::make('duplicate_suspicion')
                        ->title("Include duplicate's suspicions")
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->value((int)$request->duplicate_suspicion),

                    Select::make('discarded')
                        ->title("Discarded")
                        ->empty('No select', 0)
                        ->options([
                            1 => 'Yes',
                            2 => 'No',
                        ])
                        ->value((int)$request->discarded),

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    DateRange::make('updated_at')
                        ->title('Updated at')
                        ->value($request->updated_at),

                    Select::make('failed_check')
                        ->empty('No select', 0)
                        ->fromModel(SuspicionTypes::class, 'suspicion_name')
                        ->value((int)$request->failed_check)
                        ->title('Failed check'),
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
                    Layout::table('suspicions', [
                        TD::make('email', 'Suspect')->render(function (\App\Models\Suspicions $model){
                            return Link::make($model->email)->class('link-primary')
                                ->route('platform.players.profile', $model->player_id);
                        })
                            ->sort(),
                        TD::make('suspicion_name', 'Reason')
                            ->sort(),
                        TD::make('created_at', 'Created at')
                            ->render(function (\App\Models\Suspicions $model) {
                                return $model->created_at;
                            })
                            ->sort(),
                        TD::make('updated_at', 'Updated at')
                            ->render(function (\App\Models\Suspicions $model) {
                                return $model->updated_at;
                            })
                            ->sort(),
                    ]),
                ]
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.suspicions');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.suspicions', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
