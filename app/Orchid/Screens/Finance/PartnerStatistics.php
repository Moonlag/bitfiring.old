<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use App\Orchid\Filters\PartnerStatisticsFilter;
use App\Orchid\Layouts\Table\PartnerStatisticsTable;
use Orchid\Support\Facades\Toast;
use App\Models\Brands;
use App\Models\Campaign;
use App\Models\Players;

class PartnerStatistics extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Partner Statistics';
    public $canSee = false;
    public $partner;
    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'PartnerStatistics';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.finance.partner-statistics'
    ];


    public function __construct(Request $request)
    {
        $request['partner_group'] = isset($request['partner_group']) ? $request['partner_group'] : 1;
        $request['visit_count'] = isset($request['visit_count']) ? $request['visit_count'] : 1;
        $request['registration_count'] = isset($request['registration_count']) ? $request['registration_count'] : 1;
        $request['first_deposits_count'] = isset($request['first_deposits_count']) ? $request['first_deposits_count'] : 1;
        $request['first_deposits_sum'] = isset($request['first_deposits_sum']) ? $request['first_deposits_sum'] : 1;
        $request['deposits_count'] = isset($request['deposits_count']) ? $request['deposits_count'] : 1;
        $request['deposits_sum'] = isset($request['deposits_sum']) ? $request['deposits_sum'] : 1;
    }

    /**
     * Query data.
     * @return array
     */
    public function query(Request $request): array
    {

        $this->canSee = Auth::user()->inRole('superadmin');
        $partner = Partner::filters()->filtersApply([PartnerStatisticsFilter::class])
            ->leftJoin('brand_partners', 'partners.id', '=', 'brand_partners.partner_id')
            ->leftJoin('brands', 'brand_partners.brand_id', '=', 'brands.id')
            ->leftJoin('campaigns', 'brands.id', '=', 'campaigns.brand_id')
            ->leftJoin('players', 'campaigns.id', '=', 'players.campaign_id')
            ->leftJoin('deposits', 'players.id', '=', 'deposits.player_id')
            ->addSelect('partners.id', DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'))
            ->paginate(10);

        return [
            'partner' => $partner,
            'text' => 'No data.',
            'filter' => [
                'group' => [
                    DateRange::make('range_date')
                        ->title('Range date')
                        ->value($request->get('range_date')),

                    Select::make('brand[]')
                        ->multiple()
                        ->fromModel(Brands::class, 'brand')
                        ->value(array_map(function ($v){
                            return (int)$v;
                        }, $request->get('brand') ?? []))
                        ->title('Brand'),

                    Select::make('partner_id')
                        ->taggable()
                        ->canSee($this->canSee)
                        ->multiple()
                        ->options($request->get('partner_id') ?? [])
                        ->value($request->get('partner_id'))
                        ->title('Partner IDs'),

                    Select::make('partner_email[]')
                        ->multiple()
                        ->fromModel(Partner::class, 'email')
                        ->value(array_map(function ($v){
                            return (int)$v;
                        }, $request->get('partner_email') ?? []))
                        ->title('Partner Emails'),

                    Select::make('tags[]')->disabled(true)
                        ->options([
                            'index' => 'Index',
                            'noindex' => 'No index',
                        ])
                        ->multiple()
                        ->value($request->get('tags'))
                        ->title('Tags'),

                    Input::make('strategies')->disabled(true)
                        ->canSee($this->canSee)
                        ->type('text')
                        ->title('Strategies')
                        ->class('form-control'),

                    Select::make('campaign_id[]')
                        ->canSee($this->canSee)
                        ->fromModel(Campaign::class, 'id')
                        ->multiple()
                        ->value($request->get('campaign_id'))
                        ->title('Campaign IDs'),

                    Input::make('promo_id')->disabled(true)
                        ->canSee($this->canSee)
                        ->type('number')
                        ->title('Promo IDs')
                        ->class('form-control'),

                    Input::make('promo_code')->disabled(true)
                        ->canSee($this->canSee)
                        ->type('number')
                        ->title('Promo Codes')
                        ->class('form-control'),

                    Select::make('player_id[]')
                        ->canSee($this->canSee)
                        ->fromModel(Players::class, 'id')
                        ->multiple()
                        ->value($request->get('player_id'))
                        ->title('Player IDs'),

                    Select::make('player_email[]')
                        ->canSee($this->canSee)
                        ->fromModel(Players::class, 'email')
                        ->multiple()
                        ->value($request->get('player_email'))
                        ->title('Player Emails'),

                    input::make('player_countries')
                        ->canSee($this->canSee)
                        ->type('text')
                        ->value($request->get('player_countries'))
                        ->title('Player Countries'),


                    Input::make('player_dynamic_tag_contain')->disabled(true)
                        ->canSee($this->canSee)
                        ->title('Player dynamic tags contain')
                        ->class('form-control'),

                    Input::make('player_dynamic_tag_exclude')->disabled(true)
                        ->canSee($this->canSee)
                        ->title('Player dynamic tags exclude')
                        ->class('form-control'),

                    DateRange::make('deposit_date_range')
                        ->title('First deposit date range')
                        ->value($request->deposit_date_range),

                    DateRange::make('sign_date_range')
                        ->title('Sign up at date range')
                        ->value($request->sign_date_range),



                    Switcher::make('convert')->placeholder('convert to')->title('Convert all currencies')
                        ->value(0),
                    Input::make('players_to')
                        ->type('number')
                        ->class('form-control')
                        ->value($request->players_to),

                    CheckBox::make('visit_count')->placeholder('Visits count')->title('Columns')
                        ->sendTrueOrFalse()
                        ->value($request->get('visit_count')),

                    CheckBox::make('registration_count')
                        ->sendTrueOrFalse()
                        ->placeholder('Registration count')
                        ->value($request->get('registration_count')),

                    CheckBox::make('qualified_account')
                        ->placeholder('Qualified account')
                        ->value($request->get('qualified_account')),

                    CheckBox::make('depositing_players_count')->placeholder('Depositing players count')
                        ->value($request->get('depositing_players_count')),

                    CheckBox::make('first_deposits_count')
                        ->sendTrueOrFalse()
                        ->placeholder('First deposits count')
                        ->value($request->get('first_deposits_count')),

                    CheckBox::make('first_deposits_sum')
                        ->sendTrueOrFalse()
                        ->placeholder('First deposits sum')
                        ->value($request->get('first_deposits_sum')),

                    CheckBox::make('deposits_count')
                        ->sendTrueOrFalse()
                        ->placeholder('Deposits count')
                        ->value($request->get('deposits_count')),

                    CheckBox::make('deposits_sum')
                        ->sendTrueOrFalse()
                        ->placeholder('Deposits sum')
                        ->value($request->get('deposits_sum')),

                    CheckBox::make('cashouts_count')->placeholder('Cashouts count')
                        ->value($request->get('cashouts_count')),

                    CheckBox::make('cashouts_sum')->placeholder('Cashouts sum')
                        ->value($request->get('cashouts_sum')),

                    CheckBox::make('chargebacks_count')->placeholder('Chargebacks count')
                        ->value($request->get('chargebacks_count')),

                    CheckBox::make('chargebacks_sum')->placeholder('Chargebacks sum')
                        ->value($request->get('chargebacks_sum')),

                    CheckBox::make('payment_system_fees_sum')->placeholder('Payment system fees sum')
                        ->value($request->get('payment_system_fees_sum')),

                    CheckBox::make('casino_wager')->placeholder('Casino Wager')
                        ->value($request->get('casino_wager')),

                    CheckBox::make('bets_sum')->placeholder('Bets sum')
                        ->value($request->get('bets_sum')),

                    CheckBox::make('wins_sum')->placeholder('Wins sum')
                        ->value($request->get('wins_sum')),

                    CheckBox::make('real_ggr')->placeholder('Real GGR')
                        ->canSee($this->canSee)
                        ->value($request->get('real_ggr')),

                    CheckBox::make('ggr')->placeholder('GGR')
                        ->value($request->get('ggr')),

                    CheckBox::make('bonus_issues_sum')->placeholder('Bonus issues sum')
                        ->canSee($this->canSee)
                        ->value($request->get('bonus_issues_sum')),

                    CheckBox::make('additional_deductions_sum')->placeholder('Additional deductions sum')
                        ->canSee($this->canSee)
                        ->value($request->get('additional_deductions_sum')),

                    CheckBox::make('ngr')->placeholder('NGR')
                        ->value($request->get('ngr')),

                    CheckBox::make('partner_income')->placeholder('Partner income')
                        ->value($request->get('partner_income')),

                        Switcher::make('period')->placeholder('period')->title('Group by')
                            ->value(0),
                        Select::make('period_type')
                            ->options([
                                1 => 'day',
                                2 => 'week',
                                3 => 'month',
                                4 => 'year',
                            ])
                            ->value($request->get('period_type')),

                    CheckBox::make('partner_group')->placeholder('Partner')
                        ->sendTrueOrFalse(true)
                        ->title('Group By')
                        ->value($request->get('partner_group')),

//                    CheckBox::make()->placeholder('Brand')
//                        ->value(0),s
//
//                    CheckBox::make()->placeholder('Strategy')
//                        ->value(0),
//
//                    CheckBox::make()->placeholder('Campaign')
//                        ->value(0),
//
//                    CheckBox::make()->placeholder('Promo')
//                        ->value(0),
//
//                    CheckBox::make()->placeholder('Player')
//                        ->value(0),
//
//                    CheckBox::make()->placeholder('Dedicated operator')
//                        ->value(0),
//
//                    CheckBox::make()->placeholder('Player country')
//                        ->value(0),
//
//                    CheckBox::make()->placeholder('Sign up at period')
//                        ->value(0),
//
//                    CheckBox::make()->placeholder('First deposit period')
//                        ->value(0),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->method('clear_filter')
                        ->class('btn btn-default')
                        ->vertical(),

                ],
            ],
            'navTable' => [
                'group' => [
                    Button::make('Export to CVS')
                        ->class('btn btn-info'),
                ],
                'title' => 'Partner Statistics'
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
                    PartnerStatisticsTable::class,
                ]]),];
    }


    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.partner-statistics');
    }

    public function apply_filter(Request $request){
        Alert::info('Filter apply');
        return redirect()->route('platform.finance.partner-statistics', array_filter($request->all(), function ($k, $v){
            return $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

}
