<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class BillsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'brand', 'strategy', 'partner_id',
        'partner_email', 'partner_tags', 'fiat_currency',
        'fiat_amount_from', 'fiat_amount_to', 'crypto_amount_from',
        'crypto_amount_to', 'created_at',  'finished_at',  'reporting_period',
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

        if ($this->request->get('brand')) {
            $builder = $builder->where('brands.id', $this->request->get('brand'));
        }

        if ($this->request->get('partner_id')) {
            $builder = $builder->where('partners.id', $this->request->get('partner_id'));
        }

        if ($this->request->get('partner_email')) {
            $builder = $builder->where('partners.email', 'LIKE', "%{$this->request->get('partner_email')}%");
        }

        if (($this->request->get('fiat_amount_from') === '0' || $this->request->get('fiat_amount_from')) && $this->request->get('fiat_amount_to')) {
            $builder = $builder->whereBetween('bills.fiat_amount', [$this->request->get('fiat_amount_from'), $this->request->get('fiat_amount_to')]);
        }

        if (($this->request->get('crypto_amount_from') === '0' || $this->request->get('crypto_amount_from')) && $this->request->get('crypto_amount_to')) {
            $builder = $builder->whereBetween('bills.coin_amount', [$this->request->get('crypto_amount_from'), $this->request->get('crypto_amount_to')]);
        }

        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('bills.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }

        if ($this->request->get('finished_at') && $this->request->get('finished_at')['start'] && $this->request->get('finished_at')['end']) {
            $builder = $builder->whereBetween('bills.processed_at', [$this->request->get('finished_at')['start'], $this->request->get('finished_at')['end']]);
        }

        if ($this->request->get('reporting_period') && $this->request->get('reporting_period')['start'] && $this->request->get('reporting_period')['end']) {
            $builder = $builder->where('bills.reporting_start', '>=', $this->request->get('reporting_period')['start']);
            $builder = $builder->where('bills.reporting_end','<=' , $this->request->get('reporting_period')['end']);
        }

        return $builder;
    }


}
