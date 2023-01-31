<?php

namespace App\Orchid\Filters;

use App\Models\GroupPlayers;
use App\Models\Players;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class GamesRelatedReportFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'player_groups', 'player', 'categories', 'provider'
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

        if ($this->request->get('player')) {
            $player_id = Players::query()->where('email', $this->request->get('player'))->select('id')->first()->id ?? 0;
            if($player_id !== 0){
                $builder = $builder->where('transactions.player_id', $player_id);
            }
        }

        if ($this->request->get('provider')) {
            $builder = $builder->whereIn('game_provider.id', $this->request->get('provider'));
        }

        if ($this->request->get('categories')) {
            $builder = $builder->whereIn('games_cats.id', $this->request->get('categories'));
        }

        if ($this->request->get('player_groups')) {
            $player_group = GroupPlayers::query()->whereIn('group_id', $this->request->get('player_groups'))->select('user_id')->get()->toArray();
            if(!empty($player_group)){
                $builder = $builder->whereIn('transactions.player_id', array_column($player_group, 'user_id'));
            }
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
