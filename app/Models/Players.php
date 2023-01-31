<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use App\Models\BonusesUser;

class Players extends Authenticatable implements CanResetPassword
{
    use HasFactory, Notifiable, AsSource, Attachable, Filterable;

    protected $table = "players";
    protected $guard = 'clients';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'email',
        'created_at',
        'useragent',
        'status',
        'affiliate_aid',
        'city',
        'password',
        'country_id',
        'partner_id',
        'register_ip',
        'session_id',
        'mail_real',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    protected $allowedSorts = [
        'email', 'firstname',
        'country', 'created_at',
        'deposit_count', 'deposit_sum',
        'bonus_count',
        'ggr', 'balance',
        'status', 'mail_real'
    ];

    public function verifyPlayer()
    {
        return $this->hasOne('App\Models\VerifyPlayer', 'player_id');
    }

    public function sendPasswordResetNotification($token)
    {
        // Your your own implementation.
        $this->notify(new ResetPasswordNotification($token));
    }

    public function wallets()
    {
        return $this->hasMany(Wallets::class, 'user_id', 'id');
    }

    public function bets()
    {
        return $this->hasMany(GamesBets::class, 'user_id', 'id');
    }

    public function deposits()
    {
        return $this->hasMany(Payments::class, 'user_id', 'id')
            ->where('status', '=', 1)
            ->where('type_id', '=', 3);
    }

    public function groups()
    {
        return $this->belongsToMany(Groups::class, 'group_players', 'user_id', 'group_id');
    }

    public function active_bonus()
    {
        return $this->hasOne(BonusIssue::class, 'user_id', 'id')
            ->where("status", "=", 2)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function sessions(){
        return $this->hasMany(Sessions::class, 'user_id', 'id')->orderByDesc('id');
    }

    public function last_session(){
        return $this->hasOne(Sessions::class, 'user_id', 'id')->orderByDesc('id');
    }

    public function last_payment(){
        return $this->hasOne(Payments::class, 'user_id', 'id')->orderByDesc('id');
    }

    public function last_game_sessions(){
        return $this->hasOne(GameSessions::class, 'user_id', 'id')->orderByDesc('id');
    }

    public function countries(){
        return $this->hasOne(Countries::class, 'id', 'country_id');
    }

    public function partner(){
        return $this->hasOne(Partners::class, 'id', 'partner_id');
    }

    public function bonus_issue(){
        return $this->hasMany(BonusIssue::class, 'user_id', 'id');
    }

    public function rank(){
        return $this->belongsToMany(Ranks::class, 'loyalty_ranks', 'player_id', 'rank_id');
    }
}
