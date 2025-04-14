<?php

// This is a simple script to test the timezone settings

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Setting;

// Get the current timezone settings
$app_timezone = config('app.timezone');
$env_timezone = env('APP_TIMEZONE');
$setting_timezone = Setting::get('app_timezone');
$php_timezone = date_default_timezone_get();

// Output the timezone settings
echo "Application Timezone (config): " . $app_timezone . "\n";
echo "Environment Timezone (env): " . $env_timezone . "\n";
echo "Setting Timezone (database): " . $setting_timezone . "\n";
echo "PHP Timezone: " . $php_timezone . "\n";

// Output the current time in different formats
echo "Current Time (UTC): " . gmdate('Y-m-d H:i:s') . "\n";
echo "Current Time (Local): " . date('Y-m-d H:i:s') . "\n";
echo "Current Time (Carbon): " . \Carbon\Carbon::now()->format('Y-m-d H:i:s') . "\n";
echo "Current Time (Carbon UTC): " . \Carbon\Carbon::now()->utc()->format('Y-m-d H:i:s') . "\n";

// Test the timezone conversion
$utc_time = \Carbon\Carbon::now()->utc();
$local_time = $utc_time->copy()->tz($app_timezone);

echo "UTC Time: " . $utc_time->format('Y-m-d H:i:s') . "\n";
echo "Local Time: " . $local_time->format('Y-m-d H:i:s') . "\n";
echo "Difference: " . $utc_time->diffInHours($local_time) . " hours\n";

// Output the window.app object
echo "window.app object:\n";
echo json_encode([
    'url' => url('/'),
    'name'=> Setting::get('app_name', env('APP_NAME')),
    'register' => Setting::get('app_user_registration') ? true : false,
    'icon'=> \App\Support\Base::icon(),
    'background'=> \App\Support\Base::background(),
    'recaptcha_enabled' => Setting::get('recaptcha_enabled') ? true : false,
    'recaptcha_public' => Setting::get('recaptcha_public'),
    'meta_home_title' => Setting::get('meta_home_title'),
    'app_date_format' => Setting::get('app_date_format'),
    'app_date_locale' => Setting::get('app_date_locale'),
    'app_timezone' => Setting::get('app_timezone'),
    'pusher_key' => env('PUSHER_APP_KEY'),
    'pusher_cluster' => env('PUSHER_APP_CLUSTER'),
], JSON_PRETTY_PRINT);
