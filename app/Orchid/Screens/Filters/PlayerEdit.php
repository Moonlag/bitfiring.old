<?php

namespace App\Orchid\Screens\Filters;

use App\Models\Currency;
use App\Models\FilterConditions;
use App\Models\Filters;
use App\Models\GroupPlayers;
use App\Models\Groups;
use App\Models\Sessions;
use App\Orchid\Filters\FiltersPlayersFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use function Symfony\Component\Translation\t;

class PlayerEdit extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'PlayerEdit';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'PlayerEdit';

    public $permission = [
        'platform.filters.players.edit'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Filters $model, Request $request): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->title;
            $this->description = 'id: ' . $model->id;
        }
        $filter_cond = FilterConditions::query()->where('filter_id', '=', $model->id)->get()->toArray();

        $currency = Currency::query()->get()->toArray();
        $currency = array_map(function ($cur) {
            return ['id' => $cur['id'], 'code' => $cur['code'], 'max' => 0, 'min' => 0,];
        }, $currency);

        $groups = Groups::query()
            ->select('groups.id', 'groups.title as text', 'groups.filter_id')
            ->get();

        $ids = array_column(GroupPlayers::query()->where('group_id', $model->id)->select('user_id')->get()->toArray(), 'user_id');

        $emails = \App\Models\Players::query()->select('id', 'email')->get();

        $players = \App\Models\Players::query()->whereIn('id', $ids)->select('id', 'email', 'fullname', 'created_at', 'balance')->paginate(15);

        $session = Sessions::query()->select('user_id as id', 'ip as text')->orderByDesc('user_id')->get()->unique('id')->toArray();
        return [
            'common_data' => [
                'currency' => $currency,
                'data' => $filter_cond,
                'filter' => ['name' => $model->title, 'id' => $model->id, 'count' => $model->users_count],
                'page' => 'edit',
                'groups' => $groups,
                'emails' => $emails,
                'players' => $players,
                'session' => array_values($session)
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
                ->route('platform.filters.players'),
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
            Layout::view('orchid.filters.filters-create')
        ];
    }

    public function get_players(Request $request)
    {
        if(!empty($request->get('data'))){
//        DB::enableQueryLog();
            return \App\Models\Players::filters()
                ->filtersApply([FiltersPlayersFilter::class])->select('id', 'email', 'fullname', 'created_at', 'balance')->paginate(15, '', '', $request->page ?? '');
//        dd(DB::getQueryLog());
        }
        return [];
    }

    public function update_count(Request $request)
    {
        $id = $request->id;

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        GroupPlayers::query()->where('group_id', $id)->delete();

        if(!empty($request->get('data'))){
            $data = array_map(function ($v) use ($id) {
                return array_merge($v, ['group_id' => $id]);
            }, \App\Models\Players::filters()
                ->filtersApply([FiltersPlayersFilter::class])->select('id as user_id')->get()->toArray());
            GroupPlayers::query()->insert($data);
        }

        Filters::query()->where('id', $id)->update(['users_count' => $request->count]);

        return new JsonResponse([], 200);
    }

    public function update_conditions(Request $request)
    {
        $condition = $request->data;
        $data = [];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }
        if (!empty($request->id) && !empty($request->name)) {
            $id = $request->id;
            Filters::query()->where('id', $id)->update(['title' => $request->name]);
            FilterConditions::query()->where('filter_id', $id)->delete();
            foreach ($condition as $con) {
                foreach ($con['model'] as $key => $value) {
                    $status = 1;
                    foreach ($value as $v) {
                        if (is_array($v)) {
                            foreach ($v as $tag) {
                                if (!is_array($tag)) {
                                    $data[] = [
                                        'filter_id' => $id,
                                        'condition_id' => $con['id'],
                                        'label_id' => $key,
                                        'value1' => $tag ?? null,
                                        'value2' => $value['value2'] ?? null,
                                        'value3' => $value['value3'] ?? null,
                                        'value4' => $value['value4'] ?? null,
                                    ];
                                } else {
                                    if ($key === 6) {
                                        $data[] = [
                                            'filter_id' => $id,
                                            'condition_id' => $con['id'],
                                            'label_id' => $key,
                                            'value1' => $value['value2'] ?? null,
                                            'value2' => $tag['id'] ?? null,
                                            'value3' => $tag['max'] ?? null,
                                            'value4' => $tag['min'] ?? null,
                                        ];
                                    } else {
                                        $data[] = [
                                            'filter_id' => $id,
                                            'condition_id' => $con['id'],
                                            'label_id' => $key,
                                            'value1' => $tag['id'] ?? null,
                                            'value2' => $value['value2'] ?? null,
                                            'value3' => $value['value3'] ?? null,
                                            'value4' => $value['value4'] ?? null,
                                        ];
                                    }
                                }
                            }
                            $status = 0;
                        };
                    }
                    if ($status === 1) {
                        $data[] = [
                            'filter_id' => $id,
                            'condition_id' => $con['id'],
                            'label_id' => $key,
                            'value1' => $value['value1'] ?? null,
                            'value2' => $value['value2'] ?? null,
                            'value3' => $value['value3'] ?? null,
                            'value4' => $value['value4'] ?? null,
                        ];
                    }
                }
            }

            FilterConditions::query()->insert($data);
        }
        return new JsonResponse([], 200);
    }
}
