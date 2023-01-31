<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class GroupPlayers extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "group_players";

    protected $fillable = [
        'user_id',
        'group_id'
    ];

}
