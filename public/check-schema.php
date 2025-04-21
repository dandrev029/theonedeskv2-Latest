<?php

// This script checks the schema of the notifications table

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the schema for the notifications table
$columns = \DB::select('SHOW COLUMNS FROM notifications');
echo "<h1>Notifications Table Schema</h1>";
echo "<pre>";
print_r($columns);
echo "</pre>";

// Get the schema for the app_notifications table
$appColumns = \DB::select('SHOW COLUMNS FROM app_notifications');
echo "<h1>App Notifications Table Schema</h1>";
echo "<pre>";
print_r($appColumns);
echo "</pre>";

// Check if there are any notifications in the database
$notifications = \DB::table('notifications')->limit(5)->get();
echo "<h1>Sample Notifications</h1>";
echo "<pre>";
print_r($notifications);
echo "</pre>";

// Check if there are any app_notifications in the database
$appNotifications = \DB::table('app_notifications')->limit(5)->get();
echo "<h1>Sample App Notifications</h1>";
echo "<pre>";
print_r($appNotifications);
echo "</pre>";
