<?php

// This is a script to test notifications for users with dashboard permissions

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the user ID from the query string
$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// Check if the user exists and has dashboard permissions
$user = \App\Models\User::find($userId);
if (!$user) {
    echo json_encode(['error' => 'User not found', 'user_id' => $userId]);
    exit;
}

// Check if user has dashboard permissions
$hasDashboardPermissions = $user->role && ($user->role->dashboard || $user->role->dashboard_access);
if (!$hasDashboardPermissions) {
    echo json_encode(['error' => 'User does not have dashboard permissions', 'user_id' => $userId, 'role' => $user->role ? $user->role->name : 'No role']);
    exit;
}

// Create a notification using both methods
try {
    // 1. Create an AppNotification
    $appNotification = \App\Models\AppNotification::create([
        'user_id' => $userId,
        'title' => 'Dashboard Test Notification',
        'message' => 'This is a test notification for dashboard users sent at ' . date('Y-m-d H:i:s'),
        'type' => 'test',
        'icon' => 'font-awesome.bell-solid',
        'link' => '/dashboard',
        'is_read' => false,
    ]);

    // We no longer need to create a separate Laravel notification
    // The CreatesAppNotification trait now handles this automatically
    // and prevents duplicates

    // 3. Broadcast the notification
    event(new \App\Events\NewNotification($appNotification));

    echo json_encode([
        'success' => true,
        'message' => 'Notification sent successfully using both methods',
        'app_notification' => $appNotification,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ? $user->role->name : 'No role',
            'has_dashboard_permissions' => $hasDashboardPermissions
        ]
    ]);
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
