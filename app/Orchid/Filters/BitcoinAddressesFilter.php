<?php

namespace App\Orchid\Filters;

use App\Models\Players;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class BitcoinAddressesFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'email', 'email_value', 'currency', 'address',
        'address_value', 'address_source', 'amount_center', 'amount_center_value',
        'created_at', 'updated_at',
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

        if ($this->request->get('currency')) {
            $builder = $builder->where('btc_address.account', $this->request->get('currency'));
        }

        if ($this->request->get('address') && $this->request->get('address') === '1' && $this->request->get('address_value')) {
            $builder = $builder->where('btc_address.address_source', 'LIKE', "%{$this->request->get('address_value')}%");
        }
        if ($this->request->get('address') && $this->request->get('address') === '2' && $this->request->get('address_value')) {
            $builder = $builder->where('btc_address.address_source', $this->request->get('address_value'));
        }
        if ($this->request->get('address') && $this->request->get('address') === '3' && $this->request->get('address_value')) {
            $builder = $builder->where('btc_address.address_source', 'LIKE', "{$this->request->get('address_value')}%");
        }
        if ($this->request->get('address') && $this->request->get('address') === '4' && $this->request->get('address_value')) {
            $builder = $builder->where('btc_address.address_source', 'LIKE', "%{$this->request->get('address_value')}");
        }

        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('btc_address.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }

        if ($this->request->get('updated_at') && $this->request->get('updated_at')['start'] && $this->request->get('updated_at')['end']) {
            $builder = $builder->whereBetween('btc_address.updated_at', [$this->request->get('updated_at')['start'], $this->request->get('updated_at')['end']]);
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
