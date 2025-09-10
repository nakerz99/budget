<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Send bill reminders daily at 9 AM
        $schedule->job(new \App\Jobs\SendBillReminders)->daily()->at('09:00');
        
        // Process recurring transactions daily at midnight
        $schedule->job(new \App\Jobs\ProcessRecurringTransactions)->daily();
        
        // Clean up old logs weekly
        $schedule->command('log:clear')->weekly();
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
