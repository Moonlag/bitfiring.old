<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class BonusIssue extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "bonus_issue";

    protected $fillable = [
        'user_id',
        'currency_id',
        'bonus_id',
        'amount',
        'locked_amount',
        'reference_id',
        'to_wager',
        'wagered',
        'created_at',
        'status',
        'stage',
    ];

    public const STATUS = [
        1 => 'not activated',
        2 => 'active',
        3 => 'wager done',
        4 => 'lost',
        5 => 'cancel',
        6 => 'expired',
    ];
}
