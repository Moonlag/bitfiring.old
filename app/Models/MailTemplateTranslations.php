<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class MailTemplateTranslations extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "mail_template_translations";

    protected $fillable = ['id', 'mail_id', 'description', 'title', 'template_name', 'language_id', 'active'];
}
