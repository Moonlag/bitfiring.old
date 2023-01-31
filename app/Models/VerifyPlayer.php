<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyPlayer extends Model
{
    use HasFactory;
    protected $table = "player_verify";

    protected $fillable = [
        'token',
        'player_id'
    ];

    public function user(){
        return $this->belongsTo('App\Models\Players', 'player_id');
    }
}
