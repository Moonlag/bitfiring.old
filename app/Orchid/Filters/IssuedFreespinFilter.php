<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class IssuedFreespinFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'title', 'title_value', 'user_email', 'user_email_value',
        'user_id_eq', 'currency', 'stage', 'status',
        'date_received', 'expiry_date', 'activate_until'
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
        if ($this->request->get('title') && $this->request->get('title') === '1' && $this->request->get('title_value')) {
            $builder = $builder->where('freespin_issue.title', 'LIKE', "%{$this->request->get('title_value')}%");
        }
        if ($this->request->get('title') && $this->request->get('title') === '2' && $this->request->get('title_value')) {
            $builder = $builder->where('freespin_issue.title', 'LIKE', "{$this->request->get('title_value')}%");
        }
        if ($this->request->get('title') && $this->request->get('title') === '3' && $this->request->get('title_value')) {
            $builder = $builder->where('freespin_issue.title', 'LIKE', "%{$this->request->get('title_value')}");
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
        if ($this->request->get('user_id_eq')) {
            $builder = $builder->where('freespin_issue.player_id', $this->request->get('user_id_eq'));
        }
        if ($this->request->get('currency')) {
            $builder = $builder->where('freespin_issue.currency_id', $this->request->get('currency'));
        }
        if ($this->request->get('status')) {
            $builder = $builder->where('freespin_issue.status', $this->request->get('status'));
        }
        if ($this->request->get('stage')) {
            $builder = $builder->where('freespin_issue.stage', $this->request->get('stage'));
        }
        if ($this->request->get('activate_until') && $this->request->get('activate_until')['start'] && $this->request->get('activate_until')['end']) {
            $builder = $builder->whereBetween('freespin_issue.activate_until', [$this->request->get('activate_until')['start'], $this->request->get('activate_until')['end']]);
        }
        if ($this->request->get('expiry_date') && $this->request->get('expiry_date')['start'] && $this->request->get('expiry_date')['end']) {
            $builder = $builder->whereBetween('freespin_issue.expiry_at', [$this->request->get('expiry_date')['start'], $this->request->get('expiry_date')['end']]);
        }
        if ($this->request->get('date_received') && $this->request->get('date_received')['start'] && $this->request->get('date_received')['end']) {
            $builder = $builder->whereBetween('freespin_issue.expiry_at', [$this->request->get('date_received')['start'], $this->request->get('date_received')['end']]);
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
