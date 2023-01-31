<?php

namespace App\Orchid\Screens\Players;

use App\Events\UpdateBalance;
use App\Http\Traits\AffiliateTrait;
use App\Http\Traits\BonusExpirationTrait;
use App\Http\Traits\SwapTrait;
use App\Models\BonusIssue;
use App\Models\Comments;
use App\Models\Currency;
use App\Models\Documents;
use App\Models\Events;
use App\Models\GamesBets;
use App\Models\GamesStats;
use App\Models\GroupPlayers;
use App\Models\Groups;
use App\Models\PaymentSystem;
use App\Models\PlayerArgs;
use App\Models\PlayerLocks;
use App\Models\PlayerPhone;
use App\Models\Tags;
use App\Models\TagItem;
use App\Models\Wallets;
use App\Orchid\Collection\DocumentFile;
use App\Orchid\Filters\IssuedBonusesFilter;
use App\Orchid\Layouts\ViewPlayerBinInfoTable;
use App\Orchid\Layouts\ViewPlayerBonusInfoTable;
use App\Orchid\Layouts\ViewPlayerDocumentTable;
use App\Orchid\Layouts\ViewPlayerLatestEventsTable;
use App\Orchid\Layouts\ViewPlayerLatestPaymentTable;
use App\Orchid\Layouts\ViewPlayerLatestSwapTable;
use App\Orchid\Layouts\ViewPlayerNetTotalTable;
use App\Orchid\Layouts\ViewPlayerSummaryTable;
use App\Orchid\Layouts\ViewPlayerLimitsTable;
use App\Traits\AttendancesTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Query\Expression;
use Laravel\Fortify\Rules\Password;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Str;
use Orchid\Support\Facades\Toast;
use App\Events\SessionKick;
use Orchid\Screen\Repository;
use Illuminate\Support\Facades\Http;

class ViewPlayer extends Screen
{
    use AttendancesTrait, BonusExpirationTrait, AffiliateTrait, SwapTrait;

    /**
     * Display header name.
     *
     * @var string
     */
    public $name;

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description;
    public $tags_value;
    public $id;
    public $bonuses;
    public $bonus_issue;
    public $technical;
    /**
     * @var bool
     */
    private $exist = false;

    public $permission = [
        'platform.players.profile'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model, Request $request, Documents $documents): array
    {

        $documents->load('attachment');
        $this->add_admin($request);
        $user = $request->user();
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
            Documents::query()->where('player_id', $model->id)->update(['status' => 2, 'staff_id' => $user->id]);
        }

        $response = Http::get('https://bitfiring.back-dev.com/lucky/public/api/spins/user/' . $model->id);
        $lucky = $response->collect();

        if (!empty(session('emulated'))) {
            Auth::guard('clients')->logout();
            session()->forget('emulated');
            Alert::info('You have successfully logout session player.');
        }

        $this->bonuses = DB::table('bonuses_user')->where('bonuses_user.user_id', '=', $model->id)->select('bonus_id')->get()->pluck('bonus_id');
        $this->bonus_issue = DB::table('bonus_issue')->where('bonus_issue.user_id', '=', $model->id)->whereNotNull('reference_id')->select('reference_id')->get()->pluck('reference_id');

        $bonus_info = \App\Models\BonusIssue::filters()
            ->filtersApply([IssuedBonusesFilter::class])
            ->leftJoin('bonuses', 'bonus_issue.bonus_id', '=', 'bonuses.id')
            ->leftJoin('currency', 'bonus_issue.currency_id', '=', 'currency.id')
            ->where('bonus_issue.user_id', '=', $model->id)
            ->select('bonus_issue.id', DB::Raw('IF(bonuses.title IS NULL, bonus_issue.custom_title, bonuses.title ) as title'), 'bonuses.strategy_id as strategy', 'bonus_issue.amount', 'bonus_issue.locked_amount',
                'currency.code as currency', 'bonus_issue.to_wager', 'bonus_issue.wagered', 'bonus_issue.active_until as expiry_at', 'bonus_issue.stage', 'bonus_issue.status', 'bonus_issue.created_at', 'bonus_issue.cat_type', 'bonus_issue.custom_title as title_frontend')
            ->orderBy('bonus_issue.id', 'DESC')
            ->limit(6)
            ->get();

        $wallet = \App\Models\Wallets::query()
            ->where('user_id', $model->id)
            ->where('currency_id', 14)
            ->select('balance')
            ->first();

        $currency = \App\Models\Currency::all();


        $deposit_sum = \App\Models\Payments::query()
            ->where('user_id', $model->id)
            ->where('type_id', 3)
            ->where('status', 1)
            ->select('amount_usd', 'amount', 'currency_id')
            ->get();

        $deposit_sum = $deposit_sum->map(function ($item, $key) use ($currency){
            if($item->amount_usd){
                return $item->amount_usd;
            }
            return ((float)$item->amount / (float)$currency->firstWhere('id', $item->currency_id)->rate);
        })->sum();

        $cashouts_sum = \App\Models\Payments::query()
            ->where('user_id', $model->id)
            ->where('type_id', 4)
            ->where('status', 1)
            ->select('amount_usd', 'amount', 'currency_id')
            ->get();

        $cashouts_sum = $cashouts_sum->map(function ($item, $key) use ($currency){
            if($item->amount_usd){
                return $item->amount_usd;
            }
            return ((float)$item->amount / (float)$currency->firstWhere('id', $item->currency_id)->rate);
        })->sum();

