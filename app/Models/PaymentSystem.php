<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Illuminate\Support\Carbon;

class PaymentSystem extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = 'payment_system';

    public function deposits(){
        $palyers = Players::query()
            ->leftJoin('group_players', 'players.id', '=', 'group_players.user_id')
            ->whereIn('group_players.group_id', [18, 21, 16])
            ->select('players.id')
            ->get();

        return $this->hasMany(Payments::class, 'payment_system_id', 'id')
            ->join('players', function ($join){
                $join->on('payments.user_id', '=', 'players.id');
            })
            ->whereNotIn('payments.user_id', $palyers->pluck('id'))
            ->where('payments.type_id', 3)
            ->where('payments.status', 1)
            ->where('payments.created_at', '>', Carbon::parse('2021-10-01 00:00:00'))
            ->select('payments.amount')
            ->groupBy('payments.id');
    }

}
