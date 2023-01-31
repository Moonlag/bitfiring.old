<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Comments extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "comments";
    protected $fillable = [
        'staff_id', 'user_id', 'created_at', 'section_id', 'comment'
    ];

    public function staff(){
        return $this->hasOne(User::class, 'id' , 'staff_id');
    }
}
