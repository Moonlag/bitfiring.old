<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;

class PostbacksLogFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'postback_actions', 'response_code', 'postback_url',
        'player_id', 'promo_id', 'promo_codes',
        'campaign_id', 'campaign_name', 'partner_id',
        'partner_name', 'partner_email', 'sent_at'
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

        if ($this->request->get('postback_actions')) {
            $builder = $builder->where('postback_action.id', $this->request->get('postback_actions'));
        }

        if ($this->request->get('response_code')) {
            $builder = $builder->where('postbacks.code', $this->request->get('response_code'));
        }

        if ($this->request->get('postback_url')) {
            $builder = $builder->where('postbacks_log.url', 'LIKE', "%{$this->request->get('postback_url')}%");
        }

        if ($this->request->get('partner_email')) {
            $builder = $builder->where('partners.email', 'LIKE', "%{$this->request->get('partner_email')}%");
        }

        if ($this->request->get('partner_id')) {
            $builder = $builder->where('partners.id', $this->request->get('partner_id'));
        }

        if ($this->request->get('player_id')) {
            $builder = $builder->where('players.id', $this->request->get('player_id'));
        }

        if ($this->request->get('campaign_id')) {
            $builder = $builder->where('campaigns.id', $this->request->get('campaign_id'));
        }

        if ($this->request->get('campaign_name')) {
            $builder = $builder->where('campaigns.campaign', $this->request->get('campaign_name'));
        }

        if ($this->request->get('sent_at') && $this->request->get('sent_at')['start'] && $this->request->get('sent_at')["end"]) {
            $from = $this->request->get('sent_at')['start'];
            $to = $this->request->get('sent_at')["end"];
            $builder = $builder->whereBetween('postbacks_log.created_at', [$from, $to]);
        }

        if($this->request->get('partner_name')){
            $builder = $builder->where('partners.company', $this->request->get('partner_name'))
                ->orWhere('partners.firstname', $this->request->get('partner_name'))
                ->orWhere('partners.lastname', $this->request->get('partner_name'));
        }

        return $builder;
    }


}