        $pending_cashouts_sum = \App\Models\Payments::query()
            ->where('user_id', $model->id)
            ->where('type_id', 4)
            ->where('status', 2)
            ->select('amount_usd', 'amount', 'currency_id')
            ->get();

        $pending_cashouts_sum = $pending_cashouts_sum->map(function ($item, $key) use ($currency){
            if($item->amount_usd){
                return $item->amount_usd;
            }
            return ((float)$item->amount / (float)$currency->firstWhere('id', $item->currency_id)->rate);
        })->sum();

        $corrections = \App\Models\Payments::query()
            ->where('user_id', $model->id)
            ->whereNotNull('staff_id')
            ->select('amount_usd', 'amount', 'currency_id')
            ->get();

        $corrections = $corrections->map(function ($item, $key) use ($currency){
            if($item->amount_usd){
                return $item->amount_usd;
            }
            return ((float)$item->amount / (float)$currency->firstWhere('id', $item->currency_id)->rate);
        })->sum();

        $gifts_sum = \App\Models\Payments::query()
            ->where('user_id', $model->id)
            ->where('type_id', 5)
            ->where('status', 1)
            ->select('amount_usd', 'amount', 'currency_id')
            ->get();

        $gifts_sum = $gifts_sum->map(function ($item, $key) use ($currency){
            if($item->amount_usd){
                return $item->amount_usd;
            }
            return ((float)$item->amount / (float)$currency->firstWhere('id', $item->currency_id)->rate);
        })->sum();

        $bet = \App\Models\GamesBets::query()
            ->where('user_id', $model->id)
            ->where('status', '!=', 2)
            ->select('profit','bet_sum', 'payoffs_sum')
            ->get();


        $profit = $bet->sum('profit');
        $bet_sum = $bet->sum('bet_sum');
        $payoffs_sum = $bet->sum('payoffs_sum');

        $bonus = \App\Models\BonusIssue::query()
            ->where('user_id', $model->id)
            ->whereIn('status', [2,3,4])
            ->whereIn('stage', [1,2,3])
            ->select('amount', 'fixed_amount', 'locked_amount', 'status')
            ->get();

        $bonus_amount = $bonus->sum('amount');
        $fixed_amount = $bonus->where('status', '!=', 4)->sum('fixed_amount');
        $locked_amount = $bonus->sum('locked_amount');

        $summary = collect([
            new Repository(['code' => 'USDT', 'balance' => $wallet->balance, 'deposit_sum' => $deposit_sum,
                'cashouts_sum' => $cashouts_sum, 'pending_cashouts_sum' => $pending_cashouts_sum,
                'corrections' => $corrections, 'gifts_sum' => $gifts_sum, 'bet_sum' => $bet_sum,
                'bonus_amount' => $bonus_amount, 'fixed_amount' => $fixed_amount, 'locked_amount' => $locked_amount,
                'payoffs_sum' => $payoffs_sum, 'profit' => $profit
                ])
        ]);
//        Wallets::query()
//            ->leftJoin('currency', 'wallets.currency_id', '=', 'currency.id')
//            ->where('wallets.user_id', $model->id)
//            ->where('wallets.currency_id', 14)
//            ->select(
//                DB::raw('(SELECT SUM(amount_usd) from payments WHERE user_id = wallets.user_id AND type_id = 3  AND status = 1) as deposit_sum'),
//                DB::raw('(SELECT SUM(amount_usd) from payments WHERE user_id = wallets.user_id AND type_id = 4 AND status = 1) as cashouts_sum'),
//                DB::raw('(SELECT SUM(amount_usd) from payments WHERE user_id = wallets.user_id AND type_id = 4 AND status = 2) as pending_cashouts_sum'),
//                DB::raw('(SELECT SUM(amount_usd) from payments WHERE user_id = wallets.user_id AND staff_id IS NOT NULL) as corrections'),
//                DB::raw('(SELECT SUM(amount_usd) from payments WHERE user_id = wallets.user_id AND type_id = 5 AND status = 1) as gifts_sum'),
//                DB::raw('(SELECT SUM(profit) from games_bets WHERE user_id = wallets.user_id AND status IN (1,2,3,5)) as bet_sum'),
//                DB::raw('(SELECT SUM(amount) from bonus_issue WHERE user_id = wallets.user_id AND status IN (2,3,4) AND stage IN (1,2,3)) as bonus'),
//                DB::raw('(SELECT SUM(fixed_amount) from bonus_issue WHERE user_id = wallets.user_id AND status IN (2,3,4)) as bonus_fixed'),
//                DB::raw('(SELECT SUM(locked_amount) from bonus_issue WHERE user_id = wallets.user_id  AND status IN (2,3,4)) as locked_amount'),
//                'currency.code', 'wallets.balance', 'currency.rate')
//            ->get();

        $comments = Comments::where('user_id', $model->id)
            ->select('comments.id', 'users.name', 'users.email', 'comments.comment', 'comments.created_at')
            ->leftJoin('users', 'comments.staff_id', '=', 'users.id')
            ->orderBy('id', 'DESC')
            ->get();

        $player_locks = PlayerLocks::query()->where('player_locks.player_id', $model->id)
            ->select('player_locks.reason', 'player_locks.comment', 'users.email')
            ->leftJoin('users', 'player_locks.staff_id', '=', 'users.id')
            ->paginate(5);

