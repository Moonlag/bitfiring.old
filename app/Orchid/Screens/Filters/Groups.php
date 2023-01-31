<?php

namespace App\Orchid\Screens\Filters;

use App\Models\Events;
use App\Models\EventTypes;
use App\Models\Filters;
use App\Models\GroupEvents;
use App\Orchid\Filters\GroupsFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class Groups extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Groups';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Groups';

    public $permission = [
        'platform.filters.groups'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $group = $this->groups($request);
        $filter = Filters::query()->select('id', 'title')->get()->toArray();
        $events = EventTypes::query()->select('id', 'event_name as title')->get()->toArray();

        return [
            'group' => $group,
            'filter' => $filter,
            'events' => $events,
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
            ->class('btn btn-outline-secondary mb-2')
            ->icon('left')
            ->route('platform.players'),
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
            Layout::view('orchid.filters.filters-groups'),
        ];
    }

    public function save(Request $request)
    {

        $result = $request->create;
        $validator = Validator::make($result, [
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['error' => 'Invalid Name'], 400);
        }

        \App\Models\Groups::query()->insert($result);

        $table = $this->groups($request);
        return new JsonResponse(['table' => $table, 'create' => 1], 200);
    }

    public function update(Request $request)
    {

        $result = $request->update;
        $id = $result['id'];

        if ($result['event']) {
            $events = array_map(function ($el) use ($id) {
                return ['event_id' => $el, 'group_id' => $id];
            }, $result['event']);
        }

        $validator = Validator::make($result, [
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['error' => 'Invalid Name'], 400);
        }

        \App\Models\Groups::query()->where('id', $id)->update([
            "title" => $result['title'],
            "color" => $result['color'],
            "filter_id" => $result['filter'],
            "published" => $result['published'],
            "public" => $result['public'],
            "writable" => $result['writable'],
            "permanent" => $result['permanent'],
            "dynamic" => $result['dynamic'],
            "status" => $result['status'],
            "technical" => $result['technical'],
            "description" => $result['description'],
            "internal_id" => $result['internal_id']
        ]);

        GroupEvents::query()->where('group_id', $id)->delete();
        if (isset($events)) {
            GroupEvents::query()->insert($events);
        }

        $table = $this->groups($request);
        return new JsonResponse(['table' => $table, 'update' => 1,], 200);
    }

    public function update_table(Request $request)
    {

        $table = $this->groups($request);
        return new JsonResponse(['table' => $table ?? []], 200);

    }

    public function groups(Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $table = \App\Models\Groups::filters()
            ->filtersApply([GroupsFilter::class])
            ->leftJoin('filters', 'groups.filter_id', '=', 'filters.id')
            ->select('groups.id', 'groups.title', 'filters.title as filter', 'filters.id as filter_id', 'groups.updated_at', 'groups.color',
                'groups.published', 'groups.public', 'groups.writable', 'groups.permanent', 'groups.dynamic', 'groups.status', 'groups.technical', 'groups.description', 'filters.users_count as count')
            ->orderBy('groups.id', 'DESC')
            ->paginate(50, '', '', $request->page ?? '');

        $group_event = $this->groups_event();
        foreach ($table->withQueryString()->items() as $key => $v) {
            $table->withQueryString()->items()[$key]['events'] = array_column(array_filter($group_event, function ($evt) use ($v) {
                return $evt['id'] === $v->id;
            }, 1), 'event_id');
        }

        return $table;
    }

    public function groups_event(): array
    {
        return GroupEvents::query()->select('group_id as id', 'event_id')->get()->toArray();
    }
}
