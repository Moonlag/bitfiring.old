<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class MailStatistics extends Model
{
    use AsSource, Attachable, Filterable;

    protected $table = "mail_statistics";

    protected $fillable = ['email', 'mail_text_id', 'id'];

    protected $allowedSorts = [
        'email', 'mail_text_id',
        'created_at', 'id'
    ];
}
