<?php

namespace App\Orchid\Screens\Players;

use App\Models\Brands;
use App\Models\Campaign;
use App\Models\Currency;
use App\Models\FirstDeposit;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PlayerView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Player';


    public $id;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
        }
        $partner = Partner::query()->where('id', $model->partner_id)->select('id', DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'))->first();
        $brand = Brands::query()->where('id', $model->brand_id)->select('id', 'brand')->first();
        $campaign = Campaign::query()->where('id', $model->campaign_id)->select('id', 'tracker_aid')->first();
        $fs = FirstDeposit::query()->where('player_id', $model->id)->select('amount', 'created_at')->first();
        return [
            'overview' => [
                'basic_info' => [
                    'card_content' => [
                        'ID' => $model->id ?? '-',
                        'Player ID in casino' => $model->player_id ?? '-',
                        'STAG visit' =>  $campaign->tracker_aid ? "?aid=$campaign->tracker_aid" : '-',
                        'Email' =>  $model->email ?? '-',
                        'Brand' =>  $brand->brand ?? '-',
                        'Partner' => Link::make($partner->partner)->route('platform.partners.view', $partner->id)->class('link-primary') ?? '-',
                        'Campaign ID' =>  $model->campaign_id ?? '-',
                        'Promo code' => '-',
                        'First name' => '-',
                        'Last name' => '-',
                        'Nickname' => '-',
                        'Sign up at' =>  $model->created_at ?? '-',
                        'Created at' =>  $model->created_at ?? '-',
                        'Frozen at' =>  $model->frozen_at ?? '-',
                        'FTD date' =>  $fs->created_at ?? '-',
                        'FTD amount' =>  $fs->amount ?? '-',
                        'Date of birth' => '-',
                        'Gender' =>  '-',
                        'Country' => '-',
                        'Language' => '-',
                        'Disabled' =>  $model->status === 2 ? 'YES' : 'NO',
                        'Disabled at' =>  $model->disabled_at ?? '-',
                        'Duplicate' =>  '-',
                        'Duplicate at' =>  '-',
                        'Qualified' =>  '-',
                        'Qualified at' => '-',
                        'Access Limit' => '-',
                        'Self-excluded at' => '-',
                        'User agent' => '-',
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
