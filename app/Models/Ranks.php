<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Ranks extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "ranks";

    public function loyalty_rank(){
        return $this->hasOne(LoyaltyRanks::class, 'rank_id', 'id');
    }
}
