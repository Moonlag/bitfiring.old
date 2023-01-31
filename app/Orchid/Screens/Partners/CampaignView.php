<?php

namespace App\Orchid\Screens\Partners;

use App\Models\Campaign;
use App\Orchid\Filters\CampaignFilter;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class CampaignView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'CampaignView';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Campaign $campaign): array
    {

       $query = Campaign::query()
           ->select(DB::raw('COUNT(players.id) as player'), DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'), 'campaigns.created_at',
              'brands.brand', 'commissions.title as commission')
           ->leftJoin('brand_partners', 'campaigns.brand_id', '=', 'brand_partners.brand_id')
           ->leftJoin('brands', 'brand_partners.brand_id', '=', 'brands.id')
           ->leftJoin('partners', 'brand_partners.partner_id', '=', 'partners.id')
           ->leftJoin('players', 'campaigns.id', '=', 'players.campaign_id')
           ->leftJoin('commissions', 'partners.commission_id', '=', 'commissions.id')
           ->where([['campaigns.id', '=' ,$campaign->id], ['brands.id', '=', $campaign->brand_id]])
           ->first();

        return [
            'overview' => [
                'basic_info' => [
                    'card_content' => [
                        'Campaign ID' => $campaign->id ?? '',
                        'Campaign Name' => $campaign->campaign ?? '',
                        'Partner' => $query->partner ?? '',
                        'Brand' => $query->brand ?? '',
                        'Commission' => $query->commission,
                        'Created at' => $campaign->created_at ?? '',
                        'Player count' => $query->player ?? 0,
                        'Promos count' => ''
                    ]
                ],
            ],
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::view('orchid.partners.overview.basic-info'),
        ];
    }
}
