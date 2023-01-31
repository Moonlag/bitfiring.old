<?php

namespace App\Orchid\Screens\Partners;

use App\Models\Partner;
use App\Orchid\Filters\ListOfPartnersFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use App\Models\Campaign;
use App\Orchid\Filters\CampaignFilter;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;

class Campaigns extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Campaign';

    public $request;
    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'Campaign';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.partners.campaigns'
    ];

    /**
     * Campaigns constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {

        $campaigns = Campaign::filters()
            ->filtersApply([CampaignFilter::class])
            ->select(DB::raw('COUNT(players.id) as player'), DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'), 'campaigns.id', 'partners.id as partner_id', 'campaigns.created_at',
                'campaigns.campaign', 'brands.brand', 'partners.company', 'partners.commission_id', 'partners.lastname', 'partners.firstname', 'commissions.title as commission')
            ->leftJoin('brand_partners', 'campaigns.brand_id', '=', 'brand_partners.brand_id')
            ->leftJoin('brands', 'brand_partners.brand_id', '=', 'brands.id')
            ->leftJoin('partners', 'brand_partners.partner_id', '=', 'partners.id')
            ->leftJoin('players', 'campaigns.id', '=', 'players.campaign_id')
            ->leftJoin('commissions', 'partners.commission_id', '=', 'commissions.id')
            ->groupBy('campaigns.id')
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return [
            'table' => $campaigns,
            'filter' => [
                'group' => [
                    Input::make('campaign')
                        ->type('text')
                        ->title('Name')
                        ->value($this->request->campaign)
                        ->placeholder('Name'),

                    Input::make('partner_id')
                        ->type('number')
                        ->title('Partner ID')
                        ->value($this->request->partner_id)
                        ->placeholder('Partner ID'),

                    Input::make('partner_email')
                        ->type('email')
                        ->title('Partner email')
                        ->value($this->request->partner_email)
                        ->placeholder('Partner email'),

                    Input::make('brand')
                        ->type('text')
                        ->title('Brand')
                        ->value($this->request->brand)
                        ->placeholder('Brand'),

                    Input::make('commission_id')
                        ->type('number')
                        ->title('Commission ID')
                        ->value($this->request->commission_id)
                        ->placeholder('Commission ID'),

                    Input::make('commission_percent')
                        ->type('text')
                        ->title('Commission title')
                        ->value($this->request->commission_percent)
                        ->placeholder('Commission title'),

                    Group::make([
                        Input::make('players_from')
                            ->type('number')
                            ->title('Players count')
                            ->class('form-control')
                            ->value($this->request->players_from),
                        Input::make('players_to')
                            ->type('number')
                            ->class('form-control')
                            ->value($this->request->players_to),
                    ])->alignEnd()->render(),

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($this->request->created_at),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->method('clear_filter')
                        ->class('btn btn-default')
                        ->vertical()
                    ,
                ],
            ],
            'navTable' => [
                'group' => [
                        Button::make('Export to CVS')
                            ->method('export_campaign')
                            ->rawClick()
                            ->class('btn btn-outline-info'),
                ],
                'title' => 'Campaign'
            ]
        ];
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * Views.
     *
     * @return Layout[]
     */
    public function layout(): array
    {
        return [
            Layout::wrapper('admin.mainWrapper', [
                'col_left' =>
                    Layout::view('admin.filter'),
                'col_right' => [
                    Layout::view('admin.navTable'),
                    Layout::table('table', [
                        TD::make('id', 'ID')->render(function (Campaign $campaign){
                            return Link::make($campaign->id)->route('platform.partners.campaign.view', $campaign->id)->class('btn btn-link text-primary');
                        })->sort()->align(TD::ALIGN_CENTER),
                        TD::make('campaign', 'Name')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('company', 'Partner')->sort()->render(function (Campaign $campaign){
                            return $campaign->partner;
                        })->align(TD::ALIGN_CENTER),
                        TD::make('brand', 'Brand')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('percent', 'Commission')->render(function (Campaign $campaign){
                            return '<b>' . $campaign->commission . '%</b>';
                        })->sort()->align(TD::ALIGN_CENTER),
                        TD::make('created_at', 'Created at')->sort()
                            ->render(function (Campaign $campaign) {
                                return $campaign->created_at;
                            })->align(TD::ALIGN_CENTER),
                        TD::make('player', 'Player count')->sort()->align(TD::ALIGN_CENTER),
                    ]),
                ]]),
        ];
    }

    public function export_campaign(Request $request){
        $campaigns = Campaign::filters()
            ->filtersApply([CampaignFilter::class])
            ->select(DB::raw('COUNT(players.id) as player'), DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'), 'campaigns.id', 'partners.id as partner_id', 'campaigns.created_at',
                'campaigns.campaign', 'brands.brand', 'partners.company', 'partners.commission_id', 'partners.lastname', 'partners.firstname', 'commissions.title as commission')
            ->leftJoin('brand_partners', 'campaigns.brand_id', '=', 'brand_partners.brand_id')
            ->leftJoin('brands', 'brand_partners.brand_id', '=', 'brands.id')
            ->leftJoin('partners', 'brand_partners.partner_id', '=', 'partners.id')
            ->leftJoin('players', 'campaigns.id', '=', 'players.campaign_id')
            ->leftJoin('commissions', 'partners.commission_id', '=', 'commissions.id')
            ->groupBy('campaigns.id')
            ->orderBy('id', 'DESC')
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=campaigns-'. Carbon::now()->format('Y-m-d') .'.csv'
        ];

        $columns = ['ID', 'Name', 'Partner', 'Brand', 'Commission', 'Created at', 'Player count'];

        $callback = function () use ($campaigns, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);

            foreach ($campaigns as $campaign) {
                fputcsv($stream, [
                    'ID' => $campaign->id,
                    'Name'   => $campaign->campaign,
                    'Partner'   => $campaign->partner,
                    'Brand' => $campaign->brand,
                    'Commission' => $campaign->commission,
                    'Created at'   => $campaign->created_at,
                    'Player count'  => $campaign->player,
                ]);
            }
            fclose($stream);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function clear_filter(){
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.partners.campaigns');
    }

    public function apply_filter(Request $request){
        Alert::info('Filter apply');
        return redirect()->route('platform.partners.campaigns', array_filter($request->all(), function ($k, $v){
            return $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
