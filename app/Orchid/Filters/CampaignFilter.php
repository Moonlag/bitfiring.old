<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class CampaignFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [

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

        if ($this->request->get('id')) {
            $builder = $builder->where('campaigns.id', $this->request->get('id'));
        }

        if ($this->request->get('campaign')) {
            $builder = $builder->where('campaigns.campaign', 'LIKE', "%{$this->request->get('campaign')}%");
        }

        if ($this->request->get('partner_id')) {
            $builder = $builder->where('partners.id', $this->request->get('partner_id'));
        }

        if ($this->request->get('partner_email')) {
            $builder = $builder->where('partners.email', 'LIKE', "%{$this->request->get('partner_email')}%");
        }

        if ($this->request->get('brand')) {
            $builder = $builder->where('brands.brand', 'LIKE', "%{$this->request->get('brand')}%");
        }

        if ($this->request->get('commission_id')) {
            $builder = $builder->where('partners.commission_id', $this->request->get('commission_id'));
        }

        if ($this->request->get('commission_percent')) {
            $builder = $builder->where('commissions.title', $this->request->get('commission_percent'));
        }

        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')["end"]) {
            $from = $this->request->get('created_at')['start'];
            $to = $this->request->get('created_at')["end"];
            $builder = $builder->whereBetween('campaigns.created_at', [$from, $to]);
        }

        if ($this->request->get('players_from') || $this->request->get('players_from') === '0'
            && $this->request->get('players_to') || $this->request->get('players_to') === '0') {
            $from = $this->request->get('players_from');
            $to = $this->request->get('players_to');
            $builder = $builder->havingBetween('player', [$from, $to]);
        }

        return $builder;
    }


}
