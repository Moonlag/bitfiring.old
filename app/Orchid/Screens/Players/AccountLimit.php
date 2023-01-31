<?php

namespace App\Orchid\Screens\Players;

use App\Models\Limits;
use App\Models\LimitLink;
use App\Orchid\Layouts\AccountLimitRow;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use function Symfony\Component\Translation\t;

class AccountLimit extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'AccountLimit';

    protected $id;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'AccountLimit';
    /**
     * Query data.
     *
     * @return array
     */
    public $request;

    public $permission = [
        'platform.players.limits'
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query(\App\Models\Players $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
        }
        $limits = Limits::query()
            ->leftJoin('limit_types', 'limits.type_id', '=', 'limit_types.type_id')
            ->leftJoin('limit_duration', 'limits.period_id', '=', 'limit_duration.period_id')
            ->select('limits.id', 'limits.status', 'limits.account_limits', 'limits.period_id', 'limits.amount',
                'limits.current_values', 'limits.confirm_until', 'limits.created_at', 'limits.disabled_at', 'limit_duration.period_name', 'limit_types.type_name')
            ->where('limits.user_id', $model->id)
            ->orderBy('limits.id', 'DESC')
            ->paginate(10);

        $limit = LimitLink::leftJoin('limit_duration', 'limit_link.limit_duration_id', '=', 'limit_duration.period_id')
            ->select('limit_link.limit_type_id', 'limit_duration.period_name', 'limit_duration.id')
            ->get()->toArray();

        return [
            'limit' => $limits,
            'column' => [
                [
                    'title' => 'Add cooling off period',
                    'content' => [
                        Select::make('cool')
                            ->options($this->limits($limit, 1))
                            ->title('Period'),
                    ],
                    'action' => Button::make('Add limit')
                        ->class('btn btn-outline-primary')
                        ->method('created_limit')
                        ->parameters([
                            'id' => $model->id,
                            'type_id' => 1,
                        ])
                ],
                [
                    'title' => 'Add self exclusion period',
                    'content' => [
                        Select::make('exclusion')
                            ->title('Period')
                            ->options($this->limits($limit, 2)),
                    ],
                    'action' =>
                        Button::make('Add limit')
                            ->class('btn btn-outline-primary')
                            ->method('created_limit')
                            ->parameters([
                                'id' => $model->id,
                                'type_id' => 2,
                            ]),
                ],
                [
                    'title' => 'Add session limit',
                    'content' => [
                        Input::make('session_amount')
                            ->type('text')
                            ->title('Minutes'),

                    ],
                    'action' => Button::make('Add limit')
                        ->class('btn btn-outline-primary')
                        ->method('created_limit')
                        ->parameters([
                            'id' => $model->id,
                            'type_id' => 3
                        ]),
                ],
                [
                    'title' => 'Add Loss limit',
                    'content' => [
                        Select::make('loss')
                            ->title('Period')
                            ->options($this->limits($limit, 4)),
                        Input::make('loss_amount')
                            ->type('number')
                            ->title('USDT'),

                    ],
                    'action' => Button::make('Add limit')
                        ->class('btn btn-outline-primary')
                        ->method('created_limit')
                        ->parameters([
                            'id' => $model->id,
                            'type_id' => 4
                        ]),
                ],
                [
                    'title' => 'Add wager limit',
                    'content' => [
                        Select::make('wager')
                            ->title('Period')
                            ->options($this->limits($limit, 5)),
                        Input::make('wager_amount')
                            ->type('number')
                            ->title('USDT'),

                    ],
                    'action' => Button::make('Add limit')
                        ->class('btn btn-outline-primary')
                        ->method('created_limit')
                        ->parameters([
                            'id' => $model->id,
                            'type_id' => 5
                        ]),
                ],
                [
                    'title' => 'Add deposit limit',
                    'content' => [
                        Select::make('deposit')
                            ->title('Period')
                            ->options($this->limits($limit, 6)),
                        Input::make('deposit_amount')
                            ->type('number')
                            ->title('USDT'),

                    ],
                    'action' => Button::make('Add limit')
                        ->class('btn btn-outline-primary')
                        ->method('created_limit')
                        ->parameters([
                            'id' => $model->id,
                            'type_id' => 6
                        ]),
                ],
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
                ->route('platform.players.profile', $this->id)
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
            Layout::view('orchid.column'),
            Layout::table('limit', [
                TD::make('type_name', 'Type name')->sort(),
                TD::make('status', 'Status')
                    ->sort(),
                TD::make('period_name', 'Period')->sort(),
                TD::make('account_limits', 'Account limits')
                    ->sort(),
                TD::make('current_values', 'Current Values')->sort(),
                TD::make('amount', 'Amount')->sort(),
                TD::make('confirm_until', 'Confirm Until')->sort(),
                TD::make('created_at', 'Created at')
                    ->render(function (Limits $model) {
                        return $model->created_at;
                    })->sort(),
                TD::make('disabled_at', 'Disabled at')->sort(),
                TD::make()->render(function (Limits $model) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            ModalToggle::make('Edit')
                                ->modal('Limit correction')
                                ->modalTitle($model->type_name)
                                ->asyncParameters($model->id)
                                ->method('updated_limit')
                                ->class('dropdown-item')
                                ->icon('full-screen'),

                            Button::make('Trash')
                                ->method('delete_limit')
                                ->parameters([
                                    'limit_id' => $model->id,
                                ])
                                ->confirm("Are you want to remove Limit? ID:" . $model->id)
                                ->class('dropdown-item')
                                ->icon('trash'),
                        ])->class('btn sharp btn-primary tp-btn');
                })->align(TD::ALIGN_LEFT)->sort(),
            ]),
            Layout::modal('Limit correction', AccountLimitRow::class)->async('asyncGetData')
        ];
    }

    public function limits($query, $id): array
    {
        $filter_arr = array_filter($query, function ($k) use ($id) {
            if ($k['limit_type_id']) {
                return $k['limit_type_id'] == $id;
            }
            return $k;
        }, ARRAY_FILTER_USE_BOTH);
        return array_column($filter_arr, 'period_name', 'id');
    }

    public function created_limit(Request $request)
    {

        try {
            $args = [
                'user_id' => $request->id,
                'status' => 1,
                'staff_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:m:s'),
                'amount' => 0
            ];
            switch ($request->type_id) {
                case 1:
                    $this->validate($request, [
                        'type_id' => ['required', 'integer'],
                        'cool' => ['required', 'integer'],
                    ]);
                    $args = array_merge($args, [
                        'type_id' => $request->type_id,
                        'period_id' => $request->cool,
                    ]);
                    break;
                case 2:
                    $this->validate($request, [
                        'type_id' => ['required', 'integer'],
                        'exclusion' => ['required', 'integer'],
                    ]);
                    $args = array_merge($args, [
                        'type_id' => $request->type_id,
                        'period_id' => $request->exclusion,
                    ]);
                    break;
                case 3;
                    $this->validate($request, [
                        'type_id' => ['required', 'integer'],
                        'session_amount' => ['required', 'integer']
                    ]);
                    $args = array_merge($args, [
                        'type_id' => $request->type_id,
                    ]);
                    $args['amount'] = $request->session_amount;
                    break;
                case 4:
                    $this->validate($request, [
                        'type_id' => ['required', 'integer'],
                        'loss' => ['required', 'integer'],
                        'amount' => ['integer']
                    ]);
                    $args = array_merge($args, [
                        'type_id' => $request->type_id,
                        'period_id' => $request->loss,
                    ]);
                    $args['amount'] = $request->loss_amount;
                    break;
                case 5:
                    $this->validate($request, [
                        'type_id' => ['required', 'integer'],
                        'wager' => ['required', 'integer'],
                        'amount' => ['integer']
                    ]);
                    $args = array_merge($args, [
                        'type_id' => $request->type_id,
                        'period_id' => $request->wager,
                    ]);
                    $args['amount'] = $request->wager_amount;
                    break;
                case 6:
                    $this->validate($request, [
                        'type_id' => ['required', 'integer'],
                        'deposit' => ['required', 'integer'],
                        'amount' => ['integer']
                    ]);
                    $args = array_merge($args, [
                        'type_id' => $request->type_id,
                        'period_id' => $request->deposit,
                    ]);
                    $args['amount'] = $request->deposit_amount;
                    break;
                default:
                    Alert::warning('Oops, type id not found.');
                    return redirect()->route('platform.players.limits', $request->id);
            }
            Limits::query()->insert([
                $args
            ]);

            Toast::info('You have successfully.');
            return redirect()->route('platform.players.limits', $request->id);
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value){
                $msg .= join('', $value);
            }
            Alert::warning($msg);
            return redirect()->route('platform.players.limits', $request->id);
        }
    }

    public function delete_limit(Request $request){
        $input = $request->all();
        Limits::query()->where('id', $input['limit_id'])->delete();
        Toast::info('Delete have successfully.');
        return redirect()->back();
    }

    public function updated_limit($id, Request $request){
        $input = $request->all();
        Limits::query()->where('id', $id)->update($input['limit_update']);
        Toast::info('Updated have successfully.');
        return redirect()->back();
    }

    public function asyncGetData(Limits $limit)
    {

        return [
            'limit_update' => $limit,
        ];
    }
}
