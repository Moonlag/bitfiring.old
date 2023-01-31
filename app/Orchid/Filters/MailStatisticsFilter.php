<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class MailStatisticsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'email', 'sort'
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
        if ($this->request->get('email', false)) {
            $builder = $builder->where('email', 'LIKE', "%{$this->request->get('email')}%");
        }

        switch ($this->request->get('sort')) {
            case '-id':
                $builder->orderBy('id', 'DESC');
                break;
            case 'id':
                $builder->orderBy('id', 'ASC');
                break;
            case '-email':
                $builder->orderBy('email', 'DESC');
                break;
            case 'email':
                $builder->orderBy('email', 'ASC');
                break;
            case '-created_at':
                $builder->orderBy('created_at', 'DESC');
                break;
            case 'created_at':
                $builder->orderBy('created_at', 'ASC');
                break;
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
