<?php

namespace App\Orchid\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Input;

class PartnerStatisticsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'company', 'firstname', 'lastname',
        'brands.id', 'partners.id', 'partner_group', 'visit_count',
        'registration_count', 'depositing_players_count', 'first_deposits_count',
        'first_deposits_sum', 'deposits_sum', 'deposits_count', 'range_date', 'brand',
        'deposit_date_range'
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
            $builder = $builder->whereIn('brands.id', $this->request->get('brand'));
        }

        if ($this->request->get('partner_email')) {
            $builder = $builder->whereIn('partners.id', $this->request->get('partner_email'));
        }

        if($this->request->get('deposit_date_range') && $this->request->get('deposit_date_range')['start'] && $this->request->get('deposit_date_range')['end']){
            $id = DB::table('first_deposits')->select('partner_id')->whereBetween('created_at',  [$this->request->get('deposit_date_range')['start'], $this->request->get('deposit_date_range')['end']])->get()->toArray();
            $builder = $builder->whereIn('partners.id', array_column($id, 'partner_id'));
        }

        if ($this->request->get('sign_date_range') && $this->request->get('sign_date_range')['start'] && $this->request->get('sign_date_range')['end']) {
            $builder = $builder->whereBetween('partners.created_at', [$this->request->get('sign_date_range')['start'], $this->request->get('sign_date_range')['end']]);
        }

        if($this->request->get('visit_count')){
            $builder = $builder->addSelect(DB::raw('(SELECT COUNT(id) FROM visits WHERE partner_id = partners.id) as visit_count'));
        }

        if($this->request->get('registration_count')){
            $builder = $builder->addSelect(DB::raw('(SELECT COUNT(id) FROM opens WHERE partner_id = partners.id) as registration_count'));
        }

        if ($this->request->get('depositing_players_count')) {
            $builder = $builder->addSelect(DB::raw('(SELECT COUNT(DISTINCT deposits.player_id) FROM deposits WHERE partner_id = partners.id) as depositing_players_count'));
        }

        if ($this->request->get('deposits_count')) {
            $builder = $builder->addSelect(DB::raw('(SELECT COUNT(id) FROM deposits WHERE partner_id = partners.id) as deposits_count'));
        }

        if ($this->request->get('deposits_sum')) {
            $builder = $builder->addSelect(DB::raw('(SELECT SUM(amount) FROM deposits WHERE partner_id = partners.id) as deposits_sum'));
        }

        if ($this->request->get('first_deposits_sum')) {
            if($this->request->get('deposit_date_range') && $this->request->get('deposit_date_range')['start'] && $this->request->get('deposit_date_range')['end']){
                $start = Carbon::parse($this->request->get('deposit_date_range')['start']);
                $end = Carbon::parse($this->request->get('deposit_date_range')['end']);
                $sql = DB::raw("(SELECT SUM(amount) FROM first_deposits WHERE partner_id = partners.id AND UNIX_TIMESTAMP(created_at) BETWEEN $start->timestamp AND $end->timestamp ) as first_deposits_sum");
            }else{
                $sql = DB::raw("(SELECT SUM(amount) FROM first_deposits WHERE partner_id = partners.id  ) as first_deposits_sum");
            }
            $builder = $builder->addSelect($sql);
        }

        if ($this->request->get('first_deposits_count')) {
            $builder = $builder->addSelect(DB::raw('(SELECT COUNT(id) FROM first_deposits WHERE partner_id = partners.id ) as first_deposits_count'));
        }

        if ($this->request->get('partner_income')) {
            $builder = $builder->addSelect(\DB::raw('(SELECT SUM(amount) FROM deposits WHERE partner_id = partners.id) as partner_income'));
        }



//        $builder = $builder->leftJoin('brand_partners', function ($join) {
//            $join->on('partners.id', '=', 'brand_partners.partner_id');
//        })->leftJoin('brands', function ($join){
//            $join->on('brand_partners.brand_id', '=', 'brands.id');

