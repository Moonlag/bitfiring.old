<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class UsersSessionsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'player_email', 'player_id_eq',
        'device_type', 'closed_at', 'created_at'
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
        if ($this->request->get('player_email')) {
            $builder = $builder->where('players.email', 'LIKE', "%{$this->request->get('player_email')}%");
        }

        if ($this->request->get('player_id_eq')) {
            $builder = $builder->where('players.id', $this->request->get('player_id_eq'));
        }

        if ($this->request->get('device_type')) {
            $builder = $builder->where('sessions.device_type', $this->request->get('device_type'));
        }

        if ($this->request->get('closed_at') && $this->request->get('closed_at')['start'] && $this->request->get('closed_at')['end']) {
            $builder = $builder->whereBetween('sessions.closed_at', [$this->request->get('closed_at')['start'], $this->request->get('closed_at')['end']]);
        }

        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('sessions.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
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
