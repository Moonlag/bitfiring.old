<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Partners extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "partners";

    protected $fillable = [
        'email',
        'fullname',
        'partner_id',
        'created_at',
    ];

}
