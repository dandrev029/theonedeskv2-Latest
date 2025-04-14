<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification 
                            {user_id? : The ID of the user to send the notification to. If not provided, will prompt for selection}
                            {--all : Send to all users}
                            {--role= : Send to users with specific role ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification to test the notification system';

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
        $title = $this->ask('Enter notification title', 'Test Notification');
        $message = $this->ask('Enter notification message', 'This is a test notification from the command line.');
        $type = $this->choice('Select notification type', ['general', 'ticket', 'system', 'alert'], 'general');
        $icon = $this->ask('Enter notification icon (Font Awesome name without prefix)', 'bell');
        
        // Handle different target scenarios
        if ($this->option('all')) {
            // Send to all users
            $count = $this->sendToAllUsers($title, $message, $type, $icon);
            $this->info("Sent notification to {$count} users successfully");
        } elseif ($roleId = $this->option('role')) {
            // Send to users with specific role
            $count = $this->sendToRole($roleId, $title, $message, $type, $icon);
            $this->info("Sent notification to {$count} users with role ID {$roleId} successfully");
        } else {
            // Send to specific user
            $userId = $this->argument('user_id');
            
            if (!$userId) {
                $userId = $this->selectUser();
            }
            
            if (!$userId) {
                $this->error('No user selected. Aborting.');
                return 1;
            }
            
            $this->sendToUser($userId, $title, $message, $type, $icon);
            $this->info("Notification sent to user ID {$userId} successfully");
        }
        
        return 0;
    }
    
    /**
     * Send notification to a specific user.
     *
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string $icon
     * @return \App\Models\AppNotification
     */
    protected function sendToUser($userId, $title, $message, $type, $icon)
    {
        return $this->notificationService->create(
            $userId,
            $title,
            $message,
            $type,
            "font-awesome.{$icon}-regular",
            null,
            ['source' => 'test-command']
        );
    }
    
    /**
     * Send notification to all users.
     *
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string $icon
     * @return int
     */
    protected function sendToAllUsers($title, $message, $type, $icon)
    {
        $notifications = $this->notificationService->createForAllUsers(
            $title,
            $message,
            $type,
            "font-awesome.{$icon}-regular",
            null,
            ['source' => 'test-command']
        );
        
        return count($notifications);
    }
    
    /**
     * Send notification to users with specific role.
     *
     * @param int $roleId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string $icon
     * @return int
     */
    protected function sendToRole($roleId, $title, $message, $type, $icon)
    {
        $notifications = $this->notificationService->createForRole(
            $roleId,
            $title,
            $message,
            $type,
            "font-awesome.{$icon}-regular",
            null,
            ['source' => 'test-command']
        );
        
        return count($notifications);
    }
    
    /**
     * Select a user from a list.
     *
     * @return int|null
     */
    protected function selectUser()
    {
        $users = User::select('id', 'name', 'email')->get()->mapWithKeys(function ($user) {
            return [$user->id => "{$user->name} ({$user->email})"];
        })->toArray();
        
        if (empty($users)) {
            $this->error('No users found in the system.');
            return null;
        }
        
        return $this->choice('Select a user to send notification to', $users);
    }
}
