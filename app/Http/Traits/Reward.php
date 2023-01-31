<?php


namespace App\Http\Traits;


use App\Models\Payments;
use Illuminate\Support\Facades\DB;

trait Reward
{

    public function set_reward($player_id, $rank_id){
        switch ($rank_id) {
            case 1:
                break;
            case 2:
                $this->get_freespin($player_id, 8);
                break;
            case 3:
                $this->get_freespin($player_id, 9);
                break;
            case 4:
                $this->get_freespin($player_id, 10);
                break;
            case 5:
                $this->get_freespin($player_id, 11);
                break;
            case 6:
                $this->get_bonus($player_id, 14, 10, '10$ BONUS');
                $this->get_freespin($player_id, 12);
                $this->get_weekly_fs($player_id, 12);
                break;
            case 7:
                $this->get_bonus($player_id, 14, 10, '10$ BONUS');
                $this->get_freespin($player_id, 12);
                $this->get_weekly_fs($player_id, 12);
                break;
            case 8:
                $this->get_bonus($player_id, 14, 10, '10$ BONUS');
                $this->get_freespin($player_id, 13);
                $this->get_weekly_fs($player_id, 13);
                break;
            case 9:
                $this->get_bonus($player_id, 14, 15, '15$ BONUS');
                $this->get_freespin($player_id, 13);
                $this->get_weekly_fs($player_id, 13);
                break;
            case 10:
                $this->get_bonus($player_id, 14, 15, '15$ BONUS');
                $this->get_freespin($player_id, 14);
                $this->get_weekly_fs($player_id, 13);
                break;
            case 11:
                $this->get_bonus($player_id, 14, 15, '15$ BONUS');
                $this->get_freespin($player_id, 23);
                $this->get_weekly_fs($player_id, 13);
                break;
            case 12:
                $this->get_bonus($player_id, 14, 20, '20$ BONUS');
                $this->get_freespin($player_id, 15);
                $this->get_weekly_fs($player_id, 13);
                break;
            case 13:
                $this->get_bonus($player_id, 14, 25, '25$ BONUS');
                $this->get_freespin($player_id, 15);
                $this->get_weekly_fs($player_id, 15);
                break;
            case 14:
                $this->get_bonus($player_id, 14, 25, '25$ BONUS');
                $this->get_freespin($player_id, 24);
                $this->get_weekly_fs($player_id, 15);
                break;
            case 15:
                $this->get_bonus($player_id, 14, 30, '30$ BONUS');
                $this->get_freespin($player_id, 25);
                $this->get_cashback($player_id, 5);
                $this->get_weekly_fs($player_id, 15);
                break;
            case 16:
                $this->get_bonus($player_id, 14, 30, '30$ BONUS');
                $this->get_freespin($player_id, 16);
                $this->get_cashback($player_id, 5);
                $this->get_weekly_fs($player_id, 16);
                break;
            case 17:
                $this->get_bonus($player_id, 14, 35, '35$ BONUS');
                $this->get_freespin($player_id, 26);
                $this->get_cashback($player_id, 5);
                $this->get_weekly_fs($player_id, 16);
                break;
            case 18:
                $this->get_bonus($player_id, 14, 35, '35$ BONUS');
                $this->get_freespin($player_id, 17);
                $this->get_cashback($player_id, 5);
                $this->get_weekly_fs($player_id, 17);
                break;
            case 19:
                $this->get_bonus($player_id, 14, 40, '40$ BONUS');
                $this->get_freespin($player_id, 17);
                $this->get_cashback($player_id, 7.5);
                $this->get_weekly_fs($player_id, 17);
                break;
            case 20:
                $this->get_bonus($player_id, 14, 45, '45$ BONUS');
                $this->get_freespin($player_id, 17);
                $this->get_cashback($player_id, 7.5);
                $this->get_weekly_fs($player_id, 17);
                break;
            case 21:
                $this->get_bonus($player_id, 14, 50, '50$ BONUS');
                $this->get_freespin($player_id, 17);
                $this->get_cashback($player_id, 7.5);
                $this->get_weekly_fs($player_id, 17);
                break;
            case 22:
                $this->get_bonus($player_id, 14, 50, '50$ BONUS');
                $this->get_freespin($player_id, 18);
                $this->get_real_balance($player_id, 20);
                $this->get_cashback($player_id, 7.5);
                $this->get_weekly_fs($player_id, 17);
                break;
            case 23:
                $this->get_bonus($player_id, 14, 60, '60$ BONUS');
                $this->get_freespin($player_id, 18);
                $this->get_real_balance($player_id, 20);
                $this->get_cashback($player_id, 10);
                $this->get_weekly_fs($player_id, 18);
                break;
            case 24:
                $this->get_bonus($player_id, 14, 60, '60$ BONUS');
                $this->get_freespin($player_id, 18);
                $this->get_real_balance($player_id, 25);
                $this->get_cashback($player_id, 10);
                $this->get_weekly_fs($player_id, 18);
                break;
            case 25:
                $this->get_bonus($player_id, 14, 70, '70$ BONUS');
                $this->get_freespin($player_id, 19);
                $this->get_real_balance($player_id, 25);
                $this->get_cashback($player_id, 10);
                $this->get_weekly_fs($player_id, 18);
                break;
            case 26:
                $this->get_bonus($player_id, 14, 75, '75$ BONUS');
                $this->get_freespin($player_id, 19);
                $this->get_real_balance($player_id, 30);
                $this->get_cashback($player_id, 10);
                $this->get_weekly_fs($player_id, 19);
                break;
            case 27:
                $this->get_bonus($player_id, 14, 75, '75$ BONUS');
                $this->get_freespin($player_id, 20);
                $this->get_real_balance($player_id, 35);
                $this->get_cashback($player_id, 15);
                $this->get_weekly_fs($player_id, 20);
                break;
            case 28:
                $this->get_bonus($player_id, 14, 85, '85$ BONUS');
                $this->get_freespin($player_id, 21);
                $this->get_real_balance($player_id, 40);
                $this->get_cashback($player_id, 15);
                $this->get_weekly_fs($player_id, 21);
                break;
            case 29:
                $this->get_bonus($player_id, 14, 100, '100$ BONUS');
                $this->get_freespin($player_id, 21);
                $this->get_real_balance($player_id, 50);
                $this->get_cashback($player_id, 15);
                $this->get_weekly_fs($player_id, 21);
                break;
            case 30:
                $this->get_bonus($player_id, 14, 100, '100$ BONUS');
                $this->get_freespin($player_id, 22);
                $this->get_real_balance($player_id, 70);
                $this->get_cashback($player_id, 15);
                $this->get_weekly_fs($player_id, 22);
                break;
        }
    }

