<?php


namespace App\Http\Traits;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait BonusExpirationTrait
{
    public function handler_user_wallet($user_id, $amount){
        $wallet = $this->get_wallet($user_id);
        if(isset($wallet->id)){
            $currency = $this->be_get_currency($wallet->currency_id);
            $new_amount = $wallet->balance + ($amount * (float)$currency->rate);
            $new_balance = $new_amount < 0 ? 0 : $new_amount;
            $this->be_update_wallet($wallet, $new_balance);
            if($new_amount < 0){
                $this->handler_user_wallet($user_id, $new_amount / (float)$currency->rate);
            }
        }
    }

    public function get_bonus_issue($now){
        return DB::table('bonus_issue')
            ->select('bonus_issue.id', 'bonus_issue.wagered', 'bonus_issue.to_wager', 'bonus_issue.locked_amount', 'bonus_issue.fixed_amount', 'bonus_issue.amount', 'bonuses.amount as bonus_ratio', 'bonus_issue.admin_id', 'bonus_issue.currency_id', 'bonus_issue.cat_type', 'bonus_issue.user_id')
            ->Join('bonuses', 'bonus_issue.bonus_id', '=', 'bonuses.id')
            ->where([['bonus_issue.active_until', '<=', $now], ['bonus_issue.status', '=', 2]])
            ->get();
    }

    public function update_bonus($bonus, $status){
        DB::table('bonus_issue')->where('id', $bonus->id)->update(['status' => $status]);
    }

    public function get_wallet($user_id){
        return DB::table('wallets')->where([['user_id', $user_id], ['balance', '>', 0]])->first();
    }

    public function be_update_wallet($wallet, $amount){
        return DB::table('wallets')->where('id', $wallet->id)->update(['balance' => $amount]);
    }

    public function be_get_currency($currency_id){
        return DB::table('currency')->where('id', $currency_id)->first();
    }
}
