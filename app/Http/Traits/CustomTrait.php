<?php

namespace App\Http\Traits;

use App\Events\UpdateBalance;
use App\Events\UpdateWallets;
use App\Models\FreespinBonusModel;
use App\Models\Wallets;
use App\Models\WalletsTemp;
use Illuminate\Support\Facades\Http;
use Carbon\Exceptions\InvalidFormatException;
use DB;
use Eloquent;
use Carbon\Carbon;

trait CustomTrait
{

    public function get_specific_games()
    {
        return DB::table('games')
            ->select('games.name as Name', 'games.img as Image')
			->where('games.provider_id', '=', 45)
            ->get();
    }
	
    public function get_specific_bets()
    {
        return DB::table('games_bets')
            ->select('players.email', 'games_bets.user_id as player_id', 'games_bets.payoffs_sum', 'games_bets.bet_sum', 'partners.email as partner_email', 'profit', 'games_bets.created_at as bet_created')
            ->join('players', 'players.id', '=', 'games_bets.user_id')
            ->join('partners', 'partners.id', '=', 'players.partner_id')
            ->whereIn('players.partner_id', [17, 11, 6, 5])
			->where('games_bets.created_at', '>', '2022-07-01 00:00:00')
			->where('games_bets.created_at', '<', '2022-08-01 00:00:00')
            ->get();
    }
	
    public function get_specific_bonuses()
    {
        return DB::table('bonus_issue')
            ->select('players.email', 'bonus_issue.user_id as player_id', 'bonus_issue.amount', 'bonus_issue.created_at as bonus_created')
            ->join('players', 'players.id', '=', 'bonus_issue.user_id')
            ->join('partners', 'partners.id', '=', 'players.partner_id')
            ->whereIn('players.partner_id', [17, 11, 6, 5])
			//->where('games_bets.created_at', '>', '2022-07-01 00:00:00')
			//->where('games_bets.created_at', '<', '2022-08-01 00:00:00')
            ->get();
    }
	
    public function get_specific_payments()
    {
        return DB::table('payments')
            ->select('players.email', 'payments.user_id as player_id', 'payments.amount', 'payments.amount_usd', 'payments.created_at as payment_created')
            ->join('players', 'players.id', '=', 'payments.user_id')
            ->join('partners', 'partners.id', '=', 'players.partner_id')
            ->whereIn('players.partner_id', [17, 11, 6, 5])
            ->where('payments.amount', '>', 0)
			->where('payments.created_at', '>', '2022-07-01 00:00:00')
			->where('payments.created_at', '<', '2022-08-01 00:00:00')
            ->get();
    }


}
