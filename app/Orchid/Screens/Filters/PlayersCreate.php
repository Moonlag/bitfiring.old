<?php

namespace App\Orchid\Screens\Filters;


use App\Models\Currency;
use App\Models\FilterConditions;
use App\Models\Filters;
use App\Models\GroupPlayers;
use App\Models\Groups;
use App\Orchid\Filters\FiltersPlayersFilter;
use App\Orchid\Filters\PlayersFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PlayersCreate extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'PlayersCreate';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'PlayersCreate';

    public $permission = [
        'platform.filters.players.create'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $groups = Groups::query()
            ->select('groups.id', 'groups.title as text', 'groups.filter_id')
            ->get();

        $players = \App\Models\Players::query()->select('id', 'email')->get()->toArray();
        $emails = \App\Models\Players::query()->select('id', 'email')->get();

        $currency = Currency::query()->get()->toArray();
        $currency = array_map(function ($cur) {
            return ['id' => $cur['id'], 'code' => $cur['code'], 'min' => 0, 'max' => 0];
        }, $currency);

        return [
            'common_data' => [
                'groups' => $groups,
                'currency' => $currency,
                'page' => 'create',
                'players' => $players,
                'emails' => $emails
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

    public function update_count(Request $request)
    {
        $id = $request->id;

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        $data = array_map(function ($v) use ($id) {
            return array_merge($v, ['group_id' => $id]);
        }, \App\Models\Players::filters()
            ->filtersApply([FiltersPlayersFilter::class])->select('id as user_id')->get()->toArray());

        GroupPlayers::query()->where('group_id', $id)->delete();
        GroupPlayers::query()->insert($data);

        Filters::query()->where('id', $id)->update(['users_count' => $request->count]);

        return new JsonResponse([], 200);
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

    public function save_conditions(Request $request)
    {
        $condition = $request->data;
        $data = [];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['error' => 'Invalid Name'], 400);
        }

        if (!empty($request->name)) {
            $id = Filters::query()->insertGetId(['title' => $request->name]);
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