//        });
//
//        $builder = $builder->leftJoin('campaigns', function ($join) {
//            $join->on('brands.id', '=', 'campaigns.brand_id');
//            if ($this->request->get('range_date') && $this->request->get('range_date')['start'] && $this->request->get('range_date')['end']) {
//                $join->whereBetween('campaigns.created_at', [$this->request->get('range_date')['start'], $this->request->get('range_date')['end']]);
//            }
//            if ($this->request->get('campaign_id')) {
//                $join->whereIn('campaigns.id', $this->request->get('campaign_id'));
//            }
//        });
//
//
//        $builder = $builder->Join('players', function ($join) {
//            $join->on('campaigns.id', '=', 'players.campaign_id');
//            if ($this->request->get('range_date') && $this->request->get('range_date')['start'] && $this->request->get('range_date')['end']) {
//                $join->whereBetween('players.created_at', [$this->request->get('range_date')['start'], $this->request->get('range_date')['end']]);
//            }
//            if ($this->request->get('player_id')) {
//                $join->whereIn('players.id', $this->request->get('player_id'));
//            }
//        });
//
//
//        $builder = $builder->leftJoin('deposits', function ($join) {
//            $join->on('players.id', '=', 'deposits.player_id');
//            if ($this->request->get('range_date') && $this->request->get('range_date')['start'] && $this->request->get('range_date')['end']) {
//                $join->whereBetween('deposits.created_at', [$this->request->get('range_date')['start'], $this->request->get('range_date')['end']]);
//            }
//            $join->selectRaw('SUM(deposits.amount) as deposits_sum');
//            if($this->request->get('player_email')){
//                $join->whereIn('players.id', $this->request->get('player_email'));
//            }
//
//            if($this->request->get('player_countries')){
//                $join->whereIn('players.countries_id', explode(', ', $this->request->get('player_countries')));
//            }
//        });
//
//        $builder = $builder->leftJoin('first_deposits', function ($join){
//           $join->on('players.id', '=', 'first_deposits.player_id');
//        });
//
//
//        if ($this->request->get('range_date') && $this->request->get('range_date')['start'] && $this->request->get('range_date')['end']) {
//            $builder = $builder->whereBetween('partners.created_at', [$this->request->get('range_date')['start'], $this->request->get('range_date')['end']]);
//        }
//
//        if ($this->request->get('partner_id')) {
//            $builder = $builder->whereIn('partners.id', $this->request->get('partner_id'));
//        }
//
//        if ($this->request->get('partner_email')) {
//            $builder = $builder->whereIn('partners.id', $this->request->get('partner_email'));
//        }
//
//        if ($this->request->get('registration_count') || $this->request->get('visit_count')) {
//            $select = array_merge($select, ['partners.id as partner_id']);
//        }
//
//        if ($this->request->get('depositing_players_count')) {
//            $select = array_merge($select, [\DB::raw('COUNT(DISTINCT deposits.player_id) as depositing_players_count')]);
//        }
//
//        if ($this->request->get('first_deposits_count')) {
//            $select = array_merge($select, [\DB::raw('COUNT(first_deposits.id) as first_deposits_count')]);
//        }
//
//        if ($this->request->get('first_deposits_sum')) {
//            $select = array_merge($select, [\DB::raw('SUM(first_deposits.amount) as first_deposits_sum')]);
//        }
//
////        if ($this->request->get('deposits_sum')) {
////            $select = array_merge($select, [\DB::raw('SUM(deposits.amount) as deposits_sum')]);
////        }
//
//        if ($this->request->get('deposits_count')) {
//            $select = array_merge($select, [\DB::raw('COUNT(deposits.id) as deposits_count')]);
//        }
//        $select = array_merge($select,  array('campaigns.id as campaigns_id'));
//        $builder = $builder->select($select);
        if ($this->request->get('partner_group')) {
            $builder = $builder->groupBy('partners.id');
        }
        return $builder;
    }

    public function display(): array
    {
        return [
            Input::make('partner_id')
                ->type('text')
                ->value($this->request->get('partner_id'))
                ->title('partner_id')
        ];
    }
}
