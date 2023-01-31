<?php

namespace App\Orchid\Filters;

use App\Models\Disables;
use App\Models\Sessions;
use App\Models\TagItem;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class PlayersBetsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'created_at', 'finished', 'provider', 'currency',
        'user_id', 'game_table', 'bets_sum',
        'bets_sum_value', 'payoffs_sum', 'payoffs_sum_value', 'confirmed',
        'sign_up', 'locked_at', 'current_sign_in_ip', 'issued_bonus',
        'jackpot_win', 'external', 'external_value',
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
            if($this->request->get('finished') === '2'){
                $builder = $builder->whereNull('games_bets.finished_at');
            }
            if($this->request->get('finished') === '1'){
                $builder = $builder->whereNotNull('games_bets.finished_at');
            }
        }

        if ($this->request->get('currency')) {
            $builder = $builder->where('games_bets.currency', $this->request->get('currency'));
        }

        if ($this->request->get('user_id')) {
            $builder = $builder->where('games_bets.user_id', $this->request->get('user_id'));
        }

        if ($this->request->get('bets_sum') && $this->request->get('bets_sum') === '1' && $this->request->get('bets_sum_value')) {
            $builder = $builder->where('games_bets.bets_sum', '=', $this->request->get('bets_sum_value'));
        }

        if ($this->request->get('bets_sum') && $this->request->get('bets_sum') === '2' && $this->request->get('bets_sum_value')) {
            $builder = $builder->where('games_bets.bets_sum', '<', $this->request->get('bets_sum_value'));
        }

        if ($this->request->get('bets_sum') && $this->request->get('bets_sum') === '3' && $this->request->get('bets_sum_value')) {
            $builder = $builder->where('games_bets.bets_sum', '>', $this->request->get('bets_sum_value'));
        }

        if ($this->request->get('payoffs_sum') && $this->request->get('payoffs_sum') === '1' && $this->request->get('payoffs_sum_value')) {
            $builder = $builder->where('games_bets.payoffs_sum', '=', $this->request->get('payoffs_sum_value'));
        }

        if ($this->request->get('payoffs_sum') && $this->request->get('payoffs_sum') === '2' && $this->request->get('payoffs_sum_value')) {
            $builder = $builder->where('games_bets.payoffs_sum', '<', $this->request->get('payoffs_sum_value'));
        }

        if ($this->request->get('payoffs_sum') && $this->request->get('payoffs_sum') === '3' && $this->request->get('payoffs_sum_value')) {
            $builder = $builder->where('games_bets.payoffs_sum', '>', $this->request->get('payoffs_sum_value'));
        }

        if ($this->request->get('issued_bonus')) {
            switch ($this->request->get('issued_bonus')) {
                case 'no':
                    $boolean = 0;
                    break;
                case 'yes':
                    $boolean = 1;
                    break;
            }
            if (isset($boolean)) {
                $builder = $builder->where('games_bets.bonus_issue', $boolean);
            }

        }

        if ($this->request->get('jackpot_win')) {
            if($this->request->get('jackpot_win') === '2'){
                $builder = $builder->whereNull('games_bets.jackpot_win');
            }
            if($this->request->get('jackpot_win') === '1'){
                $builder = $builder->whereNotNull('games_bets.jackpot_win');
            }
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
