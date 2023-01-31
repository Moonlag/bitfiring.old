<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class BankrollTransactionsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'reference_type', 'currency', 'amount_from', 'amount_to',
        'created_at', 'transaction'
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

        if ($this->request->get('reference_type')) {
            $builder = $builder->where('transactions.reference_type', $this->request->get('reference_type'));
        }

        if (($this->request->get('amount_from') === '0' || $this->request->get('amount_from')) && $this->request->get('amount_to')) {
            $builder = $builder->whereBetween('transactions.amount', [$this->request->get('amount_from'), $this->request->get('amount_to')]);
        }

        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('transactions.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }

        if ($this->request->get('transaction') && $this->request->get('transaction')['start'] && $this->request->get('transaction')['end']) {
            $builder = $builder->whereBetween('transactions.transaction', [$this->request->get('transaction')['start'], $this->request->get('transaction')['end']]);
        }

        return $builder;
    }


}
