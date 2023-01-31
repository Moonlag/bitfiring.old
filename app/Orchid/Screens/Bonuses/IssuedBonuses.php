<?php

namespace App\Orchid\Screens\Bonuses;

use App\Models\Currency;
use App\Models\FeedExports;
use App\Models\User;
use App\Orchid\Filters\IssuedBonusesFilter;
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

class IssuedBonuses extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Issued Bonuses';
    public $staff;

    public $export = 'issued_bonuses';

//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'IssuedBonuses';

    public $permission = [
        'platform.bonuses.issued'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $this->staff = $request->user();

        $bonus = \App\Models\BonusIssue::filters()
            ->filtersApply([IssuedBonusesFilter::class])->select()
            ->leftJoin('bonuses','bonus_issue.bonus_id', '=', 'bonuses.id')
            ->leftJoin('players', 'bonus_issue.user_id', '=', 'players.id')
            ->leftJoin('currency', 'bonus_issue.currency_id', '=', 'currency.id')
            ->select('bonus_issue.id', 'players.email', 'players.id as player_id', 'bonuses.title', 'bonuses.strategy_id as strategy',  'bonus_issue.amount',
                'currency.code as currency', 'bonus_issue.to_wager', 'bonus_issue.custom_title', 'bonus_issue.wagered', 'bonus_issue.active_until', 'bonus_issue.stage', 'bonus_issue.created_at')
            ->orderBy('bonus_issue.id', 'DESC')
            ->paginate();

        $total = \App\Models\BonusIssue::query()->select('status')->get()->toArray();
        return [
            'bonuses' => $bonus,
            'filter' => [
                'title' => 'Filter',
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
                            1 => 'not activated',
                            2 => 'active',
                            3 => 'wager done',
                            4 => 'lost',
                        ])
                        ->value((int)$request->stage),

                    Select::make('status')
                        ->empty('No select', 0)
                        ->options([
                            1 => 'active',
                            2 => 'no active',
                        ])
                        ->title('Status')
                        ->value((int)$request->status),

                    Group::make([
                        Select::make('amount_cents')
                            ->title('Amount cents')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Equals',
                                2 => 'Greater than',
                                3 => 'Less than',
                            ])
                            ->value((int)$request->amount_cents),

                        Input::make('amount_cents_value')
                            ->type('text')
                            ->value($request->amount_cents_value),
                    ])->alignEnd()->render(),

                    Group::make([
                        Select::make('amount_wager_cents')
                            ->title('Amount Wager cents')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Equals',
                                2 => 'Greater than',
                                3 => 'Less than',
                            ])
                            ->value((int)$request->get('amount_wager_cents')),

                        Input::make('amount_wager_cents_value')
                            ->type('text')
                            ->value($request->amount_wager_cents_value),
                    ])->alignEnd()->render(),

                    DateRange::make('date_received')
                        ->title('Date Received')
                        ->value($request->date_received),

                    DateRange::make('active_until')
                        ->title('Active until')
                        ->value($request->active_until),

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
                    'No Active' => count(array_filter($total, function ($v, $k){
                        return $v['status'] === 2;
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
                        TD::make('id', 'ID')->render(function (\App\Models\BonusIssue $model){
                            return Link::make($model->id)->route('platform.bonuses.issued.view', $model->id)
                                ->class('link-primary');
                        })->sort(),
                        TD::make('email', 'User')->render(function (\App\Models\BonusIssue $model){
                            return  Link::make($model->email)->class('link-primary')
                                ->route('platform.players.profile', $model->player_id);
                        })->sort(),
                        TD::make('title', 'Title')->render(function (\App\Models\BonusIssue $model){
                            return $model->title ?? $model->custom_title;
                        })->sort(),
                        TD::make('created_at', 'Date Received')->render(function (\App\Models\BonusIssue $model){
                            return $model->created_at;
                        })->sort(),
                        TD::make('currency', 'Currency')->sort(),
                        TD::make('active_until', 'Active until')->sort(),
                        TD::make('wager', 'Wager')->render(function (\App\Models\BonusIssue $model){
                            $percent = 100 * ($model->wagered  / $model->to_wager);
                            return $model->wagered . ' / ' . $model->to_wager .' ('. bcdiv($percent, 1, 0). '%)';
                        })->sort(),
                        TD::make()->render(function (\App\Models\BonusIssue $model) {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Link::make('View')
                                        ->route('platform.bonuses.issued.view', $model->id)
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
        return redirect()->route('platform.bonuses.issued');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.bonuses.issued', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function add_export(Request $request)
    {
        $fileName = 'bonuses_issued-' . Carbon::now()->format('Y-m-d') . '.csv';

        $tasks = \App\Models\BonusIssue::filters()
            ->filtersApply([IssuedBonusesFilter::class])->select()
            ->leftJoin('bonuses','bonus_issue.bonus_id', '=', 'bonuses.id')
            ->leftJoin('players', 'bonus_issue.user_id', '=', 'players.id')
            ->leftJoin('currency', 'bonus_issue.currency_id', '=', 'currency.id')
            ->select('bonus_issue.id', 'players.email', 'players.id as player_id', 'bonuses.title', 'bonuses.strategy_id as strategy',  'bonus_issue.amount',
                'currency.code as currency', 'bonus_issue.to_wager', 'bonus_issue.custom_title', 'bonus_issue.wagered', 'bonus_issue.active_until', 'bonus_issue.stage', 'bonus_issue.created_at')
            ->orderBy('bonus_issue.id', 'DESC')
            ->get();

        $header = $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName"
        ];

        $columns = array('ID', 'Email', 'Title', 'Date Received', 'Active until', 'Wager');



        $callback = function () use ($tasks, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);


            foreach ($tasks as $task) {

                $percent = 100 * ($task->wagered  / $task->to_wager);

                fputcsv($stream, [
                    'ID' => $task->id,
                    'Email' => $task->email,
                    'Title' => $task->title,
                    'Date Received' => $task->created_at,
                    'Active until' => $task->active_until,
                    'Wager' =>  $task->wagered . ' / ' . $task->to_wager .' ('. bcdiv($percent, 1, 0). '%)',
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
