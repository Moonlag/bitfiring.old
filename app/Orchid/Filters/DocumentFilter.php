<?php

namespace App\Orchid\Filters;

use App\Models\Disables;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class DocumentFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'created_at', 'status'
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
            $builder = $builder->whereBetween('documents.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }
        if ($this->request->get('status')) {
            $builder = $builder->where('documents.status', $this->request->get('status'));
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
