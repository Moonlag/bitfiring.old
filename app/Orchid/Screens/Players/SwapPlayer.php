<?php

namespace App\Orchid\Screens\Players;

use App\Models\GroupPlayers;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class SwapPlayer extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'SwapPlayer';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $player): array
    {

        $transactions = \App\Models\Transactions::query()
            ->where([['transactions.player_id', '=', $player->id], ['reference_type_id', '=', 9]])
            ->Join('players', 'transactions.player_id', '=', 'players.id')
            ->rightJoin('swaps', function ($join) {
                $join->on('transactions.reference_id', '=', 'swaps.id');
            })
            ->select('swaps.id', 'swaps.from_id', 'swaps.to_id', 'players.id as player_id', 'swaps.from_amount', 'swaps.to_amount', 'swaps.created_at')
            ->groupBy('swaps.id')
            ->orderBy('swaps.id', 'DESC')
            ->paginate();

        return [
            'transaction' => $transactions
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
            Layout::table('transaction', [
                TD::make('id', 'ID')->render(function (\App\Models\Transactions $model) {
                    return $model->id;
                })
                    ->sort(),
                TD::make('email', 'User')
                    ->render(function (\App\Models\Transactions $model) {
                        $player = \App\Models\Players::find($model->player_id);
                        $link = Link::make($player->email)->class('link-primary')
                            ->route('platform.players.profile', $player->id);
                        $group = '';
                        foreach ($player->groups as $kye) {
                            $color = $kye['color'];
                            $title = $kye['title'];
                            $group .= "<li style='background-color: $color;'><span style='border-color: transparent transparent transparent $color;'></span>$title</li>";
                        }
                        return "<div>$link
                                    <ul class='groups'>$group</ul>
                                </div>";
                    })->sort(),
                TD::make('from_id', 'From Currency')
                    ->render(function (\App\Models\Transactions $model){
                        $currency = \App\Models\Currency::find($model->from_id);
                        return $currency->code;
                    })
                    ->sort(),
                TD::make('from_amount', 'From Sum')
                    ->sort(),
                TD::make('to_id', 'To Currency')
                    ->render(function (\App\Models\Transactions $model){
                        $currency = \App\Models\Currency::find($model->to_id);
                        return $currency->code;
                    })
                    ->sort(),
                TD::make('to_amount', 'To Sum')->sort(),
                TD::make('status', 'Status')->render(function (\App\Models\Transactions $model) {
                    return "<span class='badge light badge-success'>Approved</span>";
                })->sort(),
                TD::make('created_at', 'Finished at')->render(function (\App\Models\Transactions $model) {
                    return $model->created_at ?? '-';
                })->sort(),
            ]),
        ];
    }
}
