<?php

namespace App\Orchid\Screens\Filters;

use App\Models\Conditions;
use App\Models\FilterConditions;
use App\Models\Filters;
use App\Models\Groups;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Players extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Filters';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Filters';


    public $permission = [
        'platform.filters.players'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $groups = Filters::query()
            ->select('filters.id', 'filters.title as name')
            ->paginate();

        $conditions = FilterConditions::query()
            ->select('filter_conditions.filter_id', 'conditions.name as condition')
            ->leftJoin('conditions', function ($join) {
                $join->on('filter_conditions.condition_id', '=', 'conditions.id');
            })
            ->distinct('conditions.name')->get()->toArray();
        foreach ($groups as $key => $group) {
            $id = $group->id;
            $groups[$key]['conditions'] = json_encode(array_filter(array_map(function ($con) use ($id) {
                if ($con['filter_id'] === $id) {
                    return $con['condition'];
                }
            }, $conditions), function ($k, $v){
                return $k !== null;
            }, 1));
        }

        return [
            'groups' => $groups,
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
            Link::make('New Filter')
                ->icon('plus')
                ->class('btn btn-secondary mb-2')
                ->route('platform.filters.players.create')
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
            Layout::table('groups', [
                TD::make('id', 'ID'),
                TD::make('name', 'Name'),
                TD::make('conditions', 'Conditions')->render(function (Filters $model) {
                    $data = json_decode($model->conditions);
                    $str = '';
                    if(!empty($data)){
                        foreach ($data as $value){
                            $str .= "<div class='badge badge-rounded badge-lg badge-outline-primary me-2'>".Str::slug($value)."</div>";
                        }
                    }
                    return $str;
                }),
                TD::make('Action')->render(function (Filters $model) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->class('btn sharp btn-primary tp-btn')
                        ->list([
                            Link::make('Edit')
                                ->route('platform.filters.players.edit', ['id' => $model->id])
                                ->class('dropdown-item')
                                ->icon('pencil'),
                            Link::make('View')
                                ->route('platform.players.profile', $model->id)
                                ->class('dropdown-item')
                                ->icon('user'),
                        ]);
                }),
            ])
        ];
    }
}
