<?php

namespace App\Orchid\Screens\Bonuses;

use App\Models\BonusIssue;
use App\Models\Comments;
use App\Models\Currency;
use App\Models\Players;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class FreespinView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'FreespinView';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'FreespinView';


    public $section_id = 5;

    public $permission = [
        'platform.bonuses.freespin.view'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\FreespinIssue $model, Request $request): array
    {
        $bonus = \App\Models\Bonuses::query()->where('id', $model->bonus_id)->first();
        $currency = Currency::query()->where('id', $model->currency_id)->first()->code ?? '-';
        $player = Players::query()->where('id', $model->player_id)->first();
        $user = $request->user();
        $comments = Comments::query()
            ->where('user_id', $model->id)
            ->where('section_id', $this->section_id)
            ->select('comments.id', 'users.name', 'users.email', 'comments.comment', 'comments.created_at')
            ->leftJoin('users', 'comments.staff_id', '=', 'users.id')
            ->orderBy('id', 'DESC')
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
        $stage = [
            1 => 'not activated',
            2 => 'active',
            3 => 'wager done',
            4 => 'lost',
        ];

        return [
            'info' => [
                'title' => 'Freespin Issue Details',
                'table' => [
                    'Title' => $model->title ?? '-',
                    'Bonus' => '-',
                    'Account' => Link::make($player->email)->class('link-primary')
                        ->route('platform.players.profile', $player->id),
                    'Currency' => $currency ?? '-',
                    'Stage' => $stage[$bonus->stage] ?? '-',
                    'Bet Level' => '-',
                    'Free Spins Awarded' => $model->count ?? '-',
                    'Win Amount' => "$model->win $currency" ?? '-',
                    'External ID' => '-',
                    'Provider' => '-',
                    'Strategy' => $strategy[$bonus->strategy_id] ?? '-',
                    'Games' => '-',
                    'Result Game Category' => '-',
                    'Activate Until' => $model->active_until,
                    'Issued At' => $model->created_at ?? '-',
                    'Updated At' => $model->updated_at ?? '-',
                ]
            ], 'comments' => [
                'section' => [
                    'title' => 'Comments(' . count($comments) . ')',
                    'textarea' => TextArea::make('comment')
                        ->rows(5)
                        ->title('Add comment'),
                    'submit' => Button::make('Add')
                        ->icon('check')
                        ->method('add_comment')
                        ->class('btn  btn-default')
                        ->parameters([
                            'staff_id' => $user->id,
                            'item_id' => $model->id
                        ])
                ],
                'content' => $comments
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
            Link::make('Return')
                ->icon('left')
                ->class('btn btn-outline-secondary mb-2')
                ->route('platform.bonuses.issued'),
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
            Layout::view('orchid.info'),
            Layout::view('orchid.players.comments')
        ];
    }
}
