<?php

// This is a simple script to send a test notification

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the user ID from the query string
$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// Create a notification for the user
try {
    $notificationService = app(\App\Services\NotificationService::class);
    
    $notification = $notificationService->create(
        (int) $userId,
        'Test Notification',
        'This is a test notification sent at ' . date('Y-m-d H:i:s'),
        'test',
        'font-awesome.bell-solid',
        '/dashboard'
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification sent successfully',
        'notification' => $notification
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
