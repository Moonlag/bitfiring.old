<?php


namespace App\Cron;

use App\Events\UpdateBalance;
use App\Http\Traits\BonusExpirationTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BonusExpiration
{
    use BonusExpirationTrait;

    public function __invoke()
    {
        $now = Carbon::now();
        $expire_bonus = $this->get_bonus_issue($now);

        foreach ($expire_bonus as $user_bonus) {
            if ($user_bonus->cat_type === 2) {
                $amount = -1 * $user_bonus->locked_amount;
            } else {
                $ggr = $user_bonus->locked_amount - $user_bonus->fixed_amount;

                if($user_bonus->amount - $ggr > 0){
                    $amount = -($user_bonus->fixed_amount - ($user_bonus->amount - $ggr));
                }

                if($user_bonus->amount - $ggr < 0){
                    $amount = -$user_bonus->fixed_amount;
                }

                if($user_bonus->amount - $ggr >= $user_bonus->amount){
                    $amount = -($user_bonus->amount - $ggr);
                }
            }

            $wallet = \App\Models\Wallets::query()
                ->where('user_id', $user_bonus->user_id)
                ->where('currency_id', '=', $user_bonus->currency_id)
                ->first();

            \App\Models\Transactions::query()->insert([
                'amount' => $amount,
                'bonus_part' => 0,
                'currency_id' => $user_bonus->currency_id,
                'reference_id' => $user_bonus->id,
                'wallet_id' => $wallet->id,
                'player_id' => $user_bonus->user_id,
                'type_id' => 1,
                'reference_type_id' => 5,
                'amount_usd' => $amount
            ]);

            $this->handler_user_wallet($user_bonus->user_id, $amount);

            \App\Models\BonusIssue::query()
                ->where('id', $user_bonus->id)
                ->update(['status' => 6, 'expiry_at' => $now]);

            event(new UpdateBalance($user_bonus->user_id));
        }

    }


}
