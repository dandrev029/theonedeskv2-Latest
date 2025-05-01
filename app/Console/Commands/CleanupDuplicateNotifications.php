<?php

namespace App\Console\Commands;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup-duplicates {--hours=24 : Number of hours to look back for duplicates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate notifications to improve user experience';

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
        $hours = $this->option('hours');
        $this->info("Cleaning up duplicate notifications from the last {$hours} hours...");
        
        $cutoffDate = now()->subHours($hours);
        $totalDeleted = 0;
        $usersProcessed = 0;
        
        // Process users in chunks to avoid memory issues
        User::chunk(100, function ($users) use (&$totalDeleted, &$usersProcessed, $cutoffDate) {
            foreach ($users as $user) {
                $deleted = $this->cleanupDuplicatesForUser($user->id, $cutoffDate);
                $totalDeleted += $deleted;
                $usersProcessed++;
                
                if ($deleted > 0) {
                    $this->info("Deleted {$deleted} duplicate notifications for user {$user->id}");
                }
            }
        });
        
        $this->info("Processed {$usersProcessed} users");
        $this->info("Total duplicate notifications deleted: {$totalDeleted}");
        
        Log::info("Duplicate notification cleanup complete", [
            'hours_threshold' => $hours,
            'users_processed' => $usersProcessed,
            'total_deleted' => $totalDeleted
        ]);
        
        return 0;
    }
    
    /**
     * Clean up duplicate notifications for a specific user.
     *
     * @param int $userId
     * @param \Carbon\Carbon $cutoffDate
     * @return int
     */
    protected function cleanupDuplicatesForUser($userId, $cutoffDate)
    {
        $totalDeleted = 0;
        
        // Get all notifications for this user within the time period
        $notifications = AppNotification::where('user_id', $userId)
            ->where('created_at', '>=', $cutoffDate)
            ->orderBy('created_at', 'desc')
            ->get();
            
        if ($notifications->isEmpty()) {
            return 0;
        }
        
        // Track seen notifications
        $seenExactContent = [];
        $seenTicketContent = [];
        $deleteIds = [];
        
        foreach ($notifications as $notification) {
            // Skip notifications without title or message
            if (!$notification->title || !$notification->message) {
                continue;
            }
            
            // Create a unique key for exact duplicate detection
            $contentKey = "{$notification->title}|{$notification->message}";
            
            // Check for exact duplicates
            if (isset($seenExactContent[$contentKey])) {
                $deleteIds[] = $notification->id;
                continue;
            }
            
            // For ticket notifications, do additional similarity checking
            if (stripos($notification->title, 'ticket') !== false || 
                stripos($notification->message, 'ticket') !== false) {
                
                // Create a simplified key for ticket notifications
                $baseMessage = explode(':', $notification->message)[0];
                $ticketKey = "{$notification->title}|{$baseMessage}";
                
                if (isset($seenTicketContent[$ticketKey])) {
                    $deleteIds[] = $notification->id;
                    continue;
                }
                
                $seenTicketContent[$ticketKey] = $notification->id;
            }
            
            // Mark as seen
            $seenExactContent[$contentKey] = $notification->id;
        }
        
        // Delete the duplicates
        if (!empty($deleteIds)) {
            $deleted = AppNotification::whereIn('id', $deleteIds)->delete();
            $totalDeleted += $deleted;
        }
        
        return $totalDeleted;
    }
}
