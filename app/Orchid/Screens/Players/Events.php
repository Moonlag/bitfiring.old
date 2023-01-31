<?php

namespace App\Orchid\Screens\Players;

use App\Models\Countries;
use App\Models\EventTypes;
use App\Orchid\Filters\FeedEventsFilter;
use App\Orchid\Layouts\ViewPlayerLatestEventsTable;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
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

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Events';

    public $id;

    public $permission = [
        'platform.players.events'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model, Request $request): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
        }

        $event = \App\Models\Events::filters()
            ->filtersApply([FeedEventsFilter::class])->where('events.user_id', $model->id)
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
                ->value((int)$request->country)
            ,
        ], $type);
        $filter = array_merge($filter, [
            Input::make('player_email')
                ->title('Player email')
                ->value($request->player_email),

            Input::make('admin_email')
                ->title('Admin email')
                ->value($request->admin_email),

        ]);
        return [
            'latest_events' => $event,
            'filter' => [
                'title' => 'Filter',
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
        return [
            Link::make('Return')
                ->icon('left')
                ->class('btn btn-outline-secondary mb-2')
                ->route('platform.players.profile', $this->id)
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
            Layout::table('latest_events', [
                TD::make('created_at', 'Date')->render(function ($latest_events){
                    return $latest_events->created_at ?? '-';
                })->sort(),
                TD::make('subject', 'Subject')->sort(),
                TD::make('event_type', 'Event type')->sort(),
                TD::make('ip', 'IP')->sort(),
                TD::make('country', 'Country')->sort(),
                TD::make('address', 'Address')->sort(),
                TD::make('coordinates', 'Coordinates')->sort(),
            ])
        ];
    }

    public function clear_filter(Request $request)
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.players.events', ['id' => $request->id]);
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));

        return redirect()->route('platform.players.events', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
