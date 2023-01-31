<?php

namespace App\Orchid\Screens\Games;

use App\Models\Currency;
use App\Models\GamesBets;
use App\Models\Players;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\TD;

class ViewBet extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'ViewBet';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'ViewBet';

    public $permission = [
        'platform.games.bet.view'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(GamesBets $model, Request $request): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $user = $request->user();
            $game = \App\Models\Games::query()->where('id', $model->game_id)->first();
            $player = \App\Models\Players::query()->where('id', $model->user_id)->first();
            $currency = Currency::query()->where('id', $model->currency)->first();
            $this->name = $game->name;
            $this->description = $player->email;
        }

        $transaction = $model->transaction()->orderBy('id', 'DESC')->paginate(100);

        return [
            'info' => [
                'title' => 'Bet Details',
                'table' => [
                    'Game Table' => $game->name ? Link::make($game->name)->route('platform.games.view', $game->id)
                        ->class('link-primary') : '-',
                    'Bonus Issue' => '-',
                    'Created at' => $model->created_at ?? '-',
                    'Finished at' => $model->finished_at ?? '-',
                    'Player' => $player->email ? Link::make($player->email)
                        ->route('platform.players.profile', $player->id)
                        ->class('link-primary') : '-',
                    'Currency' => $currency->code ?? '-',
                    'Balance Before Money' => $model->balance_before ?? '-',
                    'Balance After Money' => $model->balance_after ?? '-',
                    'Total Bets Money' => $model->bet_sum ?? '-',
                    'Total Wins Money' => $model->profit ?? '-',
                    'External' => $model->tx_id ?? '-',
//                    'Details' => 'Load details'
                ],
            ],
            'transactions' => $transaction
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
                ->route('platform.games.bets'),
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
            Layout::table('transactions', [
                TD::make('id', 'ID')->render(function (\App\Models\Transactions $model){
                    return $model->id;
                })
                    ->sort(),
                TD::make('type', 'Type')->render(function (\App\Models\Transactions $model){
                    return \App\Models\Transactions::TYPE_GAME[$model->type_id];
                })->sort(),
                TD::make('amount', 'Amount')->sort(),
                TD::make('created_at', 'Created at')->render(function (\App\Models\Transactions $model) {
                    return $model->created_at ?? '-';
                })->sort(),
            ]),
        ];
    }
}
