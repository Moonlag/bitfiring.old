<?php

namespace App\Orchid\Screens\Feed;

use App\Models\Countries;
use App\Models\EventTypes;
use App\Orchid\Filters\FeedEventsFilter;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Events extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Events';

//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'Events';

    public $permission = [
        'platform.feed.events'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $event = \App\Models\Events::filters()
            ->filtersApply([FeedEventsFilter::class])
            ->leftJoin('event_types', 'events.type_id', '=', 'event_types.id')
            ->leftJoin('countries', 'events.country_id', '=', 'countries.id')
            ->leftJoin('players', 'events.user_id', '=', 'players.id')
            ->select('events.id', 'events.subject', 'event_types.event_name as event_type', 'events.ip',
                'events.created_at', 'events.coordinates', 'countries.name as country', 'events.address')
            ->groupBy('events.id')
            ->orderBy('id', 'DESC')
            ->paginate();

        $event_type = EventTypes::query()->get();
        $type = [];
        foreach ($event_type as $kye => $value) {
            if ($kye === 0) {
                $type[] = CheckBox::make("event_type[$value->id]")
                    ->placeholder($value->event_name)
                    ->value($request->event_type[$value->id] ?? false)
                    ->title('Event Type');
            } else {
                $type[] = CheckBox::make("event_type[$value->id]")
                    ->placeholder($value->event_name)
                    ->value($request->event_type[$value->id] ?? false);
            }

        }
        $filter = array_merge([
            DateRange::make('created_at')
                ->title('Created at')
                ->value($request->created_at),

            Input::make('ip')
                ->title('IP')
                ->value($request->ip),

            Select::make('country')
                ->title('Country')
                ->empty('No select', 0)
                ->fromModel(Countries::class, 'name')
                ->value((int)$request->country),

            Input::make('player_email')
                ->title('Player email')
                ->value($request->player_email),

            Input::make('admin_email')
                ->title('Admin email')
                ->value($request->admin_email),
        ], $type);

        return [
            'tables' => $event,
            'filter' => [
                'group' => $filter,

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
                    Layout::table('tables', [
                        TD::make('id', 'ID')->sort(),
                        TD::make('subject', 'Subject')->sort(),
                        TD::make('event_type', 'Event type')->sort(),
                        TD::make('ip', 'IP')->sort(),
                        TD::make('created_at', 'Created at')->render(function (\App\Models\Events $model){
                            return $model->created_at ?? '-';
                        })->sort(),
                        TD::make('coordinates', 'Coordinates')->sort(),
                        TD::make('country', 'Country')->sort(),
                        TD::make('address', 'Address')->sort(),
                    ]),
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.feed.events');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));

        return redirect()->route('platform.feed.events', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
