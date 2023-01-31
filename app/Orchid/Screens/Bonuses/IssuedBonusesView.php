<?php

namespace App\Orchid\Screens\Bonuses;

use App\Models\BonusIssue;
use App\Models\Comments;
use App\Models\Currency;
use App\Models\Players;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class IssuedBonusesView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Issued Bonuses View';

//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'IssuedBonusesView';

    public $section_id = 4;

    public $permission = [
        'platform.bonuses.issued.view'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Bonuses $model, Request $request): array
    {
        $bonus = BonusIssue::query()->where('bonus_id', $model->id)->first();
        $currency = Currency::query()->where('id', $model->currency_id)->first()->code ?? '-';
        $player = Players::query()->where('id', $bonus->user_id)->first();
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
                'title' => 'Bonus Issue Details',
                'table' => [
                    'Title' => $model->title ?? '-',
                    'Account' => Link::make($player->email)->class('link-primary')
                        ->route('platform.players.profile', $player->id),
                    'Payment' => Link::make($strategy[$model->strategy_id])->class('link-primary')
                            ->route('platform.payments.view', $bonus->reference_id) ?? '-',
                    'Strategy' => $strategy[$model->strategy_id] ?? '-',
                    'Game Category' => '-',
                    'Stage' => $stage[$bonus->stage] ?? '-',
                    'Amount' => "$bonus->amount $currency" ?? '-',
                    'Amount Locked' => "0 $currency" ?? '-',
                    'Amount Wager Requirement' => "$bonus->to_wager $currency" ?? '-',
                    'Wagered Percent' => bcdiv(100 / ($bonus->to_wager / $bonus->wagered), 1, 0) ?? '-',
                    'Issue History' => '-',
                    'Expiry Date' => $bonus->expiry_at,
                    'Issued At' => $bonus->created_at ?? '-',
                    'Updated At' => $bonus->updated_at ?? '-',
                ]
            ],
            'comments' => [
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

    public function add_comment(Request $request)
    {
        try {
            $this->validate($request, [
                'staff_id' => ['required', 'integer'],
                'item_id' => ['required', 'integer'],
                'comment' => ['required', 'string'],
            ]);
            $args = [
                'staff_id' => $request->staff_id,
                'user_id' => $request->item_id,
                'created_at' => Carbon::now()->format('Y-m-d H:m:s'),
                'section_id' => $this->section_id,
                'comment' => $request->comment
            ];
            Comments::query()->insert($args);
            Alert::info('You have successfully.');
            return redirect()->route('platform.bonuses.issued.view', $request->item_id);
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
        }
    }
}
