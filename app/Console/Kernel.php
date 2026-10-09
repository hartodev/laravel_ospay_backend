<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\CheckPendingTransactions;
use App\Console\Commands\RetryFailedRefunds;
use App\Console\Commands\ExpireStaleDeposits;
use App\Console\Commands\SyncPriceList;
use App\Console\Commands\ImportPriceList;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        CheckPendingTransactions::class,
        RetryFailedRefunds::class,
        ExpireStaleDeposits::class,
        SyncPriceList::class,
        ImportPriceList::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(CheckPendingTransactions::class)->everyFiveMinutes()->withoutOverlapping();
        $schedule->command(RetryFailedRefunds::class)->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command(ExpireStaleDeposits::class)->hourly()->withoutOverlapping();
        $schedule->command(SyncPriceList::class)->dailyAt('03:00')->withoutOverlapping();
    }
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
