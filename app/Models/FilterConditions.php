<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class FilterConditions extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "filter_conditions";

    public function condition(){
        return $this->belongsTo(Conditions::class, 'condition_id', 'id');
    }
}
