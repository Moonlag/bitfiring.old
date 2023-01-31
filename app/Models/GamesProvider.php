<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class GamesProvider extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "game_provider";

    public function currency(){
        return $this->belongsToMany(Currency::class, 'provider_currency_map', 'provider_id', 'currency_id')
            ->select('provider_currency_map.denomination', 'currency.code');
    }

    public function games(){
        return $this->hasMany(Games::class, 'provider_id', 'id');
    }

    public function bets(){
        return $this->hasManyThrough(GamesBets::class, Games::class,'provider_id', 'game_id', 'id', 'id')->where('status', '!=', 2);
    }
}
