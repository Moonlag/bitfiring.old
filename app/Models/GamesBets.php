<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class GamesBets extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "games_bets";

    public function player (){
        return $this->belongsTo(Players::class, 'user_id', 'id');
    }

    public function wallets (){
        return $this->belongsTo(Wallets::class, 'wallet_id', 'id');
    }

    public function games (){
        return $this->belongsTo(Games::class, 'game_id', 'id');
    }

    public function transaction(){
        return $this->belongsTo(Transactions::class, 'id', 'reference_id')->where('reference_type_id', 4);
    }

    public const STATUS = [
        0 => 'NOT FINISHED',
        1 => 'PLACED',
        2 => 'REFUND',
        3 => 'WIN',
        4 => 'CANCELED',
        5 => 'FORCED FINISH',
    ];
}
