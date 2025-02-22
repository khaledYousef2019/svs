<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\MemberBonusDistribute::class,
        Commands\CustomTokenDeposit::class,
        Commands\AdjustCustomTokenDeposit::class,
        Commands\SvsPriceUpdate::class,
        Commands\CoinInfoUpdate::class,
        Commands\CoinPriceUpdate::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command('command:membershipbonus')
             ->daily();

        $schedule->command('custom-token-deposit')
            ->everyMinute();

        $schedule->command('adjust-token-deposit')
            ->everyThirtyMinutes();

        $schedule->command('coininfo:update')
            ->daily();

        $schedule->command('SvsPriceUpdate')
            ->everyFiveMinutes();

        $schedule->command('coinprice:update')
            ->everyFiveMinutes();
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
