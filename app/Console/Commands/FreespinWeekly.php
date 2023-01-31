<?php

namespace App\Console\Commands;

use App\Http\Traits\Reward;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FreespinWeekly extends Command
{
    use Reward;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freespin:weekly';

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
        $freespins = \App\Models\FreespinWeekly::query()->where('parsed', '=', 0)->limit(10)->get();

        foreach ($freespins as $free){
            $player = \App\Models\Players::find($free->player_id);

            if(!isset($player->id)){

                $this->get_freespin($player->id, $free->freespin_id);
                $free->parsed = 1;
                $free->save();
            }
        }

        return 0;
    }
}
