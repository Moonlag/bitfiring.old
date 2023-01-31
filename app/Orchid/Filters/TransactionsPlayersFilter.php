<?php

namespace App\Orchid\Filters;


use App\Models\Sessions;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class TransactionsPlayersFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'created_at', 'reference_type',  'reference_id',
        'account_id'
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
            $builder = $builder->whereBetween('transactions.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }

        if ($this->request->get('reference_type')) {
            $builder = $builder->where('transactions.type_id', $this->request->get('reference_type'));
        }

        if ($this->request->get('reference_id')) {
            $builder = $builder->where('transactions.reference_id', $this->request->get('reference_id'));
        }

        if ($this->request->get('account_id')) {
            $builder = $builder->where('transactions.player_id', $this->request->get('account_id'));
        }

        return $builder;
    }

    /**
     * @return Field[]
     */
    public
    function display(): array
    {
        //
    }
}
