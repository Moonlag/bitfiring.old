<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class PageTranslations extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $fillable = [
        'page_id',
        'language_id',
        'active',
        'description',
        'title',
        'meta_description',
        'headline'
    ];

    protected $table = "page_translations";
}
