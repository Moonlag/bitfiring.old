<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class AttendancesFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'created_at', 'path', 'path_value', 'admin_user'
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
            $builder = $builder->whereBetween('attendances.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }
        if ($this->request->get('path') && $this->request->get('path') === '1' && $this->request->get('path_value')) {
            $builder = $builder->where('attendances.path', 'LIKE', "%{$this->request->get('path_value')}%");
        }
        if ($this->request->get('path') && $this->request->get('path') === '2' && $this->request->get('path_value')) {
            $builder = $builder->where('attendances.path', 'LIKE', "{$this->request->get('path_value')}%");
        }
        if ($this->request->get('path') && $this->request->get('path') === '3' && $this->request->get('path_value')) {
            $builder = $builder->where('attendances.path', 'LIKE', "%{$this->request->get('path_value')}");
        }
        if ($this->request->get('admin_user')) {
            $builder = $builder->where('attendances.staff_id', $this->request->get('admin_user'));
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
