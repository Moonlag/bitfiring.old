<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Suspicions extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "suspicions";

    protected $fillable = [
        'user_id', 'reason_id', 'active',
        'created_at', 'updated_at',
    ];

    protected $allowedSorts = [
        'email', 'suspicion_name',
        'created_at', 'updated_at',
    ];
}
