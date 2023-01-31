<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class CommissionsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'brand',
        'strategy', 'schedule_plan', 'default',
        'allow_subaffiliates', 'state'
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

        if ($this->request->get('brand')) {
            $builder = $builder->where('commissions.brand_id', $this->request->get('brand'));
        }

        if ($this->request->get('strategy')) {
            $builder = $builder->where('commissions.strategy', $this->request->get('strategy'));
        }

        if ($this->request->get('schedule_plan')) {
            $builder = $builder->where('commissions.schedule_plan', $this->request->get('schedule_plan'));
        }

        if ($this->request->get('default')) {
            switch ($this->request->get('default')){
                case '1':
                    $default = 1;
                    break;
                case '2':
                    $default = 0;
                    break;
                default:
                    $default = 1;
            }
            $builder = $builder->where('commissions.default', $default);
        }

//        if ($this->request->get('allow_subaffiliates')) {
//            $builder = $builder->where('commission.allow_subaffiliates', $this->request->get('allow_subaffiliates'));
//        }

        if ($this->request->get('state')) {
            $builder = $builder->where('commissions.state', $this->request->get('state'));
        }

        return $builder;
    }


}
