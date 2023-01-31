<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Transactions extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "transactions";

    protected $fillable = [
        'amount',
        'bonus_part',
        'currency_id',
        'reference_id',
        'wallet_id',
        'player_id',
        'type_id',
        'reference_type_id',
    ];

    public const SWAP = [
        1 => 'From',
        2 => 'To'
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

    public const TYPE_GAME = [
        1 => 'Placed',
        2 => 'Rollback',
        3 => 'WIN'
    ];
}
