<?php

namespace App\Orchid\Screens\Bonuses;

use App\Models\BonusIssue;
use App\Models\FreespinIssue;
use App\Models\Players;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class IssuesHistoryView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'IssuesHistoryView';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'IssuesHistoryView';

    public $permission = [
        'platform.bonuses.history.view'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\BonusIssueHistory $model): array
    {
        $fs = FreespinIssue::query()->leftJoin('bonus_issue_history_binds', function($join){
            $join->on('freespin_issue.id', '=', 'bonus_issue_history_binds.bonus_id');
            $join->where('bonus_issue_history_binds.type_id', 2);
        })->join('bonuses', 'bonus_issue_history_binds.bonus_id', '=', 'bonuses.id')
            ->get();

        $player = Players::query()->where('id', $model->player_id)->select('id', 'email')->first();

        $bonus = BonusIssue::query()->Join('bonus_issue_history_binds', function($join){
            $join->on('bonus_issue.id', '=', 'bonus_issue_history_binds.bonus_id');
            $join->where('bonus_issue_history_binds.type_id', 1);
        })->join('bonuses', 'bonus_issue_history_binds.bonus_id', '=', 'bonuses.id')
            ->get();

        $strategy = [
            1  => 'Personal',
            2 => 'Prize',
            3 => 'Deposit',
            4 => 'Scheduler',
            5 => 'Prize Award',
            6 => 'Manual',
            7 => 'Registration'
        ];
        $status = [
            1 => 'not activated',
            2 => 'active',
            3 => 'wager done',
            4 => 'lost',
        ];

        return [
            'info' => [
                'title' => 'Bonus Issue Details',
                'table' => [
                    'ID' => $model->id ?? '-',
                    'User' => Link::make($player->email)->class('link-primary')
                        ->route('platform.players.profile', $player->id),
                    'Strategies' => $strategy[$model->strategy_id] ?? '-',
                    'Group Key' => $model->group_key ?? '-',
                    'Status' => '-',
                    'Updated at' => $model->updated_at ?? '-',
                    'Created at' => $model->created_at ?? '-',
                    'Prize' => '-',
                ]
            ],
            'fs' => $fs,
            'bonus' => $bonus
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
            Layout::wrapper('orchid.history-info', [
                'table_bonus' => Layout::table('bonus', [
                    TD::make('id', 'ID')->sort(),
                    TD::make('amount', 'Amount')->sort(),
                    TD::make('strategy', 'Strategy')->render(function (\App\Models\BonusIssue $model){
                        switch ($model->strategy){
                            case 1:
                                return 'Personal';
                            case 2:
                                return 'Prize';
                            case 3:
                                return 'Deposit';
                            case 4:
                                return 'Scheduler';
                            case 5:
                                return 'Prize Award';
                            case 6:
                                return 'Manual';
                            case 7:
                                return 'Registration';
                            default:
                                return '-';
                        }
                    })->sort(),
                    TD::make('stage', 'Stage')->render(function (\App\Models\BonusIssue $model){
                        switch ($model->stage){
                            case 1:
                                return 'Not activated';
                            case 2:
                                return 'Active';
                            case 3:
                                return 'Wager done';
                            case 4:
                                return 'Lost';
                            default:
                                return '-';
                        }
                    })->sort(),
                ]),
                'table_freespin' => Layout::table('fs', [
                    TD::make('id', 'ID')->sort(),
                ]),
            ]),
        ];
    }
}
