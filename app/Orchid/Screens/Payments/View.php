<?php

namespace App\Orchid\Screens\Payments;

use App\Models\Comments;
use App\Models\Currency;
use App\Models\GroupPlayers;
use App\Models\PaymentSystem;
use App\Models\Players;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class View extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'View';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'View';

    public $section_id = 2;

    public $permission = [
        'platform.payments.view'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Payments $model, Request $request): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $user = $request->user();
            $player = Players::query()->where('id', $model->user_id)->first();
            $success = [
                1 => 'Yes',
                2 => 'No',
                3 => 'Warn',
                4 => 'Pending',
            ];

            $player_action = [
                1 => 'deposits',
                2 => 'cashouts',
                3 => 'chargebacks',
                4 => 'refunds',
                5 => 'reversals',
                6 => 'gifts'
            ];
            $success = $success[$model->success] ?? 0;
            $player_action = $player_action[$model->type_id] ?? '';
            $this->name = $player_action;
            $this->description = $model->email;
        }


        $payment_system = PaymentSystem::query()->where('id', '=', $model->payment_system_id)->first();
        $currency = Currency::query()->where('id', '=', $model->currency_id)->first();
        $admin = User::query()->where('id', '=', $model->staff_id)->select('email')->first();
        $comments = Comments::query()
            ->where('user_id', $model->id)
            ->where('section_id', $this->section_id)
            ->select('comments.id', 'users.name', 'users.email', 'comments.comment', 'comments.created_at')
            ->leftJoin('users', 'comments.staff_id', '=', 'users.id')
            ->orderBy('id', 'DESC')
            ->get();

        $args = [
            'Player' => $this->get_player($player) ?? '-',
            'Amount' => $model->amount . ' ' . $currency->code ?? '',
            'Success' => $success ?? 'Empty',
            'Action' => $player_action ?? 'Empty',
            'Admin User' => $admin->email ?? 'Empty',
            'Payment System' => $payment_system->name ?? 'Empty',
            'Payment Code' => $model->payment_code ?? 'Empty',
            'Network Fees' => $model->network_fee ?? '0.00',
            'Account' => 'Empty',
        ];

        if ($model->player_action === 1) {
            $args = array_merge($args, [
                'Currency' => $currency->code ?? 'Empty',
                'Recalled' => 'Empty',
                'User Agent' => $player->useragent ?? 'Empty',
                'Cashout Reject Reason' => 'Empty',
                'Security Checks Status' => 'Empty',
            ]);
        }

        if ($model->player_action === 2 || $model->player_action === 4) {
            $args = array_merge($args, [
                'Bin' => 'Empty',
                'Tx' => 'Empty',
                'Tx Name' => 'Empty',
                'AUTH Code' => 'Empty',
                'Provider' => 'Empty',
                'Tx Type' => 'Empty',
                'Attributes' => $model->attributes ?? 'Empty',
                'Account Holder' => 'Empty',
                'Masked Account' => 'Empty'
            ]);
        }

        if ($model->player_action === 3) {
            $args = array_merge($args, [
                'Fee' => 'Empty',
                'Tx' => 'Empty',
                'Fee Cy' => 'Empty',
                'Tx Name' => 'Empty',
                'AUTH Code' => 'Empty',
                'Provider' => 'Empty',
                'Tx Type' => 'Empty',
                'Attributes' => $model->attributes ?? 'Empty',
                'PSP Service' => 'Empty',
                'Tx PSP Amount' => 'Empty',
                'Account Holder' => 'Empty',
                'Tx PSP Amount Cy' => 'Empty',
            ]);
        }

        if ($model->player_action === 5) {
            $args = array_merge($args, [
                'Bin' => 'Empty',
                'Tx' => 'Empty',
                'Tx Name' => 'Empty',
                'AUTH Code' => 'Empty',
                'Currency' => $currency->code ?? 'Empty',
                'Provider' => 'Empty',
                'Tx Type' => 'Empty',
                'Attributes' => $model->attributes ?? 'Empty',
                'User Agent' => $player->useragent ?? 'Empty',
                'Status Code' => $model->payment_code ?? 'Empty',
                'Account Holder' => 'Empty',
                'Masked Account' => 'Empty'
            ]);
        }

        $args = array_merge($args, [
            'Finish At' => $model->finished_at,
            'Created At' => $model->created_at,
            'Updated At' => $model->updated_at
        ]);

        return [
            'table' => $args,
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
                            'item_id' => $model->id])
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
                ->class('btn btn-outline-secondary mb-2')
                ->icon('left')
                ->route('platform.payments'),
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
            Layout::view('orchid.payment.view'),
            Layout::view('orchid.info'),
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
            return redirect()->route('platform.payments.view', $request->item_id);
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
        }
    }

    public function get_player($model)
    {

        $groups = GroupPlayers::query()
            ->leftJoin('groups', 'group_players.group_id', '=', 'groups.id')
            ->select('group_players.user_id as id', 'groups.title', 'groups.color')->get()->toArray();

        $model->group = array_values(array_filter($groups, function ($k, $v) use ($model) {
            return $model->id === $k['id'];
        }, 1));


        $link = Link::make($model->email)
            ->class('link-primary')
            ->route('platform.players.profile', $model->id);

        $group = '';

        foreach ($model->group as $kye) {
            $color = $kye['color'];
            $title = $kye['title'];
            $group .= "<li style='background-color: $color;'><span style='border-color: transparent transparent transparent $color;'></span>$title</li>";
        }
        return "<div>$link<ul class='chip'>$group</ul></div>";
    }
}
