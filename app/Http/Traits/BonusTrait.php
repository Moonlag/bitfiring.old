<?php

namespace App\Http\Traits;

use App\Models\Wallets;
use DB;
use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Query\Expression;

trait BonusTrait
{

    public function update_bonus_balance($user_id, $wallet_id, $amount)
    {

        DB::table('wallets')->where('user_id', '=', $user_id)->where('id', '=', $wallet_id)->update(
            ['bonus_balance' => new Expression('bonus_balance + ' . $amount)]
        );

    }

    public function update_locked_balance($user_id, $wallet_id, $amount)
    {

        DB::table('wallets')->where('user_id', '=', $user_id)->where('id', '=', $wallet_id)->update(
            ['locked_balance' => new Expression('locked_balance + ' . $amount)]
        );

    }



    public function change_bonus_wagered_amount($bonus_id, $currency_id, $amount)
    {

		$currency_info = $this->get_single_currency('id', $currency_id);
		$converted_amount = abs($amount / $currency_info->rate);

		$this->update_bonus_wagered_amount($bonus_id, $converted_amount);

    }

    public function change_bonus_fixed_amount($bonus, $currency_id, $amount)
    {
        $currency_info = $this->get_single_currency('id', $currency_id);
        $converted_amount = $amount / $currency_info->rate;

        $bonus_amount = ($bonus->fixed_amount + $converted_amount);

        $fixed_amount = $bonus_amount <= 0 ? 0 : $bonus_amount;

        if($fixed_amount === 0) {
            $this->update_bonus_issue($bonus->id, ['status' => 4]);
        }

        $this->update_bonus_fixed_amount($bonus->id, $fixed_amount);

        return $bonus_amount * $currency_info->rate;
    }

    public function reject_games_category($game_id) {

        $session = DB::table('game_category_binds')->where([['game_id', '=', $game_id]])->get();

        if($session->count() === 0){
            return false;
        }

        return $session->where('category_id', '=', 31)->count();

    }

    public function reject_games_fs($game_id) {

        $session = DB::table('game_category_binds')->where([['game_id', '=', $game_id]])->get();

        if($session->count() === 0){
            return false;
        }

        return $session->where('category_id', '=', 31)->count();

    }

	public function update_bonus_wagered_amount($bonus_id, $converted_amount) {

        DB::table('bonus_issue')->where('id', '=', $bonus_id)->update(
            ['wagered' => new Expression('wagered + ' . $converted_amount)]
        );

	}

    public function update_bonus_fixed_amount($bonus_id, $fixed_amount) {

        DB::table('bonus_issue')->where('id', '=', $bonus_id)->update(
            ['fixed_amount' => $fixed_amount]
        );

    }

	public function update_bonus_issue($bonus_id, $params) {

        DB::table('bonus_issue')->where('id', '=', $bonus_id)->update(
            $params
        );

	}

    public function get_issued_user_bonus($by, $value, $game_id, $params=[])
    {

		if(count($params) == 0) {
			$bonus_issue = DB::table('bonus_issue')->orderBy('id','asc')->select('id', 'wagered', 'to_wager', 'locked_amount', 'fixed_amount')->where($by, '=', $value)->get();
		} else {
			$bonus_issue = DB::table('bonus_issue')
                ->orderBy('cat_type','desc')
                ->orderBy('id','desc')
                ->select('id', 'wagered', 'to_wager', 'locked_amount', 'fixed_amount')
                ->where([['game_id', '=', $game_id], ...$params])
                ->orWhere([['game_id', '=', 0], ...$params])
                ->get();
		}

		
        if (isset($bonus_issue[0])) {
            return $bonus_issue[0];
        }

        return 0;
    }


    public function is_there_bonus($by, $value, $params=[])
    {

		if(count($params) == 0) {
			$is_there = DB::table('bonus_issue')->orderBy('id','asc')->select('id', 'wagered', 'to_wager', 'locked_amount')->where($by, '=', $value)->count();
		} else {
			$is_there = DB::table('bonus_issue')->orderBy('id','asc')->select('id', 'wagered', 'to_wager', 'locked_amount')->where($params)->count();
		}

        return $is_there;
    }



}
