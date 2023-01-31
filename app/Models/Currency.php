<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Currency extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "currency";

    public function games(){
        return $this->belongsToMany(Games::class, 'game_currency', 'currency_id', 'game_id');
    }

    public function deposits(){
        $palyers = Players::query()
            ->leftJoin('group_players', 'players.id', '=', 'group_players.user_id')
            ->whereIn('group_players.group_id', [18, 21, 16])
            ->select('players.id')
            ->get();

        return $this->hasMany(Payments::class, 'currency_id', 'id')
            ->whereNotIn('payments.user_id', $palyers->pluck('id'))
            ->where('payments.type_id', 3)
            ->where('payments.status', 1)
            ->where('payments.created_at', '>', Carbon::parse('2021-10-01 00:00:00'))
            ->select('payments.amount')
            ->groupBy('payments.id');
    }

    public function payment_system(){
        return $this->hasOne(PaymentSystem::class, 'currency_id', 'id');
    }
}
