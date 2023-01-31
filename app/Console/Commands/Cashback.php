<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class Cashback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'player:cashback';

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
     * @return int
     */
    public function handle()
    {
        $cashbacks = \App\Models\CashbackShedule::query()->where('parsed', '=', 0)->limit(10)->get();

        foreach ($cashbacks as $cash){
            $player = \App\Models\Players::find($cash->player_id);
            $bets = $player->bets()->where('created_at', '>', Carbon::now()->subDay()->startOfDay()
            )->get();
            $ggr = $bets->sum('bet_sum') - $bets->sum('profit');

            if($ggr > 0){
                \App\Models\Wallets::query()->where([
                    ['user_id', '=', $player->id],
                    ['currency_id', '=', 14]
                ])->update(['balance' => new Expression('balance + ' . $ggr)]);
            }

            $cash->parsed = 1;
            $cash->save();
        }

        return 0;
    }
}
