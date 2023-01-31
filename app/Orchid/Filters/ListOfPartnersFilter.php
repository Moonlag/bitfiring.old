<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;


class ListOfPartnersFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [];

    /**
     * @return string
     */
    public function name(): string
    {
        return 'Partners';
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {


        if($this->request->get('partner_id')){
            $builder = $builder->where('partners.id', $this->request->get('partner_id'));
        }

        if($this->request->get('email')){
            $builder = $builder->where('partners.email', $this->request->get('email'));
        }

        if($this->request->get('company')){
            $builder = $builder->where('partners.company', $this->request->get('company'))
                ->orWhere('partners.firstname', $this->request->get('company'));
        }

        if($this->request->get('status_state')){
            $builder = $builder->where('partners.status_state', $this->request->get('status_state'));
        }

        return $builder;
    }

    /**
     * @return Field[]
     */
    public function display(): array
    {
        return [

        ];
    }
}
