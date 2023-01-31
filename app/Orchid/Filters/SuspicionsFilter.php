<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class SuspicionsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = ['player_id', 'duplicate_suspicion', 'discarded',
        'created_at', 'update_at', 'failed_check'
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
        if ($this->request->get('player_id')) {
            $builder = $builder->where('suspicions.user_id', $this->request->get('player_id'));
        }

        if ($this->request->get('discarded')) {
            switch ($this->request->get('discarded')) {
                case '1':
                    $discarded = 1;
                    break;
                case '2':
                    $discarded = 0;
                    break;
                default:
                    $discarded = 0;
            }
                $builder = $builder->where('suspicions.active', $discarded);

        }


        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('suspicions.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }

        if ($this->request->get('updated_at') && $this->request->get('updated_at')['start'] && $this->request->get('updated_at')['end']) {
            $builder = $builder->whereBetween('suspicions.updated_at', [$this->request->get('updated_at')['start'], $this->request->get('updated_at')['end']]);
        }

        if ($this->request->get('failed_check')) {
            $builder = $builder->where('suspicions.reason_id', $this->request->get('failed_check'));
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
