<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set the application timezone from database settings
        $this->setApplicationTimezone();
    }

    /**
     * Set the application timezone from database settings
     *
     * @return void
     */
    private function setApplicationTimezone()
    {
        try {
            // Get timezone from database settings, fallback to config
            $timezone = Setting::get('app_timezone', config('app.timezone'));

            if ($timezone) {
                // Set the default timezone for PHP date functions
                date_default_timezone_set($timezone);

                // Update the config value for consistency
                config(['app.timezone' => $timezone]);
            }
        } catch (\Exception $e) {
            // If database is not available or there's an error, use the config default
            // This prevents the application from breaking during migrations or when DB is down
            \Illuminate\Support\Facades\Log::warning('Could not set timezone from database: ' . $e->getMessage());
        }
    }
}
