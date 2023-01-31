<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Payments extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "payments";

    protected $fillable = [
        'email',
        'user_id',
        'wallet_id',
        'staff_id',
        'amount',
        'type_id',
        'status',
        'source_address',
        'payment_system_id',
        'created_at',
        'currency_id',
        'player_action',
        'network_fee',
    ];

    protected $allowedSorts = [
        'email', 'amount',
        'currency', 'player_action',
        'source', 'success',
        'comments', 'finished_at',
        'admin_id',
    ];

    public const ACTION = [
        1 => 'add',
        2 => 'subtract',
        3 => 'deposits',
        4 => 'cashouts',
        5 => 'gifts',
        6 => 'chargebacks',
        7 => 'refunds',
        8 => 'reversals',
    ];

    public const STATUS = [
        1 => 'Approved',
        2 => 'Pending',
        3 => 'Rejected',
        4 => 'Error',
        5 => 'In Progress',
    ];

    public function player(){
        return $this->belongsTo(Players::class, 'user_id', 'id');
    }

    public function payment_system(){
        return $this->hasOne(PaymentSystem::class, 'id', 'payment_system_id');
    }

    public function currency(){
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
