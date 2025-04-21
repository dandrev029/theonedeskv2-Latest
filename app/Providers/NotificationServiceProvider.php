<?php

namespace App\Providers;

use App\Channels\CustomDatabaseChannel;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Extend the notification system with our custom database channel
        Notification::extend('database', function ($app) {
            return new CustomDatabaseChannel();
        });
    }
}
