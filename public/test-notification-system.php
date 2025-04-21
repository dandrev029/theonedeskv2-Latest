<?php

// This is a diagnostic script to check the notification system

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the user ID from the query string
$userId = $_GET['user_id'] ?? null;

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

// Check Pusher configuration
$pusherConfig = [
    'app_id' => env('PUSHER_APP_ID'),
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'cluster' => env('PUSHER_APP_CLUSTER'),
    'useTLS' => true
];

// Check if we have all required Pusher config
if (empty($pusherConfig['app_id']) || empty($pusherConfig['key']) || empty($pusherConfig['secret']) || empty($pusherConfig['cluster'])) {
    echo json_encode([
        'error' => 'Pusher configuration is incomplete',
        'config' => $pusherConfig
    ]);
    exit;
}

// Check if the broadcast driver is set to pusher
$broadcastDriver = env('BROADCAST_DRIVER');
if ($broadcastDriver !== 'pusher') {
    echo json_encode([
        'error' => 'Broadcast driver is not set to pusher',
        'current_driver' => $broadcastDriver
    ]);
    exit;
}

// Check if the BroadcastServiceProvider is registered
$providers = config('app.providers');
$broadcastProviderRegistered = in_array('App\Providers\BroadcastServiceProvider', $providers);
if (!$broadcastProviderRegistered) {
    echo json_encode([
        'error' => 'BroadcastServiceProvider is not registered in config/app.php'
    ]);
    exit;
}

// Check if the notification channel is defined
$channelsDefined = file_exists(base_path('routes/channels.php'));
if (!$channelsDefined) {
    echo json_encode([
        'error' => 'routes/channels.php file not found'
    ]);
    exit;
}

// Try to create a notification
try {
    $notificationService = app(\App\Services\NotificationService::class);
    
    $notification = $notificationService->create(
        (int) $userId,
        'Diagnostic Test Notification',
        'This is a diagnostic test notification sent at ' . date('Y-m-d H:i:s'),
        'diagnostic',
        'font-awesome.wrench-solid',
        '/dashboard'
    );
    
    if (!$notification) {
        echo json_encode([
            'error' => 'Failed to create notification',
            'user_id' => $userId
        ]);
        exit;
    }
    
    // Check if the notification was saved to the database
    $savedNotification = \App\Models\AppNotification::find($notification->id);
    if (!$savedNotification) {
        echo json_encode([
            'error' => 'Notification was not saved to the database',
            'notification_id' => $notification->id
        ]);
        exit;
    }
    
    // All checks passed
    echo json_encode([
        'success' => true,
        'message' => 'Notification system is working correctly',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ],
        'notification' => $notification,
        'pusher_config' => [
            'app_id' => $pusherConfig['app_id'],
            'key' => $pusherConfig['key'],
            'cluster' => $pusherConfig['cluster']
        ],
        'broadcast_driver' => $broadcastDriver,
        'broadcast_provider_registered' => $broadcastProviderRegistered,
        'channels_defined' => $channelsDefined
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
