<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class DormantFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'payment_at', 'game_at'
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

        if ($this->request->get('payment_at') && $this->request->get('payment_at')['start'] && $this->request->get('payment_at')['end']) {
            $builder = $builder->whereBetween('payments.created_at', [$this->request->get('payment_at')['start'], $this->request->get('payment_at')['end']]);
        }

        if ($this->request->get('game_at') && $this->request->get('game_at')['start'] && $this->request->get('game_at')['end']) {
            $builder = $builder->whereBetween('events.created_at', [$this->request->get('game_at')['start'], $this->request->get('game_at')['end']]);
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
