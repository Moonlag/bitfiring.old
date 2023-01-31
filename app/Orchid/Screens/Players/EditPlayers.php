<?php

namespace App\Orchid\Screens\Players;

use App\Models\Comments;
use App\Models\Events;
use App\Models\GamesStats;
use App\Models\PlayerArgs;
use App\Models\PlayerLocks;
use App\Models\PlayerPhone;
use App\Models\Tags;
use App\Models\TagItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Str;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;

class EditPlayers extends Screen
{
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

    /**
     * @var bool
     */
    private $exist = false;

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model, Request $request): array
    {
        $user = $request->user();
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
        }
        $bonuses = \App\Models\Bonuses::select()
            ->Join('bonuses_user', 'bonuses.id', '=', 'bonuses_user.bonus_id')
            ->Join('bonus_translations', 'bonuses.id', '=', 'bonus_translations.bonus_id')
            ->where('bonuses_user.user_id', '=', $model->id)->first();

        $comments = Comments::where('user_id', $model->id)
            ->select('comments.id', 'users.name', 'users.email', 'comments.comment', 'comments.created_at')
            ->leftJoin('users', 'comments.staff_id', '=', 'users.id')
            ->orderBy('id', 'DESC')
            ->get();

        $player_locks = PlayerLocks::query()->where('player_locks.player_id', $model->id)
            ->select('player_locks.reason', 'player_locks.comment', 'users.email')
            ->leftJoin('users', 'player_locks.staff_id', '=', 'users.id')
            ->paginate(5);

        $events = Events::query()->where('events.user_id', $model->id)
            ->select('events.created_at', 'events.ip', 'event_types.event_name', 'events.country',
                'events.address', 'events.coordinates')
            ->leftJoin('event_types', 'events.type_id', '=', 'event_types.id')
            ->latest('events.id')
            ->first();

        $gameStats = GamesStats::query()->where('user_id', $model->id)->latest('id')
            ->first();;


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
        if($phone->phone){
            $player_number = PhoneNumber::parse($phone->phone);
        }
        $payments = \App\Models\Payments::where('user_id', '=', $model->id)->latest('id')->first();
        $player_args = PlayerArgs::where('player_id', $model->id)->first();
        return [
            'player' => $model,
            'collapse' => [
                [
                    'title' => 'Summary',
                    'table' => [
                        'Currency' => $model->currency ?? '-',
                        'Balance' => $model->balance ?? '0.00',
                        'Deposits Sum' => $model->deposit_sum ?? '0.00',
                        'Cashouts sum' => '0.00',
                        'Chargebacks sum' => '0.00',
                        'Refunds sum' => '0.00',
                        'Spent in casino' => $player->bonus_count ?? '0',
                        'Bonus ration' => '-',
                    ],
                    'action' => Link::make('Change Balance')
                        ->route('platform.players.balance', $model->id)
                        ->icon('link')->class('btn  btn-default'),
                ],
                [
                    'title' => 'Bonus info',
                    'table' => [
                        'Title' => $bonuses->title ?? '-',
                        'Issued at' => $bonuses->created_at ?? '-',
                        'Amount' => $bonuses->amount ?? '-',
                        'Stage' => $bonuses->stage ?? '-',
                        'Amount locked' => '-',
                        'Wager' => $bonuses->wager ?? '-',
                        'Expiry date' => $bonuses->expiry_date ?? '-',
                        'Action' => Link::make('Cancel') ?? '-',
                    ],
                    'action' => Link::make('All Bonuses')->icon('link')->class('btn  btn-default'),
                ],
                [
                    'title' => 'Latest payments',
                    'table' => [
                        'Action' => $payments->player_action ?? '-',
                        'Source' => $payments->source ?? '-',
                        'Account' => '-',
                        'Admin user' => '-',
                        'Manual' => '-',
                        'Comment' => '-',
                        'Success' => $payments->success ?? '-',
                        'Bonus ration' => $payments->finished_at ?? '-',
                    ],
                ],
                [
                    'title' => 'BIN info',
                    'table' => [
                        'Currency' => '-',
                        'System' => '-',
                        'Account' => '-',
                        'Bank name' => '-',
                        'Bank country' => '-',
                        'Stage' => '-',
                        'Card type' => '-',
                    ],
                ],
                [
                    'title' => 'Net total',
                    'table' => [
                        'Currency' => $gameStats->currency ?? '-',
                        'Total bets' => '-',
                        'Total wins' => $gameStats->total_winnings ?? '-',
                        'Bonuses' => '-',
                        'Net Total' => $gameStats->profit ?? '-',
                        'Payout' => $gameStats->payout ?? '-',
                    ],
                ],
                [
                    'title' => 'Latest events',
                    'table' => [
                        'Date' => $events->created_at ?? '-',
                        'Event type' => $events->event_name ?? '-',
                        'IP' => $events->ip ?? '-',
                        'Country' => $events->country ?? '-',
                        'Address' => $events->address ?? '-',
                        'Coordinates' => $events->coordinates ?? '-',
                    ],
                    'action' => Link::make('All events')->icon('link')->class('btn  btn-default'),
                ],
                [
                    'title' => 'Documents',
                    'table' => [
                        'Preview' => '-',
                        'Description' => '-',
                        'Created at' => '-',
                        'Updated at' => '-',
                        'Status' => '-',
                    ],
                ],
            ],
            'actions' => [
                Link::make('Account Limits')->route('platform.players.limits', $model->id)->icon('pie-chart'),
                Link::make('Changes History')->icon('paste'),
                Link::make('User Sessions')->route('platform.players.session', $model->id)->icon('user'),
                Link::make('Comments(' . count($comments) . ')')->icon('speech'),
                Link::make('Suspicions')->route('platform.players.suspicion', $model->id)->icon('action-redo'),
                Link::make('Games')->route('platform.players.games', $model->id)->icon('game-controller'),
                Link::make('Bets')->route('platform.players.bets', $model->id)->icon('star'),
            ],
            'player_info' => [
                'table' => [
                    'Name' => $model->fullname ?? '-',
                    'Date of birth' => $model->dob ?? '-',
                    'Gender' => $model->gender ? $gender[$model->gender] : '-',
                    'Country' => $model->country ?? '-',
                    'City' => $model->city ?? '-',
                    'Address' => $model->address ?? '-',
                    'Postal code' => $model->postal_code ?? '-',
                ],
                'comment' => [
                    'textarea' => TextArea::make('comment')
                        ->rows(5)
                        ->title('Add a comment'),
                    'submit' => Button::make('Add')
                        ->icon('check')
                        ->method('add_comment')
                        ->class('btn  btn-default')
                        ->parameters([
                            'staff_id' => $user->id,
                            'user_id' => $model->id])
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
                        ->class('btn btn-default')
                        ->confirm("Are you sure " . $model->suspicious ? 'you want to remove suspicious?' : 'you want to  mark as suspicious?')
                        ->parameters([
                            'player_id' => $model->id,
                            'suspicious' => $model->suspicious ? 0 : 1
                        ]), Button::make('Change')
                        ->icon('check')
                        ->method('status_change')
                        ->class('btn btn-default')
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
                        ->class('btn btn-default')
                        ->parameters([
                            'player_id' => $model->id,
                            'tags_value' => $this->tags($tags_value)
                        ])
                ]
            ],
            'phones' =>[
                'table' => [
                    'Country Code' => $player_number ? $player_number->getRegionCode() : '-',
                    'Phone Code' => $player_number ? $player_number->getCountryCode() : '-',
                    'Phone' => $phone->phone ?? '-',
                    'Active' => $phone->active ? 'Yes' : 'No',
                    'Verified' => $phone->verified ? 'Yes' : 'No',
                ],
            ],
            'player_args' =>[
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
            Layout::view('orchid.collapse'),
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
                    'Groups' => Layout::view('platform::dummy.block'),
                    'Player ad args' => Layout::view('orchid.players.player-ad-args'),
                    'Phones' => Layout::view('orchid.players.phones'),
                ]),
                'col_right' => Layout::view('orchid.players.action-link')
            ]),
            Layout::view('orchid.players.comments'),
        ];
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
            Comments::query()->insert($args);
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

    public function susp_change(Request $request)
    {
        try {
            $this->validate($request, [
                'player_id' => ['required', 'integer'],
                'suspicious' => ['required', 'integer'],
            ]);
            \App\Models\Players::query()->where('id', $request->player_id)->update(['suspicious' => $request->suspicious]);
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

    public function status_change(Request $request)
    {
        try {
            $this->validate($request, [
                'player_id' => ['required', 'integer'],
                'status' => ['required', 'integer'],
            ]);
            \App\Models\Players::query()->where('id', $request->player_id)->update(['status' => $request->status]);
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

    public function add_player_args(){

        $rand = ['Casino', 'Bet', 'Kingdom', 'Fan', 'Slots', 'Vegas', 'Play', 'Spin', 'Joker', 'Party', 'Pay',
            'Win', 'Million', 'Cart', 'Gaming', 'Room', '777' , 'One', 'First', 'Starz', 'Instant', 'Safe', 'Energy',
            'Match', '1x', 'Heroes'];

        $id = \App\Models\Players::query()->latest('id')
            ->first()->id;;
        $args = [];
        $i = 1;
        while ($i <= $id){
            $args[] = [
                'player_id' => $i,
                'ga' => $this->get_ga(),
                'utm_source' => $rand[rand(0, count($rand)-1)],
                'utm_medium' => 'cpc',
                'utm_content' => 'creative',
                'utm_campaign' => 'network',
                'utm_term' => 'keyword',
                's_tag_affiliate' => '',
                'btag' => $rand[rand(0, count($rand)-1)] . ' Group',
                'btag_net_refer' => rand(10000, 99999),
                'qtag' => rand(10000, 99999)
            ];
            $i++;
        }

        //2341234sdaf-d234-324f-b234-23423423p
//        PlayerArgs::query()->insert($args);
//        dd(true);
    }

    public function get_ga(){
        $array = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
            's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        );
        $ga = '';
        //2341234sdaf-d234-324f-b234-23423423p
        for ($i = 0; $i < 36; $i++){
            if($i === 11){
                $ga.= '-';
            }
            if($i === 15){
                $ga.= '-';
            }
            if($i === 20){
                $ga.= '-';
            }
            if($i === 25){
                $ga.= '-';
            }
            $ga.= $array[rand(0, count($array)-1)];
        }
        return $ga;
    }
}
