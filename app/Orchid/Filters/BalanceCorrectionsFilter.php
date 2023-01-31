<?php

namespace App\Orchid\Filters;

use App\Models\Players;
use App\Orchid\Screens\Filters\Groups;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class BalanceCorrectionsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'action', 'currency',
        'amount_cents', 'amount_cents_value', 'email',
        'created_at'
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
        if ($this->request->get('email')) {
            $builder = $builder->where('players.email', 'LIKE', "%{$this->request->get('email')}%");
        }

        if ($this->request->get('action')) {
            $builder = $builder->where('payments.type_id', $this->request->get('action'));
        }

        if ($this->request->get('currency')) {
            $builder = $builder->where('transactions.currency_id', $this->request->get('currency'));
        }

        if ($this->request->get('amount_cents') && $this->request->get('amount_cents') === '1' && $this->request->get('amount_cents_value')) {
            $builder = $builder->where('transactions.amount', '=', $this->request->get('amount_cents_value'));
        }
        if ($this->request->get('amount_cents') && $this->request->get('amount_cents') === '2' && $this->request->get('amount_cents_value')) {
            $builder = $builder->where('transactions.amount', '>', $this->request->get('amount_cents_value'));
        }
        if ($this->request->get('amount_cents') && $this->request->get('amount_cents') === '3' && $this->request->get('amount_cents_value')) {
            $builder = $builder->where('transactions.amount', '<', $this->request->get('amount_cents_value'));
        }
        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('transactions.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
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
