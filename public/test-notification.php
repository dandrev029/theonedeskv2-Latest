<?php

// This is a simple test script to create a notification for a user

// Bootstrap the Laravel application
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\NotificationService;

// Get the user by ID or use the first user
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if ($userId) {
    $user = User::find($userId);
    if (!$user) {
        echo "User with ID {$userId} not found.";
        exit;
    }
} else {
    $user = User::first();
    if (!$user) {
        echo "No users found in the database.";
        exit;
    }
}

// Create a notification service
$notificationService = new NotificationService();

// Create a notification for the user
$notification = $notificationService->create(
    $user->id,
    'Test Notification',
    'This is a test notification to verify that in-app notifications are working.',
    'test',
    'font-awesome.bell-solid',
    '/dashboard/home'
);

echo "Notification created for user {$user->name} (ID: {$user->id}).\n";
echo "Notification ID: {$notification->id}\n";
echo "Notification Title: {$notification->title}\n";
echo "Notification Message: {$notification->message}\n";
