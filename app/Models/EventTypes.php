<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class EventTypes extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "event_types";

    public function groups(){
        return $this->belongsToMany(Groups::class, 'group_events', 'event_id', 'group_id');
    }
}
