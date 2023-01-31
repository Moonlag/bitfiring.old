<?php

namespace App\Orchid\Screens\Partners;

use Carbon\Carbon;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Facades\Layout;
use App\Models\Currency;
use App\Orchid\Filters\ListOfPartnersFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\TD;
use App\Models\Partner;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;


class ListOfPartners extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Partners';
    public $exist = false;
    public $canSee = false;
    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'List of partners';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.partners.list-of-partners'
    ];

    public $request;
    /**
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Query data.
     *
     * @param Request $request
     * @return array
     */
    public function query(Request $request): array
    {

        $this->canSee = Auth::user()->inRole('superadmin');

        $partner = Partner::filters()
            ->filtersApply([ListOfPartnersFilter::class,])
            ->defaultSort('id')
            ->leftJoin('brand_partners', 'partners.id', '=', 'brand_partners.partner_id')
            ->leftJoin('brands', 'brand_partners.brand_id', '=', 'brands.id')
            ->leftJoin('campaigns', 'brands.id', '=', 'campaigns.brand_id')
            ->leftJoin('players', 'partners.id', '=', 'players.partner_id')
            ->select(DB::raw('(SELECT COUNT(id) FROM players WHERE players.partner_id = partners.id) as player'), 'partners.id', 'partners.created_at',
                DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as company'),
                'partners.last_login', 'partners.email',
                'partners.status_state')
            ->groupBy('partners.id')->orderBy('id','DESC')->paginate();

        return [
            'table' => $partner,
            'filter' => [
                'group' => [
                    Input::make('partner_id')
                        ->type('text')
                        ->title('Partner ID')
                        ->value($request->partner_id)
                        ->placeholder('Partner ID'),

                    Input::make('email')
                        ->type('email')
                        ->title('Partner Email')
                        ->value($request->email)
                        ->placeholder('Partner Email'),

                    Input::make('company')
                        ->type('text')
                        ->title('Company name')
                        ->value($request->company)
                        ->placeholder('Company name'),

                    Select::make('traffic[]')
                        ->canSee($this->canSee)
                        ->options([
                            'index' => 'Index',
                            'noindex' => 'No index',
                        ])
                        ->multiple()
                        ->value($request->get('traffic'))
                        ->title('Traffic sources'),

                    Select::make('tags[]')
                        ->canSee($this->canSee)
                        ->options([
                            'index' => 'Index',
                            'noindex' => 'No index',
                        ])
                        ->multiple()
                        ->value($request->get('tags'))
                        ->title('Tags'),

                    Select::make('balance')
                        ->empty('No select', 0)
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'name', 'id')
                        ->title('Balance currency'),

                    Group::make([
                        Input::make('players_from')
                            ->formaction('apply_filter')
                            ->title('Balance')
                            ->type('number')
                            ->class('form-control')
                            ->value($request->players_from)
                            ->id('players_from'),
                        Input::make('players_to')
                            ->type('number')
                            ->class('form-control mt-auto')
                            ->value($request->players_to),
                    ])->alignEnd()->render(),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->method('clear_filter')
                        ->class('btn btn-default')
                        ->vertical(),
                ],
                'title' => 'Filter'
            ],
            'navTable' => [
                'group' => [
                    Select::make('partner.balance')
                        ->options([
                            'add_tag' => 'Add tags',
                            'remove_tag' => 'Remove tags',
                            'verify' => 'Verify',
                            'block' => 'Block',
                            'email' => 'Email',
                        ])->empty('Batch actions', 0)
                        ->disabled(false),
                    Button::make('Export to CVS')
                        ->method('export_partner')
                        ->rawClick()
                        ->class('btn btn-info'),
                ],
                'title' => 'List of partners'
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
        return [
            Button::make('All')
                ->class(!$this->request->status_state ? 'btn btn-info active' : 'btn')
                ->parameters([
                    'status_state' => 0
                ])
                ->method('apply_filter'),

            Button::make('Verified')
                ->class($this->request->status_state === '1' ? 'btn btn-outline-info active' : 'btn')
                ->parameters([
                    'status_state' => 1
                ])
                ->method('apply_filter'),

            Button::make('Not verified')
                ->class($this->request->status_state === '2' ? 'btn btn-outline-info active' : 'btn')
                ->parameters([
                    'status_state' => 2
                ])
                ->method('apply_filter'),

            Button::make('Blocked')
                ->class($this->request->status_state === '3' ? 'btn btn-outline-info active' : 'btn')
                ->method('apply_filter')
                ->parameters([
                    'status_state' => 3
                ]),

            Link::make('Create new')
                ->route('platform.partners.new')
                ->icon('plus')
        ];
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
                        TD::make('check-box', '')
                            ->render(function (Partner $partner) {
                                return CheckBox::make($partner->id)
                                    ->value(0);
                            }),
                        TD::make('id', 'ID')
                            ->render(function (Partner $model) {
                                return Link::make($model->id)->class('btn btn-link text-primary')
                                    ->route('platform.partners.view', $model->id);
                            })
                            ->sort(),
                        TD::make('created_at', 'Created')->sort()
                            ->render(function (Partner $partner) {
                                return $partner->created_at;
                            }),
                        TD::make('company', 'Company name')
                            ->sort(),
                        TD::make('last_login', 'Last seen at')->sort(),
                        TD::make('player', 'Registered players')->sort()->align(TD::ALIGN_CENTER),
                        TD::make('email', 'Dedicated operator')->sort()->canSee($this->canSee),
                        TD::make('state', 'State')
                            ->render(function (Partner $partner) {

                                switch ($partner->status_state) {
                                    case '1':
                                        $title = 'Verified';
                                        break;
                                    case '2':
                                        $title = 'Not verified';
                                        break;
                                    case '3':
                                        $title = 'Blocked';
                                        break;
                                    default:
                                        $title = 'Not verified';
                                }
                                return DropDown::make($title)
                                    ->icon('arrow-down')->class('btn btn-link')
                                    ->list([
                                        Button::make(__('Verified'))->class($title === 'Verified' ? 'btn btn-link disabled' : 'btn btn-link')
                                            ->method('status_state_change')
                                            ->confirm(__('Are you sure you want to change status state?'))
                                            ->parameters([
                                                'id' => $partner->id,
                                                'partner_status_state' => 1
                                            ]),
                                        Button::make(__('Not verified'))->class($title === 'Not verified' ? 'btn btn-link disabled' : 'btn btn-link')
                                            ->method('status_state_change')
                                            ->confirm(__('Are you sure you want to change status state?'))
                                            ->parameters([
                                                'id' => $partner->id,
                                                'partner_status_state' => 2
                                            ]),
                                        Button::make(__('Blocked'))->class($title === 'Blocked' ? 'btn btn-link disabled' : 'btn btn-link')
                                            ->method('status_state_change')
                                            ->confirm(__('Are you sure you want to change status state?'))
                                            ->parameters([
                                                'id' => $partner->id,
                                                'partner_status_state' => 3
                                            ]),
                                    ]);
                            })->align(TD::ALIGN_LEFT),
                        TD::make('action')->colspan(2)->align(TD::ALIGN_RIGHT)
                            ->render(function (Partner $modal) {
                                return Link::make('Report')
                                    ->icon('dislike');
                            }),
                        TD::make()->colspan(2)->align(TD::ALIGN_LEFT)
                            ->render(function (Partner $model) {
                                return Link::make('Edit')
                                    ->icon('pencil')
                                    ->route('platform.partners.view', $model->id);
                            }),
                    ]),
                ]]),
        ];
    }

    public function export_partner(Request $request){
        $partners = Partner::filters()
            ->filtersApply([ListOfPartnersFilter::class,])
            ->defaultSort('id')
            ->leftJoin('brand_partners', 'partners.id', '=', 'brand_partners.partner_id')
            ->leftJoin('brands', 'brand_partners.brand_id', '=', 'brands.id')
            ->leftJoin('campaigns', 'brands.id', '=', 'campaigns.brand_id')
            ->leftJoin('players', 'campaigns.id', '=', 'players.campaign_id')
            ->select(DB::raw('COUNT(players.id) as player'), 'partners.id', 'partners.created_at',
                DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as company'),
                'partners.last_login', 'partners.email',
                'partners.status_state')
            ->groupBy('partners.id')->orderBy('id','DESC')->get();

        $header = $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=partner-'. Carbon::now()->format('Y-m-d') .'.csv'
        ];

        $columns = ['id', 'created_at', 'company', 'last_login', 'player', 'id', 'email', 'state'];

        $callback = function () use ($partners, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);

            foreach ($partners as $partner) {
                fputcsv($stream, [
                    'ID' => $partner->id,
                    'Created at'   => $partner->created_at,
                    'Company'   => $partner->company,
                    'last_login' => $partner->last_login,
                    'Player' => $partner->player,
                    'Email'   => $partner->email,
                    'State'  => Partner::STATUS[$partner->status_state],
                ]);
            }
            fclose($stream);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function status_state_change(Request $request)
    {
        $input = $request->all();
        Partner::where('id', $input['id'])->update(['status_state' => $input['partner_status_state']]);
        Alert::info('You have successfully change status state.');
        return redirect()->route('platform.partners.list-of-partners');
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.partners.list-of-partners');
    }

    public function apply_filter(Request $request)
    {
        Alert::success('Filter apply');
        return redirect()->route('platform.partners.list-of-partners', array_filter($request->all(), function ($k, $v) {
            return $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

}
