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
        Commands\FixTicketConcernDepartments::class,
        Commands\FixTicketConcernDepartmentsCommand::class,
        Commands\FixDepartmentsCommand::class,
        Commands\FixNotificationsCondoLocation::class,
        Commands\CleanupOldNotifications::class,
        Commands\CleanupDuplicateNotifications::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Run notification cleanup daily at midnight
        $schedule->command('notifications:cleanup')->daily();

        // Run duplicate notification cleanup every 6 hours
        $schedule->command('notifications:cleanup-duplicates')->everyFourHours();
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