    public function get_bonus($user_id, $currency_id, $amount, $title, $wager = 70)
    {
        $currency = DB::table('currency')->where('id', $currency_id)->first();
        $amount = (float)$amount;
        $valid_until = \Illuminate\Support\Carbon::now()->addDays(3);
        $bonus_amount = $amount / $currency->rate;

        DB::table('bonuses_user')->insert([
            'user_id' => $amount,
            'published' => 1,
            'stage' => 2,
            'currency' => $currency_id,
            'bonus_id' => 0,
            'amount' => $bonus_amount,
            'wager' => $wager,
            'created_at' => \Illuminate\Support\Carbon::now(),
        ]);

        $locked_amount = $bonus_amount;
        $to_wager = (float)$wager * $bonus_amount;

        $id = DB::table('bonus_issue')->insertGetId([
            'user_id' => $user_id,
            'currency_id' => $currency_id,
            'bonus_id' => 0,
            'amount' => $bonus_amount,
            'locked_amount' => $locked_amount,
            'fixed_amount' => $locked_amount,
            'active_until' => $valid_until,
            'to_wager' => $to_wager,
            'wagered' => 0,
            'stage' => 2,
            'status' => 1,
            'admin_id' => 1,
            'custom_title' => $title,
            'created_at' => \Illuminate\Support\Carbon::now(),
        ]);

        return ['id' => $id];
    }

    public function get_freespin($user_id, $freespin_id)
    {
        $freespin = \App\Models\FreespinBonusModel::query()
            ->where('id', '=', $freespin_id)
            ->first();

        $expiry = new \App\Models\Bonuses();
        $expiry_method = $freespin->activate_duration_type ? $expiry::DURATION[$freespin->activate_duration_type] : null;

        \App\Models\FreespinIssue::query()
            ->where('player_id', '=', $user_id)
            ->where('status', '=', 1)
            ->update([
                'status' => 2
            ]);

        return \App\Models\FreespinIssue::query()
            ->create([
                'title' => $freespin->title,
                'player_id' => $user_id,
                'currency_id' => $freespin->currency_id,
                'bonus_id' => $freespin->id,
                'count' => $freespin->count,
                'win' => 0,
                'stage' => 2,
                'status' => 1,
                'active_until' => $expiry_method ? $expiry->$expiry_method($freespin->activate_duration) : null
            ]);
    }

    public function get_real_balance($user_id, $amount, $currency_id = 14)
    {

        $player = \App\Models\Players::find($user_id);

        $wallet = \App\Models\Wallets::query()->where([
            ['user_id', '=', $player->id],
            ['currency_id', '=', $currency_id]
        ])->first();

        if (empty($wallet)) {
            $wallet = new \App\Models\Wallets();
            $wallet->primary = 0;
            $wallet->currency_id = $currency_id;
            $wallet->user_id = $player->id;
            $wallet->balance = 0;
            $wallet->save();
        }

        $currency = \App\Models\Currency::find($currency_id);


        $data = [
            'staff_id' => 0,
            'wallet_id' => $wallet,
            'user_id' => $player->id,
            'currency_id' => $currency_id,
            'amount' => $amount,
            'amount_usd' => $amount / $currency->rate,
            'type_id' => 5,
            'status' => 1,
            'email' => $player->email
        ];

        $payment = \App\Models\Payments::create($data);

        \App\Models\Transactions::query()->insert([
            'wallet_id' => $wallet,
            'bonus_part' => 0,
            'reference_id' => $payment->id,
            'reference_type_id' => 1,
            'player_id' => $player->id,
            'token' => $player->session_id,
            'currency_id' => $currency_id,
            'amount_usd' => $amount / $currency->rate,
            'amount' => $amount,
            'type_id' => 5,
        ]);

        $wallet->balance = $wallet->balance + $amount;
        $wallet->save();
    }

    public function get_cashback($player_id, $amount){
        \App\Models\CashbackShedule::updateOrCreate([
            'player_id' => $player_id
        ], ['percent' => $amount]);
    }

    public function get_weekly_fs($player_id, $freespin_id){
        \App\Models\FreespinWeekly::updateOrCreate([
            'player_id' => $player_id
        ], ['freespin_id' => $freespin_id]);
    }
}
