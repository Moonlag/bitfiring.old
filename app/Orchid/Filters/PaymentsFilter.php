<?php

namespace App\Orchid\Filters;

use App\Models\Players;
use App\Orchid\Screens\Filters\Groups;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class PaymentsFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'payment_system', 'child_system', 'action', 'currency',
        'amount_cents', 'amount_cents_value', 'email', 'user_groups',
        'admin_user', 'payment_code', 'payment_code_value', 'finished_at',
        'created_at', 'country', 'success', 'player_id', 'partner'
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
        if ($this->request->get('email')) {
            $builder = $builder->where('payments.email', 'LIKE', "%{$this->request->get('email')}%");
        }
        if ($this->request->get('player_id')) {
            $builder = $builder->where('payments.user_id', '=', $this->request->get('player_id'));
        }
        if ($this->request->get('payment_system')) {
            $builder = $builder->where('payments.payment_system_id', $this->request->get('payment_system'));
        }
        if ($this->request->get('action')) {
            $builder = $builder->where('payments.type_id', $this->request->get('action'));
        }
        if ($this->request->get('currency')) {
            $builder = $builder->where('payments.currency_id', $this->request->get('currency'));
        }
        if ($this->request->get('status')) {
            $builder = $builder->where('payments.status', $this->request->get('status'));
        }
        if ($this->request->get('admin_user')) {
            $builder = $builder->where('payments.staff_id', $this->request->get('admin_user'));
        }
        if ($this->request->get('amount_cents') && $this->request->get('amount_cents') === '1' && $this->request->get('amount_cents_value')) {
            $builder = $builder->where('payments.amount', '=', $this->request->get('amount_cents_value'));
        }
        if ($this->request->get('amount_cents') && $this->request->get('amount_cents') === '2' && $this->request->get('amount_cents_value')) {
            $builder = $builder->where('payments.amount', '>', $this->request->get('amount_cents_value'));
        }
        if ($this->request->get('amount_cents') && $this->request->get('amount_cents') === '3' && $this->request->get('amount_cents_value')) {
            $builder = $builder->where('payments.amount', '<', $this->request->get('amount_cents_value'));
        }
        if ($this->request->get('payment_code') && $this->request->get('payment_code') === '1' && $this->request->get('payment_code_value')) {
            $builder = $builder->where('payments.payment_code', 'LIKE', "%{$this->request->get('payment_code_value')}%");
        }
        if ($this->request->get('payment_code') && $this->request->get('payment_code') === '2' && $this->request->get('payment_code_value')) {
            $builder = $builder->where('payments.payment_code', $this->request->get('payment_code_value'));
        }
        if ($this->request->get('payment_code') && $this->request->get('payment_code') === '3' && $this->request->get('payment_code_value')) {
            $builder = $builder->where('payments.payment_code', 'LIKE', "{$this->request->get('payment_code_value')}%");
        }
        if ($this->request->get('payment_code') && $this->request->get('payment_code') === '4' && $this->request->get('payment_code_value')) {
            $builder = $builder->where('payments.payment_code', 'LIKE', "%{$this->request->get('payment_code_value')}");
        }
        if ($this->request->get('finished_at') && $this->request->get('finished_at')['start'] && $this->request->get('finished_at')['end']) {
            $builder = $builder->whereBetween('payments.finished_at', [$this->request->get('finished_at')['start'], $this->request->get('finished_at')['end']]);
        }
        if ($this->request->get('created_at') && $this->request->get('created_at')['start'] && $this->request->get('created_at')['end']) {
            $builder = $builder->whereBetween('payments.created_at', [$this->request->get('created_at')['start'], $this->request->get('created_at')['end']]);
        }
        if ($this->request->get('country')) {
            $country = Players::query()->where('country', $this->request->get('country'))->select('id')->get()->toArray();
            $builder = $builder->whereIn('payments.user_id', array_column($country, 'id'));
        }
        if ($this->request->get('user_groups')) {
            $player = DB::table('group_players')->where('group_id', $this->request->get('user_groups'))->select('user_id')->get()->toArray();
            $builder = $builder->whereIn('payments.user_id', array_column($player, 'user_id'));
        }

        if ($this->request->get('partner')) {
            $partners_id = \App\Models\Partners::query()->where('fullname', 'LIKE', "%{$this->request->get('partner')}%")->orWhere('email', 'LIKE', "%{$this->request->get('partner')}%")->select('id')->pluck('id');
            $players_id = \App\Models\Players::query()->where('players.partner_id', $partners_id)->select('id')->pluck('id');
            $builder = $builder->whereIn('payments.user_id', $players_id);
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
