<?php

// This is a simple script to test the Pusher connection

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$options = [
    'cluster' => env('PUSHER_APP_CLUSTER'),
    'useTLS' => true
];

try {
    $pusher = new Pusher\Pusher(
        env('PUSHER_APP_KEY'),
        env('PUSHER_APP_SECRET'),
        env('PUSHER_APP_ID'),
        $options
    );
    
    // Get connection info
    $info = $pusher->getChannelInfo('presence-channel');
    
    echo json_encode([
        'success' => true,
        'message' => 'Pusher connection successful',
        'config' => [
            'app_id' => env('PUSHER_APP_ID'),
            'key' => env('PUSHER_APP_KEY'),
            'cluster' => env('PUSHER_APP_CLUSTER'),
        ],
        'info' => $info
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'config' => [
            'app_id' => env('PUSHER_APP_ID'),
            'key' => env('PUSHER_APP_KEY'),
            'cluster' => env('PUSHER_APP_CLUSTER'),
        ]
    ]);
}
