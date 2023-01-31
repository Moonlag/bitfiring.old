<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Games extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "games";


    protected $allowedSorts = [
        'title', 'provider',
        'producer', 'category',
        'identifier', 'devices',
        'fs', 'jp', 'balance',
    ];

    public $DEVICES = [
        1 => 'DM',
        2 => 'D',
        3 => 'M',
    ];


    public function currency(){
        return $this->belongsToMany(Currency::class, 'game_currency','game_id', 'currency_id');
    }

    public function category (){
        return $this->belongsToMany(GamesCats::class, 'game_category_binds', 'game_id', 'category_id')->withPivot('weight')->orderBy('game_category_binds.weight');
    }

    public function sorting (){
        return $this->belongsTo(GamesSorting::class, 'game_id', 'id');
    }

    public function provider (){
        return $this->belongsTo(GamesProvider::class, 'provider_id', 'id');
    }

    public function users (){
        return $this->belongsTo(GameSessions::class, 'id', 'game_id');
    }
}
