<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class IssuedBonusesFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'title', 'title_value', 'user_email', 'user_email_value',
        'user_id_eq', 'currency', 'stage', 'status', 'amount_cents',
        'amount_cents_value', 'amount_wager_cents', 'amount_wager_cents_value',
        'date_received', 'expiry_date'
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
        if ($this->request->get('title') && $this->request->get('title') === '1' && $this->request->get('title_value')) {
            $builder = $builder->where('components.title', 'LIKE', "%{$this->request->get('title_value')}%");
        }
        if ($this->request->get('title') && $this->request->get('title') === '2' && $this->request->get('title_value')) {
            $builder = $builder->where('components.title', 'LIKE', "{$this->request->get('title_value')}%");
        }
        if ($this->request->get('title') && $this->request->get('title') === '3' && $this->request->get('title_value')) {
            $builder = $builder->where('components.title', 'LIKE', "%{$this->request->get('title_value')}");
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
            $builder = $builder->where('bonus_issue.user_id', $this->request->get('user_id_eq'));
        }
        if ($this->request->get('currency')) {
            $builder = $builder->where('components.currency_id', $this->request->get('currency'));
        }

        if ($this->request->get('status')) {
            $builder = $builder->where('bonus_issue.status', $this->request->get('status'));
        }
        if ($this->request->get('stage')) {
            $builder = $builder->where('bonus_issue.stage', $this->request->get('stage'));
        }

        if ($this->request->get('amount_cents') && $this->request->get('amount_cents') === '1' && $this->request->get('amount_cents_value')) {
            $builder = $builder->where('bonus_issue.amount', '=', $this->request->get('amount_cents_value'));
        }
        if ($this->request->get('amount_cents') && $this->request->get('amount_cents') === '2' && $this->request->get('amount_cents_value')) {
            $builder = $builder->where('bonus_issue.amount', '>', $this->request->get('amount_cents_value'));
        }
        if ($this->request->get('amount_cents') && $this->request->get('amount_cents') === '3' && $this->request->get('amount_cents_value')) {
            $builder = $builder->where('bonus_issue.amount', '<', $this->request->get('amount_cents_value'));
        }

        if ($this->request->get('amount_wager_cents') && $this->request->get('amount_wager_cents') === '1' && $this->request->get('amount_wager_cents_value')) {
            $builder = $builder->where('bonus_issue.wagered', '=', $this->request->get('amount_wager_cents_value'));
        }
        if ($this->request->get('amount_wager_cents') && $this->request->get('amount_wager_cents') === '2' && $this->request->get('amount_wager_cents_value')) {
            $builder = $builder->where('bonus_issue.wagered', '>', $this->request->get('amount_wager_cents_value'));
        }
        if ($this->request->get('amount_wager_cents') && $this->request->get('amount_wager_cents') === '3' && $this->request->get('amount_wager_cents_value')) {
            $builder = $builder->where('bonus_issue.wagered', '<', $this->request->get('amount_wager_cents_value'));
        }


        if ($this->request->get('expiry_date') && $this->request->get('expiry_date')['start'] && $this->request->get('expiry_date')['end']) {
            $builder = $builder->whereBetween('bonus_issue.expiry_at', [$this->request->get('expiry_date')['start'], $this->request->get('expiry_date')['end']]);
        }

        if ($this->request->get('date_received') && $this->request->get('date_received')['start'] && $this->request->get('date_received')['end']) {
            $builder = $builder->whereBetween('bonus_issue.expiry_at', [$this->request->get('date_received')['start'], $this->request->get('date_received')['end']]);
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
