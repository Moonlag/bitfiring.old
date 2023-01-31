<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class FeedEventsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'created_at', 'ip', 'country', 'player_email', 'admin_email', 'event_type'
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
            $builder = $builder->whereBetween('events.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }
        if ($this->request->get('event_type')) {
            $type_id = [];
            foreach ($this->request->get('event_type') as $id => $value) {
                if ($value === 'on') {
                    $type_id[] = $id;
                }
            }
            if(!empty($type_id)){
                $builder = $builder->whereIn('event_types.id', $type_id);
            }
        }

        if($this->request->get('ip')){

            $builder = $builder->where('events.ip', 'LIKE', "%{$this->request->get('ip')}%");
        }

        if($this->request->get('country')){
            $builder = $builder->whereIn('countries.id', $this->request->get('country'));
        }

        if($this->request->get('player_email')){
            $builder = $builder->whereIn('players.email', $this->request->get('player_email'));
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
