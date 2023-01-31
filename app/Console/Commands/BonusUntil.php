<?php

namespace App\Console\Commands;

use App\Models\BonusIssue;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BonusUntil extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:until';

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
        $now = Carbon::now();
        $bonusIssue = BonusIssue::query()->where('active_until', '<=', $now)->get();
        BonusIssue::query()->where('id', $bonusIssue->pluck('id'));
        return 0;
    }
}
