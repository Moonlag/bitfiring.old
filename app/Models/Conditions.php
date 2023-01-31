<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Conditions extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "conditions";

    public const CONDITIONS = [
        51 => 'user_duplicate',
        20 => 'deposit_payment_systems',
        24 => 'disposable_email',
    ];

}
