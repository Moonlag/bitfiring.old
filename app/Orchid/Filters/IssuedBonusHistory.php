<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class IssuedBonusHistory extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'group_key', 'group_key_value', 'user_email', 'user_email_value',
        'strategy', 'created_at'
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
            $builder = $builder->whereBetween('bonus_issue_history.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }
        if ($this->request->get('group_key') && $this->request->get('group_key') === '1' && $this->request->get('group_key_value')) {
            $builder = $builder->where('bonus_issue_history.group_key', 'LIKE', "%{$this->request->get('group_key_value')}%");
        }
        if ($this->request->get('group_key') && $this->request->get('group_key') === '2' && $this->request->get('group_key_value')) {
            $builder = $builder->where('bonus_issue_history.group_key', 'LIKE', "{$this->request->get('group_key_value')}%");
        }
        if ($this->request->get('group_key') && $this->request->get('group_key') === '3' && $this->request->get('group_key_value')) {
            $builder = $builder->where('bonus_issue_history.group_key', 'LIKE', "%{$this->request->get('group_key_value')}");
        }
        if ($this->request->get('user_email') && $this->request->get('user_email') === '1' && $this->request->get('user_email_value')) {
            $builder = $builder->where('players.email', 'LIKE', "%{$this->request->get('user_email_value')}%");
        }
        if ($this->request->get('user_email') && $this->request->get('user_email') === '2' && $this->request->get('user_email_value')) {
            $builder = $builder->where('players.email', 'LIKE', "{$this->request->get('user_email_value')}%");
        }
        if ($this->request->get('user_email') && $this->request->get('user_email') === '3' && $this->request->get('user_email_value')) {
            $builder = $builder->where('players.email', 'LIKE', "%{$this->request->get('user_email_value')}");
        }
        if ($this->request->get('strategy')) {
            $builder = $builder->where('bonus_issue_history.strategy_id', $this->request->get('strategy'));
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
