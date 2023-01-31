<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Games extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "games";

    protected $allowedSorts = [
        'title', 'provider',
        'producer', 'category',
        'identifier', 'devices',
        'fs', 'jp', 'balance',
    ];
}
