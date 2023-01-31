<?php


namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;

trait SwapTrait
{
    public function handler_swap($player_id, $from, $to, $balance){
        $from_amount = (float)$balance;

        if($from !== 14) {
            $currency = \App\Models\Currency::find($from);
            $to_amount = $from_amount / $currency->rate;
        }else{
            $currency = \App\Models\Currency::find($to);
            $to_amount = $from_amount * $currency->rate;
        }


        if (!$this->check_swap($player_id, $from, $from_amount)) {
            return false;
        }

        $wallet_from = $this->get_wallet_swap($player_id, $from);
        $wallet_to = $this->get_wallet_swap($player_id, $to);


        $swap_id = $this->set_swap($from, $to, $from_amount, $to_amount);

        $this->set_transactions_swap($player_id, $from, -1 * $from_amount, 1, $wallet_from->id, $swap_id);
        $new_balance_from = (float)$wallet_from->balance - $from_amount;

        $new_balance_from = $new_balance_from >= 0 ? $new_balance_from : 0;

        $this->set_transactions_swap($player_id, $to, $to_amount, 2, $wallet_to->id, $swap_id);
        $new_balance_to = (float)$wallet_to->balance + $to_amount;

        $this->set_wallet_swap([
            'balance' => $new_balance_from
        ], $wallet_from->id);

        $this->set_wallet_swap([
            'balance' => $new_balance_to
        ], $wallet_to->id);

        return true;
    }

    private function get_wallet_swap($user_id, $currency_id)
    {
        return DB::table('wallets')->where([
            ['user_id', '=', $user_id],
            ['currency_id', '=', $currency_id],
        ])->first();
    }

    private function set_swap($from_id, $to_id, $from_amount, $to_amount)
    {
        DB::table('swaps')->insert([
            'from_id' => $from_id,
            'to_id' => $to_id,
            'from_amount' => $from_amount,
            'to_amount' => $to_amount,
        ]);

        return DB::getPdo()->lastInsertId();
    }

    private function set_wallet_swap($args, $id)
    {
        DB::table('wallets')->where('id', '=', $id)->update($args);
    }

    private function set_transactions_swap($user_id, $currency_id, $amount, $type, $wallet_id, $swap_id)
    {
        DB::table('transactions')->insert([
            'player_id' => $user_id,
            'currency_id' => $currency_id,
            'amount' => $amount,
            'reference_id' => $swap_id,
            'reference_type_id' => 9,
            'type_id' => $type,
            'wallet_id' => $wallet_id
        ]);
    }

    public function check_swap($user_id, $currency_id, $amount)
    {
        $result = DB::table('wallets')->where([
            ['user_id', '=', $user_id],
            ['currency_id', '=', $currency_id],
        ])->first();

        if (isset($result->id)) {
            return $result->balance >= $amount;
        }

        return 0;
    }


}
