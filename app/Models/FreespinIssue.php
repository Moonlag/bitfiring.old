<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class FreespinIssue extends Model
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "freespin_issue";

    public $fillable = [
        'title',
        'player_id',
        'currency_id',
        'bonus_id',
        'count',
        'win',
        'issue_code',
        'wallet_id',
        'stage',
        'status',
        'active_until'
    ];

    public function freespin_bonus(){
        return $this->hasOne(FreespinBonusModel::class, 'id', 'bonus_id');
    }
}
