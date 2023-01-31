<?php

namespace App\Orchid\Filters;

use App\Models\Players;
use App\Orchid\Screens\Filters\Groups;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class PaymentsSystemDetailsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'system', 'status'
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
        if ($this->request->get('system')) {
            $builder = $builder->where('payment_systems_details.ps_id', '=', $this->request->get('system'));
        }
        if ($this->request->get('status')) {
            $builder = $builder->where('payment_system.active', '=', $this->request->get('status'));
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
