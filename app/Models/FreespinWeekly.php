<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class FreespinWeekly extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "freespin_weekly";

    protected $fillable = [
        'created_at', 'updated_at', 'player_id', 'freespin_id', 'parsed'
    ];
}
