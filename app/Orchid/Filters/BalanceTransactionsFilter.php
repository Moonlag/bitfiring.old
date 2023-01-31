<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class BalanceTransactionsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'currency', 'amount_from', 'amount_to', 'correction_created_at', 'account_type',
        'reference', 'reference_id', 'actor', 'actor_id',
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

        if($this->request->get('account_type')){
            $builder = $builder->where('statement.reference', '=', $this->request->get('account_type'));
        }

        if ($this->request->get('correction_created_at') && $this->request->get('correction_created_at')['start'] && $this->request->get('correction_created_at')['end']) {
            $builder = $builder->whereBetween('statement.created_at', [$this->request->get('correction_created_at')['start'], $this->request->get('correction_created_at')['end']]);
        }

        if (($this->request->get('amount_from') || $this->request->get('amount_from') === '0') && $this->request->get('amount_to')) {
            $builder = $builder->where([
                ['statement.credit', '>', $this->request->get('amount_from')],
                ['statement.credit', '<', $this->request->get('amount_to')]
            ])->orWhere([
                ['statement.debit', '>', $this->request->get('amount_from')],
                ['statement.debit', '<', $this->request->get('amount_to')]
            ]);

            if ($this->request->get('correction_created_at') && $this->request->get('correction_created_at')['start'] && $this->request->get('correction_created_at')['end']) {
                $builder = $builder->whereBetween('statement.created_at', [$this->request->get('correction_created_at')['start'], $this->request->get('correction_created_at')['end']]);
            }
        }

        if($this->request->get('account_type')){
            $builder = $builder->where('statement.reference', '=', $this->request->get('account_type'));
        }


        return $builder;
    }

    public function display(): array
    {
        return [
            Input::make('email')
                ->type('text')
                ->value($this->request->get('email'))
                ->placeholder('Search...')
                ->title('Search')
        ];
    }

}
