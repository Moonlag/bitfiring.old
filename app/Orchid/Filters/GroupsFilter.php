<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class GroupsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'published', 'public', 'writable',
        'permanent', 'dynamic', 'status', 'technical',
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

        if ($this->request->get('published')) {
            $builder = $builder->where('groups.published', $this->request->get('published'));
        }
        if ($this->request->get('public')) {
            $builder = $builder->where('groups.public', $this->request->get('public'));
        }
        if ($this->request->get('writable')) {
            $builder = $builder->where('groups.writable', $this->request->get('writable'));
        }
        if ($this->request->get('permanent')) {
            $builder = $builder->where('groups.permanent', $this->request->get('permanent'));
        }
        if ($this->request->get('dynamic')) {
            $builder = $builder->where('groups.dynamic', $this->request->get('dynamic'));
        }
        if ($this->request->get('status')) {
            $builder = $builder->where('groups.status', $this->request->get('status'));
        }
        if ($this->request->get('technical')) {
            $builder = $builder->where('groups.technical', $this->request->get('technical'));
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
