<?php

// This is a simple script to send a test event to Pusher

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$options = [
    'cluster' => env('PUSHER_APP_CLUSTER'),
    'useTLS' => true
];

$pusher = new Pusher\Pusher(
    env('PUSHER_APP_KEY'),
    env('PUSHER_APP_SECRET'),
    env('PUSHER_APP_ID'),
    $options
);

$data = [
    'message' => 'Hello from PHP!',
    'timestamp' => date('Y-m-d H:i:s')
];

$result = $pusher->trigger('test-channel', 'test-event', $data);

echo "Event sent: " . ($result ? 'success' : 'failed');
