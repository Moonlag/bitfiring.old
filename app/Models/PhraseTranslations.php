<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class PhraseTranslations extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = 'phrase_translations';

    protected $fillable = [
      'code', 'active', 'text', 'title', 'language_id', 'uuid'
    ];

    public function languages(){
       return $this->belongsTo(Languages::class, 'language_id', 'id');
    }
}
