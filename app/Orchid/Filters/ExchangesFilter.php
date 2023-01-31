<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class ExchangesFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'created_at', 'from', 'to',
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

        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('currency_exchange_history.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }
        if ($this->request->get('from')) {
            $builder = $builder->where('currency_exchange_history.currency_id', $this->request->get('from'));
        }
        if ($this->request->get('to')) {
            $builder = $builder->where('currency_exchange_history.currency_to', $this->request->get('to'));
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
