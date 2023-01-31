<?php

namespace App\Orchid\Screens\Bonuses;

use App\Models\Currency;
use App\Models\FeedExports;
use App\Orchid\Filters\IssuedBonusesFilter;
use App\Orchid\Filters\IssuedFreespinFilter;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Carbon\Carbon;
class Freespin extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Freespin';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Freespin';

    public $staff;

    public $export = 'issued_freespin';

    public $permission = [
        'platform.bonuses.freespin'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $this->staff = $request->user();

        $bonus = \App\Models\FreespinIssue::filters()
            ->filtersApply([IssuedFreespinFilter::class])->select()
            ->leftJoin('freespin_bonus', 'freespin_issue.bonus_id', '=', 'freespin_bonus.id')
            ->leftJoin('players', 'freespin_issue.player_id', '=', 'players.id')
            ->leftJoin('currency', 'freespin_issue.currency_id', '=', 'currency.id')
            ->select('freespin_issue.id', 'players.email', 'players.id as player_id', 'freespin_bonus.title',
                'currency.code as currency', 'freespin_issue.stage', 'freespin_issue.win', 'freespin_issue.count as free',
                'freespin_issue.active_until', 'freespin_issue.created_at',  'freespin_issue.expiry_at')
            ->orderBy('freespin_issue.id', 'DESC')
            ->paginate();

        $total = \App\Models\FreespinIssue::query()->select('status')->get()->toArray();
        return [
            'bonuses' => $bonus,
            'filter' => [
                'group' => [
                    Group::make([
                        Select::make('title')
                            ->title('Title')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Starts with',
                                3 => 'Ends with',
                            ])
                            ->value((int)$request->title),

                        Input::make('title_value')
                            ->type('text')
                            ->value($request->title_value),
                    ])->alignEnd()->render(),

                    Group::make([
                        Select::make('user_email')
                            ->title('User Email')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Starts with',
                                3 => 'Ends with',
                            ])
                            ->value((int)$request->user_email),

                        Input::make('user_email_value')
                            ->type('text')
                            ->value($request->user_email_value),
                    ])->alignEnd()->render(),

                    Input::make('user_id_eq')
                        ->type('number')
                        ->title('User ID EQ')
                        ->value($request->user_id_eq),

                    Select::make('currency')
                        ->empty('No select', 0)
                        ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'code')
                        ->title('Account Currency')
                        ->value((int)$request->currency),

                    Select::make('stage')
                        ->empty('No select', 0)
                        ->title('Stage')
                        ->options([
                            1 => 'Issued',
                            2 => 'Activated',
                            3 => 'Canceled',
                            4 => 'Expired',
                            5 => 'Played'
                        ])
                        ->value((int)$request->stage),

                    Select::make('status')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'active',
                            2 => 'issued',
                            3 => 'activated',
                        ])
                        ->title('Status')
                        ->value((int)$request->status),

                    DateRange::make('date_received')
                        ->title('Date Received')
                        ->value($request->date_received),

                    DateRange::make('activate_until')
                        ->title('Activate Until')
                        ->value($request->date_received),

                    DateRange::make('expiry_date')
                        ->title('Expiry Date')
                        ->value($request->expiry_date),

                ],
                'action' => [
                    Button::make('Filter')
                        ->vertical()
                        ->icon('filter')
                        ->class('btn btn-primary btn-sm btn-block')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->icon('refresh')
                        ->method('clear_filter')
                        ->class('btn btn-sm btn-dark')
                        ->vertical(),
                ]
            ],
            'scope' => [
                'title' => 'Scope Counters',
                'thead' => [
                    'Scope Name' => 'Count'
                ],
                'table' => [
                    'All' => count($total),
                    'Active' => count(array_filter($total, function ($v, $k){
                        return $v['status'] === 1;
                    },1)),
                    'Issued' => count(array_filter($total, function ($v, $k){
                        return $v['status'] === 2;
                    },1)),
                    'Activated' => count(array_filter($total, function ($v, $k){
                        return $v['status'] === 3;
                    },1)),
                ]
            ]
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make('Export')
                ->rawClick()
                ->method('add_export')
                ->parameters(
                    [
                        'export' => [
                            'type_name' => $this->export,
                            'staff_id' => $this->staff->id,
                            'status' => 1,
                        ]
                    ]
                )
                ->icon('share-alt')
                ->class('btn btn-outline-secondary'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::wrapper('orchid.wrapper-col2', [
                'col_left' => [
                    Layout::view('orchid.filter'),
                    Layout::table('bonuses', [
                        TD::make('id', 'ID')->render(function (\App\Models\FreespinIssue $model){
                            return Link::make($model->id)->route('platform.bonuses.freespin.view', $model->id)
                                ->class('link-primary');
                        })->sort(),
                        TD::make('email', 'User')->render(function (\App\Models\FreespinIssue $model){
                            return  Link::make($model->email)->class('link-primary')
                                ->route('platform.players.profile', $model->player_id);
                        })->sort(),
                        TD::make('title', 'Title')->sort(),
                        TD::make('created_at', 'Date Received')->render(function (\App\Models\FreespinIssue $model){
                            return $model->created_at;
                        })->sort(),
                        TD::make('free', 'Free spins awarded')->sort(),
                        TD::make('currency', 'Currency')->sort(),
                        TD::make('expiry_at', 'Expiry date')->sort(),
                        TD::make()->render(function (\App\Models\FreespinIssue $model) {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Link::make('View')
                                        ->route('platform.bonuses.freespin.view', $model->id)
                                        ->class('dropdown-item')
                                        ->icon('user'),
                                ])->class('btn sharp btn-primary tp-btn');
                        })->align(TD::ALIGN_RIGHT)->sort(),
                    ])
                ],
                'col_right' => [
                    Layout::view('orchid.players.scope-countres'),
                ]
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.bonuses.freespin');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.bonuses.freespin', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function add_export(Request $request)
    {
        $fileName = $this->export .'-'. Carbon::now()->format('Y-m-d') . '.csv';

        $tasks =  \App\Models\FreespinIssue::filters()
            ->filtersApply([IssuedFreespinFilter::class])->select()
            ->leftJoin('freespin_bonus', 'freespin_issue.bonus_id', '=', 'freespin_bonus.id')
            ->leftJoin('players', 'freespin_issue.player_id', '=', 'players.id')
            ->leftJoin('currency', 'freespin_issue.currency_id', '=', 'currency.id')
            ->select('freespin_issue.id', 'players.email', 'players.id as player_id', 'freespin_bonus.title',
                'currency.code as currency', 'freespin_issue.stage', 'freespin_issue.win', 'freespin_issue.count as free',
                'freespin_issue.active_until', 'freespin_issue.created_at',  'freespin_issue.expiry_at')
            ->orderBy('freespin_issue.id', 'DESC')
            ->get();

        $header = $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName"
        ];

        $columns = array('ID', 'Email', 'Title', 'Date Received', 'Free spins awarded', 'Currency', 'Expiry date');



        $callback = function () use ($tasks, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);


            foreach ($tasks as $task) {
                fputcsv($stream, [
                    'ID' => $task->id,
                    'Email' => $task->email,
                    'Title' => $task->title,
                    'Date Received' => $task->created_at,
                    'Free spins awarded' => $task->free,
                    'Currency' =>  $task->currency ,
                    'Expiry date' => $task->expiry_at,
                ]);

            }
            fclose($stream);
        };

        $data = array_merge($request->export, ['url' => $fileName]);
        FeedExports::query()->insert($data);
        Toast::info(__('Success'));
        return response()->stream($callback, 200, $headers);
    }
}
