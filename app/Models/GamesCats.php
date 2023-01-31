<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class GamesCats extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "games_cats";

    public function games(){
        return $this->belongsToMany(Games::class, 'game_category_binds', 'category_id', 'game_id');
    }

}
