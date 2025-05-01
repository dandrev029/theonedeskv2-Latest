<?php

namespace App\Console\Commands;

use App\Models\AppNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup {--days=30 : Number of days to keep notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old notifications to prevent database bloat';

    /**
     * The notification service.
     *
     * @var \App\Services\NotificationService
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\NotificationService $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Cleaning up notifications older than {$days} days...");

        // Delete old notifications based on days
        $cutoffDate = now()->subDays($days);
        $deletedByDate = AppNotification::where('created_at', '<', $cutoffDate)->delete();
        $this->info("Deleted {$deletedByDate} notifications older than {$days} days");

        // Clean up per-user notifications to keep only the most recent ones
        $userCount = 0;
        $totalDeleted = $deletedByDate;

        User::chunk(100, function ($users) use (&$userCount, &$totalDeleted) {
            foreach ($users as $user) {
                $deleted = $this->notificationService->cleanupOldNotifications($user->id);
                $totalDeleted += $deleted;
                $userCount++;
            }
        });

        $this->info("Processed notifications for {$userCount} users");
        $this->info("Total notifications deleted: {$totalDeleted}");

        Log::info("Notification cleanup complete", [
            'days_threshold' => $days,
            'deleted_by_date' => $deletedByDate,
            'users_processed' => $userCount,
            'total_deleted' => $totalDeleted
        ]);

        return 0;
    }
}
