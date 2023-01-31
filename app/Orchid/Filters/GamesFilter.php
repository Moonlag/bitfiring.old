<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class GamesFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'created_at', ''
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

        if ($this->request->get('provider')) {
            $builder = $builder->where('games.id', $this->request->get('provider'));
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
