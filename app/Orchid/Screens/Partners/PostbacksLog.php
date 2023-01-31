<?php

namespace App\Orchid\Screens\Partners;

use App\Models\Campaign;
use App\Models\PostbackAction;
use App\Models\Postbacks;
use App\Orchid\Filters\CampaignFilter;
use App\Orchid\Filters\PostbacksLogFilter;
use App\Traits\DbPostbackLogTestingTrait;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;

class PostbacksLog extends Screen
{
    use DbPostbackLogTestingTrait;
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Postbacks Log';

    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'Pastbacks Log';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.partners.postbacks-log'
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
     * @return array
     */
    public function query(Request $request): array
    {

        $postbacks = Postbacks::filters()->filtersApply([PostbacksLogFilter::class,])
            ->Join('postbacks_log', 'postbacks.id', '=', 'postbacks_log.postback_id')
            ->leftJoin('partners', 'postbacks.partner_id', '=', 'partners.id')
            ->leftJoin('campaigns', 'postbacks.campaign_id', '=', 'campaigns.id')
            ->leftJoin('postback_action', 'postbacks.postback_action_id', '=', 'postback_action.id')
            ->leftJoin('players', 'partners.id', '=', 'players.partner_id')
            ->select(DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'),
                'campaigns.campaign', 'postbacks_log.url as postback_url', 'postbacks_log.created_at as sent_at', 'postbacks.id as postback_id',
                'postbacks.code as response_code', 'postback_action.action')
            ->groupBy('postbacks_log.id')
            ->paginate();

        return [
            'postbacks' => $postbacks,
            'filter' => [
                'group' => [
                    Select::make('postback_actions')
                        ->empty('No select', '0')
                        ->fromModel(PostbackAction::class, 'action')
                        ->value((int)$request->postback_actions)
                        ->title('Postback actions'),

                    Input::make('response_code')
                        ->type('text')
                        ->value($request->response_code)
                        ->title('Response code'),

                    Input::make('postback_url')
                        ->type('text')
                        ->value($request->postback_url)
                        ->title('Postback URL'),

                    Input::make('player_id')
                        ->type('text')
                        ->value($request->player_id)
                        ->title('Player IDs'),

                    Input::make('promo_id')
                        ->type('text')
                        ->value($request->promo_id)
                        ->title('Promo IDs'),

                    Input::make('promo_codes')
                        ->type('text')
                        ->value($request->promo_codes)
                        ->title('Promo codes'),

                    Input::make('campaign_id')
                        ->type('text')
                        ->value($request->campaign_id)
                        ->title('Campaigns IDs'),

                    Input::make('campaign_name')
                        ->type('text')
                        ->value($request->campaign_name)
                        ->title('Campaigns name'),

                    Input::make('partner_id')
                        ->type('text')
                        ->value($request->partner_id)
                        ->title('Partner IDs'),

                    Input::make('partner_name')
                        ->type('text')
                        ->value($request->partner_name)
                        ->title('Partner Name'),

                    Input::make('partner_email')
                        ->type('text')
                        ->value($request->partner_email)
                        ->title('Partner Email'),

                    DateRange::make('sent_at')
                        ->value($request->sent_at)
                        ->title('Sent at'),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->method('clear_filter')
                        ->class('btn btn-default')
                        ->vertical()
                ],
                'title' => 'Filter'
            ],
            'navTable' => [
                'group' => [
                    Button::make('Export to CVS')
                        ->method('export_PostbacksLog')
                        ->rawClick()
                        ->class('btn btn-outline-info'),
                ],
                'title' => 'Postbacks Log'
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
                    Layout::table('postbacks', [
                        TD::make('postback_id', 'Postback ID'),
                        TD::make('sent_at', 'Sent at')->render(function (Postbacks $model){
                            return $model->sent_at;
                        }),
                        TD::make('partner', 'Partner'),
                        TD::make('postback_url', 'Postback url'),
                        TD::make('response_code', 'Response code'),
                        TD::make('campaign', 'Campaign'),
                        TD::make('action', 'Postback action'),
                    ]),
                ]]),
        ];
    }

    public function export_PostbacksLog(Request $request){
        $postbacks = Postbacks::filters()
            ->filtersApply([PostbacksLogFilter::class,])
            ->Join('postbacks_log', 'postbacks.id', '=', 'postbacks_log.postback_id')
            ->leftJoin('partners', 'postbacks.partner_id', '=', 'partners.id')
            ->leftJoin('campaigns', 'postbacks.campaign_id', '=', 'campaigns.id')
            ->leftJoin('postback_action', 'postbacks.postback_action_id', '=', 'postback_action.id')
            ->leftJoin('players', 'partners.id', '=', 'players.partner_id')
            ->select(DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'),
                'campaigns.campaign', 'postbacks_log.url as postback_url', 'postbacks_log.created_at as sent_at', 'postbacks.id as postback_id',
                'postbacks.code as response_code', 'postback_action.action')
            ->groupBy('postbacks_log.id')
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=postbacks-'. Carbon::now()->format('Y-m-d') .'.csv'
        ];

        $columns = ['Postback ID', 'Sent at', 'Partner', 'Postback url', 'Response code', 'Campaign', 'Postback action'];

        $callback = function () use ($postbacks, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);

            foreach ($postbacks as $postback) {
                fputcsv($stream, [
                    'ID' => $postback->postback_id,
                    'Sent at'   => $postback->sent_at,
                    'Partner'   => $postback->company,
                    'Postback url' => $postback->postback_url,
                    'Response code' => $postback->response_code,
                    'Campaign'   => $postback->campaign,
                    'Postback action'  => $postback->action,
                ]);
            }
            fclose($stream);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.partners.postbacks-log');
    }

    public function apply_filter(Request $request)
    {
        Toast::success('Filter apply');
        return redirect()->route('platform.partners.postbacks-log', array_filter($request->all(), function ($k, $v) {
            return $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
