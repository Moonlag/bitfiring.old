<?php

namespace App\Orchid\Filters;

use App\Models\Disables;
use App\Models\GroupPlayers;
use App\Models\PlayerLocks;
use App\Models\Sessions;
use App\Models\TagItem;
use App\Models\Wallets;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class GamesBetFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'created_at', 'finished', 'base_type', 'currency',
        'player', 'game_table', 'bet_sum',
        'bet_sum_value', 'payoff_sum', 'payoff_sum_value', 'issued_bonus',
        'jackpot_win', 'external', 'external_value'
    ];

    /**
     * @return string
     */
    public function name(): string
    {
        return '';
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {

        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('games_bets.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }

        if ($this->request->get('finished')) {
            if((int)$this->request->get('finished') === 1){
                $builder = $builder->whereNotNull('games_bets.finished_at');
            }
            if((int)$this->request->get('finished') === 2){
                $builder = $builder->whereNull('games_bets.finished_at');
            }
        }

        if ($this->request->get('currency')) {
            $builder = $builder->where('games_bets.currency', $this->request->get('currency'));
        }

        if ($this->request->get('player')) {
            $builder = $builder->where('games_bets.user_id', $this->request->get('player'));
        }

        if ($this->request->get('game_table')) {
            $builder = $builder->where('games_bets.game_id', $this->request->get('game_table'));
        }

        if ($this->request->get('bet_sum') && $this->request->get('bet_sum') === '1' && $this->request->get('bet_sum_value')) {
            $builder = $builder->where('games_bets.bet_sum', '=', $this->request->get('bet_sum_value'));
        }
        if ($this->request->get('bet_sum') && $this->request->get('bet_sum') === '2' && $this->request->get('bet_sum_value')) {
            $builder = $builder->where('games_bets.bet_sum', '>', $this->request->get('bet_sum_value'));
        }
        if ($this->request->get('bet_sum') && $this->request->get('bet_sum') === '3' && $this->request->get('bet_sum_value')) {
            $builder = $builder->where('games_bets.bet_sum', '<', $this->request->get('bet_sum_value'));
        }

        if ($this->request->get('payoff_sum') && $this->request->get('payoff_sum') === '1' && $this->request->get('payoff_sum_value')) {
            $builder = $builder->where('games_bets.payoffs_sum', '=', $this->request->get('payoff_sum_value'));
        }
        if ($this->request->get('payoff_sum') && $this->request->get('payoff_sum') === '2' && $this->request->get('payoff_sum_value')) {
            $builder = $builder->where('games_bets.payoffs_sum', '>', $this->request->get('payoff_sum_value'));
        }
        if ($this->request->get('payoff_sum') && $this->request->get('payoff_sum') === '3' && $this->request->get('payoff_sum_value')) {
            $builder = $builder->where('games_bets.payoffs_sum', '<', $this->request->get('payoff_sum_value'));
        }



        if ($this->request->get('issued_bonus')) {
            if((int)$this->request->get('issued_bonus') === 1){
                $builder = $builder->whereNotNull('games_bets.bonus_issue');
            }
            if((int)$this->request->get('issued_bonus') === 2){
                $builder = $builder->whereNull('games_bets.bonus_issue');
            }
        }



        if ($this->request->get('jackpot_win')) {
            if((int)$this->request->get('jackpot_win') === 1){
                $builder = $builder->whereNotNull('games_bets.jackpot_win');
            }
            if((int)$this->request->get('jackpot_win') === 2){
                $builder = $builder->whereNull('games_bets.jackpot_win');
            }
        }

        if ($this->request->get('external') && $this->request->get('external') === '1' && $this->request->get('external_value')) {
            $builder = $builder->where('games_bets.tx_id', 'LIKE', "%{$this->request->get('external_value')}%");
        }
        if ($this->request->get('external') && $this->request->get('external') === '2' && $this->request->get('external_value')) {
            $builder = $builder->where('games_bets.tx_id', $this->request->get('external_value'));
        }
        if ($this->request->get('external') && $this->request->get('external') === '3' && $this->request->get('external_value')) {
            $builder = $builder->where('games_bets.tx_id', 'LIKE', "{$this->request->get('external_value')}%");
        }
        if ($this->request->get('external') && $this->request->get('external') === '4' && $this->request->get('external_value')) {
            $builder = $builder->where('games_bets.tx_id', 'LIKE', "%{$this->request->get('external_value')}");
        }

        return $builder;
    }

    /**
     * @return Field[]
     */
    public function display(): array
    {
        //
    }
}
