<?php

// This is a script to test notifications for all users

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the user ID from the URL
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$type = $_GET['type'] ?? 'test';

if (!$userId) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// Check if the user exists
$user = \App\Models\User::find($userId);
if (!$user) {
    echo json_encode(['error' => 'User not found', 'user_id' => $userId]);
    exit;
}

// Create a notification
try {
    // Create an AppNotification
    $notification = \App\Models\AppNotification::create([
        'user_id' => $userId,
        'title' => 'Test Notification',
        'message' => 'This is a test notification sent at ' . date('Y-m-d H:i:s'),
        'type' => $type,
        'icon' => 'font-awesome.bell-solid',
        'link' => '/dashboard',
        'is_read' => false,
    ]);

    // Create a database notification for Laravel's notification system
    try {
        // Create a database record directly to avoid the user_id issue
        \DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\\Notifications\\InAppNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $userId,
            'user_id' => $userId, // Add user_id explicitly
            'data' => json_encode([
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'icon' => $notification->icon,
                'link' => $notification->link,
                'created_at' => $notification->created_at->toDateTimeString(),
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } catch (\Exception $dbEx) {
        \Log::error('Error creating database notification: ' . $dbEx->getMessage(), [
            'user_id' => $userId,
            'exception' => $dbEx
        ]);
        // Continue even if database notification fails
    }

    // Broadcast the notification
    event(new \App\Events\NewNotification($notification));

    echo json_encode([
        'success' => true,
        'message' => 'Notification sent successfully',
        'notification' => $notification
    ]);
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
