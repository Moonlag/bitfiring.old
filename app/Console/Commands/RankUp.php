<?php

namespace App\Console\Commands;

use App\Http\Traits\Reward;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Players;
use App\Models\LoyaltyRanks;
use App\Models\Ranks;

class RankUp extends Command
{
    use Reward;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rank:up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $now = Carbon::now();
        $before_now = $now->subMinute();

        $rank = LoyaltyRanks::firstOrCreate(
            ['player_id' => 1],
            [
                'exp' => 1,
                'rank_id' => 0
            ]
        );

        $rank->exp = 65000;
        $rank->save();
        echo $rank->rank_id;
        $rank->rank_id = Ranks::query()->where('lvlup', '<=', $rank->exp)->latest('lvlup')->first()->id;

        echo $rank->getOriginal('rank_id');

        if ($rank->rank_id != $rank->getOriginal('rank_id')) {
            $this->set_reward(1, $rank->rank_id);
            echo "reward";
            $rank->save();
        }else{
            echo "no reward";
        }

        return 0;
    }

    public function get_exp($deposit_sum, $wager)
    {
        if (100 > $deposit_sum) {
            return $wager * $deposit_sum * 0.004;
        } elseif (100 < $deposit_sum && 400 > $deposit_sum) {
            return $wager * 0.4;
        } elseif (400 < $deposit_sum && 500 > $deposit_sum) {
            return $wager * $deposit_sum * 0.001;
        } elseif (500 < $deposit_sum && 850 > $deposit_sum) {
            return $wager * 0.5;
        } elseif (850 < $deposit_sum && 1000 > $deposit_sum) {
            return $wager * $deposit_sum * 0.0006;
        } else {
            return $wager * 0.6;
        }
    }

    public function lvl_up($player_id, $exp){
        $rank = LoyaltyRanks::firstOrNew(
            ['player_id' => $player_id],
            ['exp' => 1]
        );

        $rank->exp = $exp;
        $rank->rank_id = Ranks::query()->where('lvlup', '<=', $rank->exp)->latest('lvlup')->first()->id;
        $rank->save();



        return $rank;
    }
}
