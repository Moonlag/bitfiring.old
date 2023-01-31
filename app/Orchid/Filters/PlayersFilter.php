<?php

namespace App\Orchid\Filters;

use App\Models\Disables;
use App\Models\GroupPlayers;
use App\Models\Payments;
use App\Models\PlayerLocks;
use App\Models\Sessions;
use App\Models\TagItem;
use App\Models\Wallets;
use App\Models\Partners;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class PlayersFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'email_contains', 'phone', 'phone_value', 'tags',
        'currency', 'firstname', 'firstname_value',
        'lastname', 'lastname_value', 'country', 'confirmed',
        'sign_up', 'locked_at', 'current_sign_in_ip', 'ip',
        'receive_email_promos', 'receive_sms_promos', 'state',
        'disable_reason', 'invite_aff', 'groups', 'language',
        'scope', 'partner'
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
        if ($this->request->get('email_contains')) {
            $builder = $builder->where('players.email', 'LIKE', "%{$this->request->get('email_contains')}%");
        }
        if ($this->request->get('partner')) {
            $partners_id = Partners::query()->where('fullname', 'LIKE', "%{$this->request->get('partner')}%")->orWhere('email', 'LIKE', "%{$this->request->get('partner')}%")->select('id')->pluck('id');
            $builder = $builder->whereIn('players.partner_id', $partners_id);
        }
        if ($this->request->get('phone') && $this->request->get('phone') === '1' && $this->request->get('phone_value')) {
            $builder = $builder->where('players.phone', 'LIKE', "%{$this->request->get('phone_value')}%");
        }
        if ($this->request->get('phone') && $this->request->get('phone') === '2' && $this->request->get('phone_value')) {
            $builder = $builder->where('players.phone', $this->request->get('phone_value'));
        }
        if ($this->request->get('phone') && $this->request->get('phone') === '3' && $this->request->get('phone_value')) {
            $builder = $builder->where('players.phone', 'LIKE', "{$this->request->get('phone_value')}%");
        }
        if ($this->request->get('phone') && $this->request->get('phone') === '4' && $this->request->get('phone_value')) {
            $builder = $builder->where('players.phone', 'LIKE', "%{$this->request->get('phone_value')}");
        }
        if ($this->request->get('tags')) {
            $player_id = TagItem::query()->whereIn('tag_id', $this->request->get('tags'))->select('item_id as id')->get()->toArray();
            $builder = $builder->whereIn('players.id', array_column($player_id, 'id'));
        }
        if ($this->request->get('groups')) {
            $player_id = GroupPlayers::query()->whereIn('group_id', $this->request->get('groups'))->select('user_id as id')->get()->toArray();
            $builder = $builder->whereIn('players.id', array_column($player_id, 'id'));
        }
        if ($this->request->get('scope')) {
            if($this->request->get('scope') === '1'){
                $now = Carbon::now()->subHour(3)->format('Y-m-d H:i:S');
                $player_id = Sessions::query()
                    ->where('sessions.created_at', '>=', $now)
                    ->leftJoin('players', 'sessions.user_id', '=', 'players.id')
                    ->whereNull('players.deleted_at')
                    ->select('players.id')
                    ->get()->pluck('id');
            }else if($this->request->get('scope') === '2'){
                $player_id = Payments::query()
                    ->where([['payments.type_id', '=', 3], ['payments.status', '=', 1]])
                    ->rightJoin('players', 'payments.user_id', '=', 'players.id')
                    ->whereNull('players.deleted_at')
                    ->select('players.id')
                    ->get()
                    ->pluck('id');
            }else{
                $player_id = null;
            }
            if(!empty($player_id)){
                $builder = $builder->whereIn('players.id', $player_id);
            }
        }
        if ($this->request->get('currency')) {
            $builder = $builder->where('players.currency_id', $this->request->get('currency'));
        }
        if ($this->request->get('lastname') && $this->request->get('lastname') === '1' && $this->request->get('lastname_value')) {
            $builder = $builder->where('players.lastname', 'LIKE', "%{$this->request->get('lastname_value')}%");
        }
        if ($this->request->get('lastname') && $this->request->get('lastname') === '2' && $this->request->get('lastname_value')) {
            $builder = $builder->where('players.lastname', $this->request->get('lastname_value'));
        }
        if ($this->request->get('lastname') && $this->request->get('lastname') === '3' && $this->request->get('lastname_value')) {
            $builder = $builder->where('players.lastname', 'LIKE', "{$this->request->get('lastname_value')}%");
        }
        if ($this->request->get('lastname') && $this->request->get('lastname') === '4' && $this->request->get('lastname_value')) {
            $builder = $builder->where('players.lastname', 'LIKE', "%{$this->request->get('lastname_value')}");
        }
        if ($this->request->get('firstname') && $this->request->get('firstname') === '1' && $this->request->get('firstname_value')) {
            $builder = $builder->where('players.firstname', 'LIKE', "%{$this->request->get('firstname_value')}%");
        }
        if ($this->request->get('firstname') && $this->request->get('firstname') === '2' && $this->request->get('firstname_value')) {
            $builder = $builder->where('players.firstname', $this->request->get('firstname_value'));
        }
        if ($this->request->get('firstname') && $this->request->get('firstname') === '3' && $this->request->get('firstname_value')) {
            $builder = $builder->where('players.firstname', 'LIKE', "{$this->request->get('firstname_value')}%");
        }
        if ($this->request->get('firstname') && $this->request->get('firstname') === '4' && $this->request->get('firstname_value')) {
            $builder = $builder->where('players.firstname', 'LIKE', "%{$this->request->get('firstname_value')}");
        }
        if ($this->request->get('country')) {
            $builder = $builder->where('players.country_id', $this->request->get('country'));
        }
        if ($this->request->get('language')) {
            $builder = $builder->where('players.language_id', $this->request->get('language'));
        }
        if ($this->request->get('confirmed')) {
            switch ($this->request->get('confirmed')) {
                case 'no':
                    $confirmed = 0;
                    break;
                case 'yes':
                    $confirmed = 1;
                    break;
            }
            if (isset($confirmed)) {
                $builder = $builder->where('players.status', $confirmed);
            }

        }
        if ($this->request->get('sign_up') && $this->request->input('sign_up.start') && $this->request->input('sign_up.end')) {
            $builder = $builder->whereBetween('players.created_at', [$this->request->input('sign_up.start'), $this->request->input('sign_up.end')]);
        }
        if ($this->request->get('locked_at') && $this->request->input('locked_at.start') && $this->request->input('locked_at.end')) {
            $player_id = Disables::query()->whereBetween('created_at', [$this->request->input('locked_at.start'), $this->request->input('locked_at.end')])->select('user_id')->get()->toArray();
            $builder = $builder->whereIn('players.id', array_column($player_id, 'user_id'));
        }
        if ($this->request->get('ip')) {
            $player_id = Sessions::query()->where('ip', $this->request->get('ip'))->select('user_id')->get()->toArray();
            $builder = $builder->whereIn('players.id', array_column($player_id, 'user_id'));
        }
        if ($this->request->get('current_sign_in_ip')) {
            $builder = $builder->where('players.ip', $this->request->get('current_sign_in_ip'));
        }
        if ($this->request->get('receive_email_promos')) {
            switch ($this->request->get('receive_email_promos')) {
                case 'no':
                    $receive_email_promos = 0;
                    break;
                case 'yes':
                    $receive_email_promos = 1;
                    break;
            }
            if (isset($receive_email_promos)) {
                $builder = $builder->where('players.promo_email', $receive_email_promos);
            }

        }
        if ($this->request->get('receive_sms_promos')) {
            switch ($this->request->get('receive_sms_promos')) {
                case 'no':
                    $receive_sms_promos = 0;
                    break;
                case 'yes':
                    $receive_sms_promos = 1;
                    break;
            }
            if (isset($receive_sms_promos)) {
                $builder = $builder->where('players.promo_sms', $receive_sms_promos);
            }
        }
        if ($this->request->get('disable_reason')) {
            $player_id = PlayerLocks::query()->where('reason_id', $this->request->get('disable_reason'))->select('player_id as id')->get()->toArray();
            $builder = $builder->whereIn('players.id', array_column($player_id, 'id'));
        }
        if ($this->request->get('invited_affiliate')) {
            switch ($this->request->get('invited_affiliate')) {
                case '1':
                    $invited_affiliate = 0;
                    break;
                case '2':
                    $invited_affiliate = 1;
                    break;
            }
            if(isset($invited_affiliate)){
                $builder = $builder->whereIn('players.invite_aff', $invited_affiliate);
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
