<?php

namespace App\Orchid\Screens\Payments;

use App\Models\BtcAddress;
use App\Models\Comments;
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

class BitcoinView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'BitcoinView';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'BitcoinView';

    public $section_id = 3;

    public $permission = [
        'platform.payments.btc_address.view'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(BtcAddress $model, Request $request): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $user = $request->user();
            $player = Players::query()->where('id', $model->user_id)->select('email', 'id')->first();
            $this->name = $model->address;
            $this->description = $player->email;
        }

        $comments = Comments::query()
            ->where('user_id', $model->id)
            ->where('section_id', $this->section_id)
            ->select('comments.id', 'users.name', 'users.email', 'comments.comment', 'comments.created_at')
            ->leftJoin('users', 'comments.staff_id', '=', 'users.id')
            ->orderBy('id', 'DESC')
            ->get();

        return [
            'info' => [
                'title' => 'Bitcoin Address Details',
                'table' => [
                    'Address' => $model->address,
                    'Player' => Link::make($player->email)->route('platform.players.profile', $player->id)->class('link-primary') ?? '-',
                    'Amount' => '0 BTC',
                    'Address Source' => $model->address_source,
                    'Created at' => $model->created_at,
                    'Updated at' => $model->updated_at,
                ]
            ],
            'comments' => [
                'section' => [
                    'title' => 'Comments(' . count($comments) .')',
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
                ->icon('left')
                ->class('btn btn-outline-secondary mb-2')
                ->route('platform.payments.btc_address'),
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
            return redirect()->route('platform.payments.btc_address.view', $request->item_id);
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
        }
    }
}
