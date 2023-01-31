<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class PaymentSystemsSorting extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "payment_systems_sorting";
}