        $events = Events::query()
            ->where('events.user_id', $model->id)
            ->select('events.created_at', 'events.subject', 'events.ip', 'event_types.event_name as event_type',
                'events.address', 'events.coordinates', 'countries.code as country')
            ->leftJoin('countries', 'events.country_id', '=', 'countries.id')
            ->leftJoin('event_types', 'events.type_id', '=', 'event_types.id')
            ->limit(5)->get();

        $net_total = $summary;

        $gender = [
            '1' => 'Male',
            '2' => 'Female'
        ];

        $tags_value = Tags::query()
            ->leftJoin('tag_item', 'tags.id', '=', 'tag_item.tag_id')
            ->where('item_id', $model->id)
            ->select('tag_item.tag_id')
            ->get()->toArray();

        $this->add_player_args();

        $phone = PlayerPhone::query()->where('player_id', $model->id)->first();

        $player_number = null;

        if (!empty($phone) && $phone->phone) {
            try {
                $player_number = $phone->phone;
            } catch (Exception $e) {
                $player_number = null;
            }

        }

        $swaps = \App\Models\Transactions::query()
            ->where([['transactions.player_id', '=', $model->id], ['reference_type_id', '=', 9]])
            ->rightJoin('swaps', function ($join) {
                $join->on('transactions.reference_id', '=', 'swaps.id');
            })
            ->select('swaps.id', 'swaps.from_id', 'swaps.to_id', 'swaps.from_amount', 'swaps.to_amount', 'swaps.created_at')
            ->orderBy('swaps.id', 'DESC')
            ->groupBy('swaps.id')
            ->limit(5)->get();

        $payments = \App\Models\Transactions::query()
            ->join('payments', function ($join) {
                $join->on('transactions.reference_id', '=', 'payments.id');
            })
            ->where('payments.user_id', '=', $model->id)
            ->leftJoin('currency', 'payments.currency_id', '=', 'currency.id')
            ->leftJoin('payment_system', 'payments.payment_system_id', '=', 'payment_system.id')
            ->leftJoin('users', 'payments.staff_id', '=', 'users.id')
            ->select(
                'payments.player_action', 'payments.created_at', 'payments.amount', 'currency.rate', 'payments.id',
                DB::raw("CONCAT(payments.wallet_id, ':', currency.code) as account"),
                'payments.staff_id', 'payments.status', 'currency.code',
                DB::raw('(SELECT COUNT(id) from comments WHERE user_id = payments.user_id AND section_id = 2) as comment'),
                'payments.type_id as action', 'payments.source_address as wallet', 'payments.amount_usd', 'payment_system.name as source', 'payment_system.link_address as link', 'users.email as admin')
            ->whereIn('transactions.reference_type_id', [5, 1])
            ->orderBy('payments.id', 'DESC')
            ->limit(10)
            ->get();

//        if(\auth()->id() === 2){
//            dd($payments);
//        }
        $groups = '';
        $this->technical = [];
        foreach ($model->groups as $group) {
            if ($group->technical) {
                $this->technical[] = $group->id;
            }
            $color = $group->color;
            $title = $group->title;
            $groups .= "<li style='background-color: $color;'>$title</li>";
        }

        $group = GroupPlayers::query()
            ->leftJoin('groups', 'group_players.group_id', '=', 'groups.id')
            ->where('group_players.user_id', $model->id)
            ->select('groups.title', 'groups.color')->get()->toArray();

        $player_args = PlayerArgs::query()->where('player_id', $model->id)->first();

        $document = Documents::query()->where('player_id', $model->id)->limit(5)
            ->get();

        $limits = \App\Models\Limits::query()
            ->leftJoin('limit_types', 'limits.type_id', '=', 'limit_types.type_id')
            ->leftJoin('limit_duration', 'limits.period_id', '=', 'limit_duration.period_id')
            ->select('limits.id', 'limits.status', 'limits.account_limits', 'limits.period_id', 'limits.amount',
                'limits.current_values', 'limits.confirm_until', 'limits.created_at', 'limits.disabled_at', 'limit_duration.period_name', 'limit_types.type_name')
            ->where('limits.user_id', $model->id)
            ->orderBy('limits.id', 'DESC')
            ->get();

