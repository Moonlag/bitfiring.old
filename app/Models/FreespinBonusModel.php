<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class FreespinBonusModel extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "freespin_bonus";

    public function bonus_games(){
        return $this->belongsToMany(Games::class, 'freespin_bonus_games','fb_id', 'game_id');
    }

    public function game_provider(){
        return $this->hasOne(GamesProvider::class, 'id', 'provider_id');
    }

}
