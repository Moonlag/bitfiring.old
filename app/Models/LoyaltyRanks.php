<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class LoyaltyRanks extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "loyalty_ranks";

    protected $fillable = [
        'player_id', 'rank_id', 'exp'
    ];

    public function rank(){
        return $this->belongsTo(Ranks::class, 'rank_id', 'id');
    }
}