        return [
            'documents' => $documents,
            'player' => $model,
            'summary' => $summary,
            'limits' => $limits,
            'bonus_info' => $bonus_info,
            'latest_payments' => $payments,
            'latest_swaps' => $swaps,
            'bin_info' => $bonus_info,
            'net_total' => $net_total,
            'latest_events' => $events,
            'document' => $document,
            'lucky' => $lucky['data'],
            'actions' => [
                Link::make('Changes History')->route('platform.players.change', $model->id)->class('list-group-item list-group-item-action')->icon('paste'),
                Link::make('User Sessions')->route('platform.players.session', $model->id)->class('list-group-item list-group-item-action')->icon('user'),
                Link::make('Suspicions')->route('platform.players.suspicion', $model->id)->class('list-group-item list-group-item-action')->icon('action-redo'),
                Link::make('Games')->route('platform.players.games', $model->id)->class('list-group-item list-group-item-action')->icon('game-controller'),
                Link::make('Bets')->route('platform.games.bets', ['player' => $model->id])->class('list-group-item list-group-item-action')->icon('star'),
                Link::make('Swap')->route('platform.players.swap', $model->id)->class('list-group-item list-group-item-action')->icon('refresh'),
                Link::make('Limits')->route('platform.players.limits', $model->id)->class('list-group-item list-group-item-action')->icon('diamond'),
            ],
            'player_info' => [
                'table' => [
                    'Email' => "<div>$model->email
                                <ul class='groups'>$groups</ul>
                                <ul class='groups' style='position: absolute; left: 25px; bottom: -5px'><li>Registered at:</li><li>$model->created_at</li></ul>
                                </div>" ?? '-',
                    'Name' => $model->fullname ?? '-',
                    'Date of birth' => "<div> $model->dob</div>",
                    'Gender' => $model->gender ? $gender[$model->gender] : '-',
                    'Country' => $model->countries->name ?? '-',
                    'City' => $model->city ?? '-',
                    'Address' => $model->address ?? '-',
                    'Postal code' => $model->postal_code ?? '-',
                ],
                'comment' => [
                    'textarea' => TextArea::make('comment')
                        ->rows(5)
                        ->id('text_comment')
                        ->title('Add a comment'),
                    'submit' => Button::make('Add')
                        ->icon('check')
                        ->method('add_comment')
                        ->id('add_comment')
                        ->class('btn btn-rounded btn-primary')
                        ->parameters([
                            'staff_id' => $user->id,
                            'player_id' => $model->id
                        ])
                ],
            ],
            'comments' => [
                'content' => $comments
            ],
            'status' => [
                'content' => [
                    Select::make('status')
                        ->title('Status')
                        ->options([
                            0 => 'disabled',
                            1 => 'enabled',
                        ])
                        ->autocomplete(true)
                        ->value($model->status),
                ],
                'suspicious' => $model->suspicious ? "Suspicious: Yes" : "Suspicious: No",
                'submit' => [
                    Button::make(__($model->suspicious ? 'Remove suspicious' : 'Mark as suspicious'))
                        ->class('btn btn-link')
                        ->icon('check')
                        ->method('susp_change')
                        ->class('btn btn-rounded btn-secondary')
                        ->confirm("Are you sure " . $model->suspicious ? 'you want to remove suspicious?' : 'you want to  mark as suspicious?')
                        ->parameters([
                            'player_id' => $model->id,
                            'suspicious' => $model->suspicious ? 0 : 1
                        ]),
                    Button::make('Change')
                        ->icon('check')
                        ->method('status_change')
                        ->class('btn btn-rounded btn-primary')
                        ->parameters([
                            'player_id' => $model->id])]
            ],
            'player_locks' =>
                $player_locks,
            'tags' => [
                'content' => [
                    Select::make('tags')
                        ->taggable()
                        ->multiple()
                        ->fromModel(
                            Tags::class, 'name', 'id')
                        ->value($this->tags($tags_value))->autocomplete(true)
                ],
                'submit' => [
                    Button::make('Save')
                        ->icon('check')
                        ->method('add_tags')
                        ->class('btn btn-rounded btn-primary')
                        ->parameters([
                            'player_id' => $model->id,
                            'tags_value' => $this->tags($tags_value)
                        ])
                ]
            ],
            'groups' => [
                'content' => $group,
                'action' => Group::make([
                    ModalToggle::make('Manage Groups')
                        ->modal('Manage Groups')
                        ->method('add_tester')
                        ->canSee(Groups::query()->where('technical', '=', 1)->whereNotIn('id', $this->technical)->count())
                        ->parameters(['player_id' => $model->id])
                        ->class('btn btn-primary btn-rounded'),

                    ModalToggle::make('Delete Groups')
                        ->modal('Delete Groups')
                        ->method('delete_tester')
                        ->canSee(count($this->technical))
                        ->parameters(['player_id' => $model->id])
                        ->class('btn btn-secondary btn-rounded'),
                ])->autoWidth()
            ],
            'phones' => [
                'table' => [
                    'Country Code' => isset($player_number) ? $player_number : '-',
                    'Phone Code' => isset($player_number) ? $player_number : '-',
                    'Phone' => isset($phone->phone) ?? '-',
                    'Active' => isset($phone->active) ? 'Yes' : 'No',
                    'Verified' => isset($phone->verified) ? 'Yes' : 'No',
                ],
                'content' => [
                    Input::make('phone[phone]')
                        ->title('Phone')
                        ->autocomplete(true)
                        ->value($phone->phone ?? ''),
                ],
                'verify' => isset($phone->verified) && $phone->verified ? "Verified: Yes" : "Verified: No",
                'submit' => [
                    Button::make(__(isset($phone->verified) && $phone->verified ? 'No Verify' : 'Verify'))
                        ->class('btn btn-link')
                        ->icon('check')
                        ->method('verified_phone')
                        ->class('btn btn-default')
                        ->confirm("Are you sure, " . (isset($phone->verified) && $phone->verified) ? 'you want to no verify your phone?' : 'you want to verify your phone?')
                        ->parameters([
                            'player_id' => $model->id,
                            'verified' => isset($phone->verified) ? !$phone->verified : 0
                        ]),
                    Button::make('Edit')
                        ->icon('check')
                        ->method('edit_phone')
                        ->class('btn btn-default')
                        ->parameters([
                            'player_id' => $model->id
                        ])]
            ],
            'player_args' => [
                'table' => [
                    'GA' => $player_args->ga ?? '-',
                    'UTM Source' => $player_args->utm_source ?? '-',
                    'UTM Medium' => $player_args->utm_medium ?? '-',
                    'UTM Campaign' => $player_args->utm_campaign ?? '-',
                    'UTM Content' => $player_args->utm_content ?? '-',
                    'UTM Term' => $player_args->utm_term ?? '-',
                    'S TAG Affiliate' => $player_args->s_tag_affiliate ?? '-',
                    'S TAG Visit' => '-',
                    'Btag' => $player_args->btag ?? '-',
                    'Btag Net Refer' => $player_args->btag_net_refer ?? '-',
                    'QTAG' => $player_args->qtag ?? '-',
                ],
            ],
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
            Button::make('Sign in as Player')
                ->icon('user')
                ->method('sign_player')
                ->class('btn btn-secondary mb-2')
                ->rawClick()
                ->parameters(['player_id' => $this->id]),

            Link::make('Edit')
                ->route('platform.players.edit', $this->id)
                ->class('btn btn-secondary mb-2')
                ->icon('pencil'),

            Link::make('Return')
                ->class('btn btn-outline-secondary mb-2')
                ->icon('left')
                ->route('platform.players'),

            ModalToggle::make('Change Password')
                ->modal('Change Password')
                ->method('change_password')
                ->icon('key')
                ->parameters(['user.id' => $this->id])
                ->class('btn btn-secondary mb-2'),
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
            Layout::wrapper('orchid.wrapper', [
                'col_left' => Layout::tabs([
                    'Player info' => Layout::view('orchid.players.player-info'),
                    'Status' => Layout::view('orchid.players.status'),
                    'Player locks' => Layout::table('player_locks', [
                        TD::make('suspicion_name', 'Reason')->sort(),
                        TD::make('comment', 'Comment')->sort(),
                        TD::make('email', 'Staff email')->sort(),
                    ]),
                    'Players details' => Layout::view('orchid.players.player-details'),
                    'Tags' => Layout::view('orchid.players.tags'),
                    'Groups' => Layout::view('orchid.players.groups'),
                    'Player ad args' => Layout::view('orchid.players.player-ad-args'),
                    'Phones' => Layout::view('orchid.players.phones'),
                ]),
                'col_right' => Layout::view('orchid.players.action-link')
            ]),

            new ViewPlayerSummaryTable(),
            new ViewPlayerLatestPaymentTable(),
            new ViewPlayerBonusInfoTable(),
            new ViewPlayerLatestSwapTable(),
            new ViewPlayerNetTotalTable(),
            new ViewPlayerDocumentTable(),

            Layout::view('orchid.table.player_lucky'),
//            Layout::view('orchid.collapse'),
            new ViewPlayerLimitsTable(),
            Layout::view('orchid.players.comments'),
            Layout::modal('Issue Bonus', [
                Layout::rows([
                    Input::make('bonus.id')->type('hidden')->value($this->id),
                    Select::make('bonus.bonus')->fromQuery(\App\Models\Bonuses::query()->whereNotIn('id', $this->bonuses ?? []), 'title')->required()->title('Bonus Name'),
                    Select::make('bonus.dep')->fromQuery(\App\Models\Payments::query()
                        ->join('currency', 'payments.currency_id', '=', 'currency.id')
                        ->where([['user_id', '=', $this->id], ['type_id', '=', 3]])
                        ->whereIn('status', [3, 1])
                        ->whereNotIn('payments.id', $this->bonus_issue)
                        ->select('payments.id', DB::Raw("CONCAT(currency.code, ' - ', payments.amount , ' - ', payments.created_at) as dep"))->orderBy('payments.id', 'DESC'), 'dep')
                        ->required()
                        ->title('Deposit'),
                    TextArea::make('bonus.comment')->title('Comment'),
                ]),
            ]),
            Layout::modal('Issue Personal Bonus', [
                Layout::rows([
                    Input::make('issue.id')->type('hidden')->value($this->id),
                    Input::make('issue.bonus')->title('Bonus Name')->required(),
                    Select::make('issue.currency')->fromQuery(\App\Models\Currency::query()->where('excluded', '!=', 1), 'code')->title('Currency')->required(),
                    Input::make('issue.amount')->title('Amount')->required(),
                    Input::make('issue.wager_multiplier')->title('Wager Multiplier')->required(),
                    DateTimer::make('issue.valid_until')->title('Expired Date')->required(),
                    TextArea::make('issue.comment')->title('Comment')
                ]),
            ]),
            Layout::modal('Change Password', [
                Layout::rows([
                    Input::make('user.password')->type('password')->title('Password')->required(),
                    Input::make('user.password_confirmation')->type('password')->title('Confirmed Password')->required(),
                ]),
            ]),
            Layout::modal('Manage Groups', [
                Layout::rows([
                    Select::make('group_id')->fromQuery(Groups::query()->where('technical', '=', 1)->whereNotIn('id', $this->technical), 'title')
                ]),
            ]),
            Layout::modal('Delete Groups', [
                Layout::rows([
                    Select::make('group_id')->fromQuery(Groups::query()->where('technical', '=', 1)->whereIn('id', $this->technical), 'title')
                ]),
            ]),
        ];
    }

    public function action_issue(Request $request)
    {
        $input = $request->all();
        $input = $input['issue'];

        $admin = Auth::user();

        $currency = DB::table('currency')->where('id', $input['currency'])->first();
        $amount = (float)$input['amount'];

        $bonus_amount = $amount / $currency->rate;

        DB::table('bonuses_user')->insert([
            'user_id' => $input['id'],
            'published' => 1,
            'stage' => 2,
            'currency' => $input['currency'],
            'bonus_id' => $input['bonus'],
            'amount' => $bonus_amount,
            'wager' => $input['wager_multiplier'],
            'created_at' => \Illuminate\Support\Carbon::now(),
        ]);

        $locked_amount = $bonus_amount;
        $to_wager = (float)$input['wager_multiplier'] * $bonus_amount;

        $id = DB::table('bonus_issue')->insertGetId([
            'user_id' => $input['id'],
            'currency_id' => $input['currency'],
            'bonus_id' => $input['bonus'],
            'amount' => $bonus_amount,
            'locked_amount' => $locked_amount,
            'fixed_amount' => $locked_amount,
            'active_until' => $input['valid_until'],
            'to_wager' => $to_wager,
            'wagered' => 0,
            'stage' => 2,
            'status' => 2,
            'admin_id' => $admin->id,
            'custom_title' => $input['bonus'],
            'created_at' => \Illuminate\Support\Carbon::now(),
        ]);

        $wallet = DB::table('wallets')->where([
            ['user_id', '=', $input['id']],
            ['currency_id', '=', $input['currency']]
        ])->first();


        if (empty($wallet)) {
            $array = [[
                'primary' => 1,
                'currency_id' => $input['currency'],
                'user_id' => $input['id'],
                'balance' => $amount,
            ]];

            DB::table('wallets')->insert($array);
        } else {
            $new_balance = ((float)$wallet->balance ?? 0) + $amount;

            DB::table('wallets')->where('id', '=', $wallet->id)->update([
                'balance' => $new_balance,
            ]);
        }

        if ($input['currency'] != 14) {
            $this->handler_swap($input['id'], $input['currency'], 14, (float)$bonus_amount);
        }

        $staff = Auth::user();
        $this->add_comment_bonus($staff->id, $id, $input['comment']);
    }

    public function action_bonus(Request $request)
    {
        $input = $request->all();
        $model = \App\Models\Players::find($request->id);
        $input = $input['bonus'];

        $admin = Auth::user();

        $payment = DB::table('payments')->where('id', '=', $input['dep'])->first();
        $wallet = DB::table('wallets')->where('id', '=', $payment->wallet_id)->first();

        $bonus = \App\Models\Bonuses::find($input['bonus']);
        $expiry = new \App\Models\Bonuses();
        $expiry_method = $bonus->duration_type ? $expiry::DURATION[$bonus->duration_type] : null;

        $currency = DB::table('currency')->where('id', $payment->currency_id)->first();

        $amount = (float)($payment->amount);
        $percentage = ((int)$bonus->amount / 100);

        $bonus_amount = $amount / $currency->rate;
        $bonus_amount = $bonus->max >= ($bonus_amount * $percentage) ? ($bonus_amount * $percentage) : $bonus->max;

        $new_balance = (float)$wallet->balance + ($bonus_amount * $currency->rate);

        DB::table('bonuses_user')->insert([
            'user_id' => $input['id'],
            'published' => 1,
            'stage' => 2,
            'currency' => $payment->currency_id,
            'bonus_id' => $bonus->id,
            'amount' => $bonus_amount,
            'wager' => $bonus->wager,
            'created_at' => \Illuminate\Support\Carbon::now(),
        ]);

        $locked_amount = ((float)$payment->amount / $currency->rate) + $bonus_amount;
        $to_wager = $locked_amount * $bonus->wager;

        $id = DB::table('bonus_issue')->insertGetId([
            'user_id' => $input['id'],
            'currency_id' => $payment->currency_id,
            'bonus_id' => $bonus->id,
            'amount' => $bonus_amount,
            'locked_amount' => $locked_amount,
            'fixed_amount' => $locked_amount,
            'reference_id' => $payment->id,
            'to_wager' => $to_wager,
            'wagered' => 0,
            'stage' => 2,
            'status' => 2,
            'admin_id' => $admin->id,
            'created_at' => \Illuminate\Support\Carbon::now(),
            'active_until' => $expiry_method ? $expiry->$expiry_method($bonus->duration) : null,
        ]);

        DB::table('wallets')->where('id', '=', $wallet->id)->update([
            'balance' => $new_balance,
        ]);

        if ($payment->currency_id !== 14) {
            $this->handler_swap($input['id'], $payment->currency_id, 14, ($bonus_amount * $currency->rate));
        }

        $staff = Auth::user();
        $this->add_comment_bonus($staff->id, $id, $input['comment']);

        if (!!$model->affiliate_aid) {
            $amount = (float)$payment->amount / (float)$currency->rate;
            $this->affiliate_deposit($model->affiliate_aid, $model->id, $amount, $payment->id, $bonus);
        }
    }

    public function add_comment(Request $request)
    {
        try {
            $this->validate($request, [
                'staff_id' => ['required', 'integer'],
                'player_id' => ['required', 'integer'],
                'comment' => ['required', 'string'],
            ]);
            $args = [
                'staff_id' => $request->staff_id,
                'user_id' => $request->player_id,
                'created_at' => Carbon::now()->format('Y-m-d H:m:s'),
                'section_id' => 1,
                'comment' => $request->comment
            ];

            Comments::query()->create($args);
            Alert::info('You have successfully.');
            return redirect(url()->current() . "#comment");
//            return response()->json(['comment' => $comment, 'staff' => ['email' => $comment->staff->email, 'name' => $comment->staff->name]]);
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
        }
    }

    public function susp_change(Request $request)
    {
        try {
            $input = $request->all();

            $this->validate($request, [
                'player_id' => ['required', 'integer'],
                'suspicious' => ['required', 'integer'],
            ]);

            \App\Models\Players::query()->where('id', $input['player_id'])->update(['suspicious' => $input['suspicious']]);


            Alert::info('You have successfully.');
            return redirect()->route('platform.players.profile', $input['player_id']);

        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
        }
    }

    public function status_change(\App\Models\Players $model, Request $request)
    {
        try {
            $input = $request->all();
            $this->validate($request, [
                'player_id' => ['required', 'integer'],
                'status' => ['required', 'integer'],
            ]);

            $player = \App\Models\Players::find($input['player_id']);
            if ($player->status === (int)$input['status']) {
                return redirect()->back();
            }

            \App\Models\Players::query()->where('id', $input['player_id'])->update(['status' => $input['status']]);

            if (!!$input['status']) {
                GroupPlayers::query()->where([
                    ['user_id', '=', $input['player_id']],
                    ['group_id', '=', 16]
                ])->delete();
            } else {
                if (!$model->groups->where('id', '=', 16)->count()) {
                    GroupPlayers::query()->insert([
                        'user_id' => $input['player_id'],
                        'group_id' => 16
                    ]);
                }


                event(new SessionKick($input['player_id']));
            }

            Alert::info('You have successfully.');
            return redirect()->route('platform.players.profile', $request->player_id);
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
        }
    }

    public function add_tags(Request $request)
    {
        try {
            if (!empty($request->tags_value)) {
                $del_tags = array_diff($request->tags_value, $request->tags ?? []);
                TagItem::query()->whereIn('tag_id', $del_tags)->delete();
            }

            if (!empty($request->tags)) {
                $tags_type = [];

                foreach ($request->tags as $kye => $value) {
                    $value = (int)$value === 0 ? $value : (int)$value;
                    if (is_int($value)) {
                        $tags_type[] = [
                            'section_id' => 1,
                            'item_id' => (int)$request->player_id,
                            'tag_id' => $value,
                        ];
                        continue;
                    }
                    $tags = [
                        'name' => $value,
                        'slug' => Str::slug($value),
                        'section_id' => 1,
                    ];
                    $id = Tags::insertGetId(
                        $tags
                    );
                    $tags_type[] = [
                        'section_id' => 1,
                        'item_id' => (int)$request->player_id,
                        'tag_id' => $id,
                    ];
                }
                TagItem::query()->insert($tags_type);
                Alert::info('You have successfully.');
            }
        } catch (\Exception $exception) {
            Alert::warning('Oops, unknown error.');
        }
    }

    public function tags($query): array
    {
        return array_column($query, 'tag_id');
    }

    public function upload(Request $request)
    {
        if (!$request->allFiles()) {
            Documents::query()->where('id', $request->id)->update(['description' => $request->description, 'original_name' => $request->original_name]);
            return response()->json($request);
        }

        $collection = collect($request->allFiles())
            ->flatten()
            ->map(function (UploadedFile $file) use ($request) {
                return $this->createModel($file, $request);
            });

        $response = $collection->count() > 1 ? $collection : $collection->first();
        return response()->json($response);
    }

    /**
     * @param Request $request
     */
    public function upload_sort(Request $request, Documents $documents)
    {
        collect($request->get('files', []))
            ->each(function ($sort, $id) use ($documents) {
                $attachment = $documents->find($id);
                $attachment->sort = $sort;
                $attachment->save();
            });
    }

    public function delete_file(Request $request)
    {
        if ($request->data) {
            $result = $request->data;
            Documents::query()->where('id', $result['id'])->delete();
            dd(Storage::disk('local')->delete('public/' . $result['image']));
        }
        return response()->json($request);
    }


    private function createModel(UploadedFile $file, Request $request)
    {
//        $this->saveStorage($file, $request);
        $model = resolve(DocumentFile::class, [
            'file' => $file,
            'disk' => $request->get('storage'),
            'group' => $request->get('group'),
            'player_id' => $request->get('player_id')
        ])->load();
        $model->url = $model->url();

        return $model;
    }

    private function saveStorage($file, $request)
    {
        $path = "/documents/";
        $storage = Storage::disk($request->get('storage'));
        $storage->put($path, $file);
    }

    public function add_player_args()
    {

        $rand = ['Casino', 'Bet', 'Kingdom', 'Fan', 'Slots', 'Vegas', 'Play', 'Spin', 'Joker', 'Party', 'Pay',
            'Win', 'Million', 'Cart', 'Gaming', 'Room', '777', 'One', 'First', 'Starz', 'Instant', 'Safe', 'Energy',
            'Match', '1x', 'Heroes'];

        $id = \App\Models\Players::query()->latest('id')
            ->first()->id;;
        $args = [];
        $i = 1;
        while ($i <= $id) {
            $args[] = [
                'player_id' => $i,
                'ga' => $this->get_ga(),
                'utm_source' => $rand[rand(0, count($rand) - 1)],
                'utm_medium' => 'cpc',
                'utm_content' => 'creative',
                'utm_campaign' => 'network',
                'utm_term' => 'keyword',
                's_tag_affiliate' => '',
                'btag' => $rand[rand(0, count($rand) - 1)] . ' Group',
                'btag_net_refer' => rand(10000, 99999),
                'qtag' => rand(10000, 99999)
            ];
            $i++;
        }

        //2341234sdaf-d234-324f-b234-23423423p
//        PlayerArgs::query()->insert($args);
//        dd(true);
    }

    public function get_ga()
    {
        $array = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
            's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        );
        $ga = '';
        //2341234sdaf-d234-324f-b234-23423423p
        for ($i = 0; $i < 36; $i++) {
            if ($i === 11) {
                $ga .= '-';
            }
            if ($i === 15) {
                $ga .= '-';
            }
            if ($i === 20) {
                $ga .= '-';
            }
            if ($i === 25) {
                $ga .= '-';
            }
            $ga .= $array[rand(0, count($array) - 1)];
        }
        return $ga;
    }

    public function verified_phone(Request $request)
    {
        try {
            $this->validate($request, [
                'player_id' => ['required', 'integer'],
                'verified' => ['required', 'integer'],
            ]);
            PlayerPhone::query()->where('player_id', $request->player_id)->update(['verified' => $request->verified]);
            Alert::info('You have successfully.');
            return redirect()->route('platform.players.profile', $request->player_id);
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
        }
    }

    public function edit_phone(Request $request)
    {
        try {
            $this->validate($request, [
                'player_id' => ['required', 'integer'],
            ]);
            PlayerPhone::query()->where('player_id', $request->player_id)->update(['phone' => $request->phone['phone']]);
            Alert::info('You have successfully.');
            return redirect()->route('platform.players.profile', $request->player_id);
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
        }

    }

    public function status_state_change(Request $request)
    {
        $input = $request->all();
        $bonus = BonusIssue::find($input['status_state_change_id']);
        $bonus->status = (int)$input['status_state_change_status'];
        if ((int)$input['status_state_change_status'] === 2) {
            $currency = Currency::find($bonus->currency_id);
            $amount = ($bonus->locked_amount / 2);
            Wallets::query()
                ->where([
                    ['currency_id', '=', $bonus->currency_id],
                    ['user_id', '=', $bonus->user_id]
                ])
                ->update(['balance' => new Expression('balance + ' . ($amount * $currency->rate))]);
            event(new UpdateBalance($bonus->user_id));
        }

        if ((int)$input['status_state_change_status'] === 4) {
            $amount = -1 * ($bonus->locked_amount / 2);

            $this->handler_user_wallet($bonus->user_id, $amount);
            event(new UpdateBalance($bonus->user_id));
        }

        if((int)$input['status_state_change_status'] === 5 || (int)$input['status_state_change_status'] === 6){

            $ggr = $bonus->locked_amount - $bonus->fixed_amount;

            if($bonus->amount - $ggr > 0){
                $amount = -($bonus->fixed_amount - ($bonus->amount - $ggr));
            }

            if($bonus->amount - $ggr < 0){
                $amount = -$bonus->fixed_amount;
            }

            if($bonus->amount - $ggr >= $bonus->amount){
                $amount = -($bonus->amount - $ggr);
            }


            $wallet = \App\Models\Wallets::query()
                ->where('user_id', $bonus->user_id)
                ->where('currency_id', '=', $bonus->currency_id)
                ->first();

            $this->insert_transaction([
                'amount' => $amount,
                'bonus_part' => 0,
                'currency_id' => $bonus->currency_id,
                'reference_id' => $bonus->id,
                'wallet_id' => $wallet->id,
                'player_id' => $bonus->user_id,
                'type_id' => 1,
                'reference_type_id' => 5,
                'amount_usd' => $amount
            ]);

            $this->handler_user_wallet($bonus->user_id, $amount);
            event(new UpdateBalance($bonus->user_id));
        }
        $bonus->save();
        Alert::info('You have successfully.');
    }

    public function change_password(\App\Models\Players $player, Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input['user'], [
            'password' => 'required|between:8,25|confirmed|regex:/^(?=\S+$)/',
        ]);

        if ($validator->passes()) {
            $player->password = Hash::make($input['user']['password']);
            $player->save();
            Alert::info('You have successfully.');
            return;
        }

        foreach ($validator->errors()->messages() as $key => $error) {
            Toast::error("$key: $error[0]");
        }
    }

    public function sign_player(Request $request)
    {

        $loggedInUser = Auth::guard('clients')->loginUsingId($request->player_id);
        if (!$loggedInUser) {
            throw new Exception('Single SignOn: User Cannot be Signed In');
        }

        session()->put('emulated', $request->player_id);
        return redirect()->route('vue_capture', ['vue_capture' => '/']);
    }

    public function add_comment_bonus($staff_id, $payment_id, $comment)
    {
        $args = [
            'staff_id' => $staff_id,
            'user_id' => $payment_id,
            'created_at' => Carbon::now()->format('Y-m-d H:m:s'),
            'section_id' => 4,
            'comment' => $comment
        ];
        Comments::query()->insert($args);
    }

    public function add_tester(\App\Models\Players $model, Request $request)
    {
        $input = $request->all();

        if ($input['group_id'] === '21' || $input['group_id'] === '18') {
            $response = Http::post('https://affiliates.bitfiring.com/scam_player', [
                'id' => $model->id,
            ]);

            if ($response->ok()) {
                Alert::info('You have successfully.');
            }
        }

        GroupPlayers::query()->insert([
            'user_id' => $input['player_id'],
            'group_id' => $input['group_id']
        ]);
    }

    public function delete_tester(Request $request)
    {
        $input = $request->all();

        GroupPlayers::query()->where([
            ['user_id', '=', $input['player_id']],
            ['group_id', '=', $input['group_id']]
        ])->delete();
    }

}
