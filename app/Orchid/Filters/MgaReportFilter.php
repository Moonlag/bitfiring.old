<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class MgaReportFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = ['provider', 'categories'
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
            $builder = $builder->whereIn('games.provider_id', $this->request->get('provider'));
        }

        if ($this->request->get('categories')) {
            $builder = $builder->whereIn('games_cats.id', $this->request->get('categories'));
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
