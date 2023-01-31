<?php

namespace App\Orchid\Filters;

use App\Models\Disables;
use App\Models\Sessions;
use App\Models\TagItem;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class GameSessionsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'email', 'email_value', 'player_id_eq', 'ident',
        'ident_value', 'created_at'
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


        if ($this->request->get('email') && $this->request->get('email') === '1' && $this->request->get('email_value')) {
            $builder = $builder->where('players.email', 'LIKE', "%{$this->request->get('email_value')}%");
        }
        if ($this->request->get('email') && $this->request->get('email') === '2' && $this->request->get('email_value')) {
            $builder = $builder->where('players.email', $this->request->get('email_value'));
        }
        if ($this->request->get('email') && $this->request->get('email') === '3' && $this->request->get('email_value')) {
            $builder = $builder->where('players.email', 'LIKE', "{$this->request->get('email_value')}%");
        }
        if ($this->request->get('email') && $this->request->get('email') === '4' && $this->request->get('email_value')) {
            $builder = $builder->where('players.email', 'LIKE', "%{$this->request->get('email_value')}");
        }

        if ($this->request->get('player_id_eq')) {
            $builder = $builder->where('game_sessions.user_id', $this->request->get('player_id_eq'));
        }

        if ($this->request->get('ident') && $this->request->get('ident') === '1' && $this->request->get('ident_value')) {
            $builder = $builder->where('game_sessions.ident', 'LIKE', "%{$this->request->get('ident_value')}%");
        }
        if ($this->request->get('ident') && $this->request->get('ident') === '2' && $this->request->get('ident_value')) {
            $builder = $builder->where('game_sessions.ident', $this->request->get('ident_value'));
        }
        if ($this->request->get('ident') && $this->request->get('ident') === '3' && $this->request->get('ident_value')) {
            $builder = $builder->where('game_sessions.ident', 'LIKE', "{$this->request->get('ident_value')}%");
        }
        if ($this->request->get('ident') && $this->request->get('ident') === '4' && $this->request->get('ident_value')) {
            $builder = $builder->where('game_sessions.ident', 'LIKE', "%{$this->request->get('ident_value')}");
        }

        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('game_sessions.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
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
