<?php

namespace App\Orchid\Screens\Players;

use App\Models\GamesBets;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Bets extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Bets';
    public $id;

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Bets';


    public $permission = [
        'platform.players'
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
            $this->id = $model->id;
            $this->description = 'id: ' . $model->id;
        }
        $games = GamesBets::where('games_bets.user_id', '=', $model->id)
            ->leftJoin('games', 'games_bets.game_id', '=', 'games.id')
            ->leftJoin('wallets', 'games_bets.wallet_id', '=', 'wallets.id')
            ->leftJoin('currency', 'wallets.currency_id', '=', 'currency.id')
            ->select('games_bets.id', 'games.name as title', 'currency.code as currency', 'games_bets.balance_before',
                'games_bets.balance_after', 'games_bets.bet_sum', 'games_bets.payoffs_sum', 'games_bets.profit',
                'games_bets.bonus_issue', 'games_bets.jackpot_win', 'games_bets.created_at', 'games_bets.finished_at')
            ->orderBy('games_bets.id', 'DESC')
            ->paginate(100);
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
                TD::make('title', 'Games')->sort(),
                TD::make('currency', 'Currency')->sort(),
                TD::make('balance_before', 'Balance before')->sort(),
                TD::make('balance_after', 'Balance after')->sort(),
                TD::make('bet_sum', 'Bets sum')->sort(),
                TD::make('payoffs_sum', 'Payoffs sum')->sort(),
                TD::make('profit', 'Profit')->sort(),
                TD::make('bonus_issue', 'Bonus issue')->sort(),
                TD::make('jackpot_win', 'Jackpot win')->sort(),
                TD::make('player', 'Player')->render(function (){
                    return Link::make($this->name)->class('link-primary')
                        ->route('platform.players.profile', $this->id);
                })->sort(),
                TD::make('created_at', 'Created at')
                    ->render(function (GamesBets $model) {
                        return $model->created_at;
                    })->sort(),
                TD::make('finished_at', 'Finished at')
                    ->render(function (GamesBets $model) {
                        return $model->finished_at;
                    })->sort(),
                TD::make('action', '')->render(function (GamesBets $model) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Link::make('View')
                                ->class('dropdown-item')
                                ->route('platform.games.bet.view', $model->id),
                        ]);
                })
            ]),
        ];
    }
}
