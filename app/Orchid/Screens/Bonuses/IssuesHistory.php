<?php

namespace App\Orchid\Screens\Bonuses;

use App\Models\Currency;
use App\Orchid\Filters\IssuedBonusHistory;
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

class IssuesHistory extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'IssuesHistory';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'IssuesHistory';

    public $permission = [
        'platform.bonuses.history'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $bonus = \App\Models\BonusIssueHistory::filters()
            ->filtersApply([IssuedBonusHistory::class])
            ->leftJoin('bonus_issue_history_binds', 'bonus_issue_history.id', '=', 'bonus_issue_history_binds.bonus_id')
            ->leftJoin('players', 'bonus_issue_history.player_id', '=', 'players.id')
            ->select('bonus_issue_history.id', 'players.email', 'players.id as player_id', 'bonus_issue_history.group_key', 'bonus_issue_history.created_at', 'bonus_issue_history.strategy_id as strategies')
            ->paginate();

        return [
            'bonuses' => $bonus,
            'filter' => [
                'group' => [
                    Group::make([
                        Select::make('group_key')
                            ->title('Group Key')
                            ->empty('No select', '0')
                            ->options([
                                1 => 'Contains',
                                2 => 'Starts with',
                                3 => 'Ends with',
                            ])
                            ->value((int)$request->group_key),

                        Input::make('group_key_value')
                            ->type('text')
                            ->value($request->group_key_value),
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

                    Select::make('strategy')
                        ->empty('No select', 0)
                        ->title('Strategy')
                        ->options([
                            1 => 'personal',
                            2 => 'prize',
                            3 => 'deposit',
                            4 => 'scheduler',
                            5 => 'prize award',
                            6 => 'manual',
                            7 => 'registration',
                        ])
                        ->value((int)$request->strategy),

                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

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

        ];
        return [];
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
            Layout::wrapper('orchid.wrapper-col2', [
                'col_left' => [
                    Layout::view('orchid.filter'),
                    Layout::table('bonuses', [
                        TD::make('id', 'ID')->render(function (\App\Models\BonusIssueHistory $model) {
                            return Link::make($model->id)->class('link-primary')
                                ->route('platform.bonuses.history.view', $model->id);
                        })->sort(),
                        TD::make('email', 'User')->render(function (\App\Models\BonusIssueHistory $model) {
                            return Link::make($model->email)->class('link-primary')
                                ->route('platform.players.profile', $model->player_id);
                        })->sort(),
                        TD::make('group_key', 'Group key')->sort(),
                        TD::make('strategies', 'Strategies')->render(function (\App\Models\BonusIssueHistory $model) {
                            switch ($model->strategies) {
                                case 1:
                                    return 'personal';
                                case 2:
                                    return 'prize';
                                case 3:
                                    return 'deposit';
                                case 4:
                                    return 'scheduler';
                                case 5:
                                    return 'prize award';
                                case 6:
                                    return 'manual';
                                case 7:
                                    return 'registration';
                                default:
                                    return '-';
                            }
                        })->sort(),
                        TD::make('created_at', 'Created_at')->render(function (\App\Models\BonusIssueHistory $model) {
                            return $model->created_at;
                        })->sort(),
                    ])
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.bonuses.history');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.bonuses.history', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
