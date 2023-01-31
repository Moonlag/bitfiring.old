<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use App\Models\Players;
class Bonuses extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;
    protected $table = "bonuses";

    protected $fillable = [
        'title',
        'title_frontend',
        'description',
        'mini_description',
        'code' ,
        'currency_id',
        'status',
        'percentage',
        'amount',
        'wager',
        'cashable',
        'duration',
        'duration_type',
        'min',
        'max',
        'idx',
        'fb_id',
        'strategy_id',
        'type_id',
        'image',
        'active_until',
        'once_per_day',
        'created_at',
    ];

    protected $allowedSorts = [];

    public const DURATION = [1 => 'hour', 2 => 'day', 3 => 'week', 4 => 'month', 5 => 'year'];
    
    public function hour($limit){
        return Carbon::now()->addHours($limit);
    }

    public function day($limit){
        return Carbon::now()->addDays($limit);
    }

    public function week($limit){
        return Carbon::now()->addWeeks($limit);
    }

    public function month($limit){
        return Carbon::now()->addMonths($limit);
    }

    public function year($limit){
        return Carbon::now()->addYears($limit);
    }

    public function freespin(){
        return $this->hasOne(FreespinBonusModel::class, 'id', 'freespin_id');
    }
}
