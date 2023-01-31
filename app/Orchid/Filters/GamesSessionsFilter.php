<?php

namespace App\Orchid\Filters;

use App\Models\GroupPlayers;
use App\Models\Players;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class GamesSessionsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'created_at', 'player_groups'
    ];


    /**
     * @return string
     */
    public function name(): string
    {
        return '';
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {

        if ($this->request->get('player_groups')) {
            $player_group = GroupPlayers::query()->whereIn('group_id', $this->request->get('player_groups'))->select('user_id')->get()->toArray();
            if(!empty($player_group)){
                $builder = $builder->whereIn('game_sessions.user_id', array_column($player_group, 'user_id'));
            }
        }

        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('game_sessions.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }

        return $builder;
    }

    /**
     * @return Field[]
     */
    public function display(): array
    {
        //
    }

}
