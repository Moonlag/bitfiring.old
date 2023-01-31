<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Wallets extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "wallets";

    protected $fillable = [
        'user_id',
        'currency_id',
        'primary',
        'balance'
    ];

    public function currency(){
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function players(){
        return $this->belongsTo(Players::class, 'user_id', 'id');
    }
    
}
