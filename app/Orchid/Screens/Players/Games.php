<?php

namespace App\Orchid\Screens\Players;

use App\Models\GamesBets;
use App\Models\GamesStats;
use App\Models\Suspicions;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Games extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Games';

    protected $id;

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Games';


    public $permission = [
        'platform.players.games'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
        }
        $games = GamesBets::where('games_bets.user_id', '=', $model->id)
            ->leftJoin('games', 'games_bets.game_id', '=', 'games.id')
            ->leftJoin('wallets', 'games_bets.wallet_id', '=', 'wallets.id')
            ->leftJoin('currency', 'wallets.currency_id', '=', 'currency.id')
            ->select('currency.code as currency', 'games.name as game', 'games.provider',
                DB::raw(
                    'SUM(games_bets.bet_sum) as total_loss,
                     abs(SUM( ( CASE WHEN games_bets.profit < 0 THEN games_bets.profit END ) )) as total_winnings,
                    (SUM(games_bets.bet_sum) - abs(SUM( ( CASE WHEN games_bets.profit < 0 THEN games_bets.profit END ) ))) as profit,
                    (abs(SUM( ( CASE WHEN games_bets.profit < 0 THEN games_bets.profit END )) ) / SUM(games_bets.bet_sum)) * 100 as payout'
                ),
            )
            ->groupBy('games_bets.game_id')
            ->paginate();

        return [
            'games' => $games
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
            Layout::table('games', [
                TD::make('game', 'Games')->sort(),
                TD::make('provider', 'Provider')->sort(),
                TD::make('currency', 'Currency')->sort(),
                TD::make('total_winnings', 'Total Winnings')->render(function (GamesBets $model){
                    return $model->total_winnings ?? '0.00';
                })->sort(),
                TD::make('total_loss', 'Total loss')->sort(),
                TD::make('profit', 'Profit')->render(function (GamesBets $model){
                    return $model->profit ?? '0.00';
                })->sort(),
                TD::make('payout', 'Payout')->render(function (GamesBets $model){
                    $p = usdt_helper($model->payout, 'USDT');
                    $amount = $model->profit < 0 ? -$p : $p;
                    $class = $model->profit < 0 ? 'bg-danger' : ($model->profit > 0 ? 'bg-success' : 'bg-secondary');
                    return "<span class='rounded px-2 $class'>$amount%</span>";
                })->sort(),
                TD::make('action', '')->render(function () {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Button::make(__('view'))->class('dropdown-item disabled')
                                ->confirm(__('Are you sure you want to change status state?')),
                        ]);
                })
            ]),
        ];
    }
}
