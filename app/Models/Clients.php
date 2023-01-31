<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Clients extends Authenticatable
{
    use Notifiable;

    protected $table = 'players';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','username','email', 'password','active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getAuthPassword()
    {
      return $this->password;
    }

    public function sendPasswordResetNotification($token)
    {
        // Your your own implementation.
        $this->notify(new ResetPasswordNotification($token));
    }

    public function verifyPlayer()
    {
        return $this->hasOne('App\Models\VerifyPlayer', 'player_id');
    }

    public function wallets()
    {
        return $this->hasMany(Wallets::class, 'user_id', 'id');
    }

    public function bets()
    {
        return $this->hasMany(GamesBets::class, 'user_id', 'id');
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

    public function bonus_issue(){
        return $this->hasMany(BonusIssue::class, 'user_id', 'id');
    }
}

