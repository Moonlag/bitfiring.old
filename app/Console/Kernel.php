<?php

namespace App\Console;

use App\Cron\BonusExpiration;
use App\FreeSpinExpiration;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Cron\UpdatedRate;
use App\Console\Commands\RankUp;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
                $schedule->call(
            new UpdatedRate
        )->everyTenMinutes();

        $schedule->call(
            new BonusExpiration,
        )->everyMinute();

        $schedule->call(
            new FreeSpinExpiration,
        )->everyMinute();
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
