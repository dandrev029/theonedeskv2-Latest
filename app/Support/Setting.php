<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Class Setting
 * @package App\Support
 */
class Setting
{
    public static function get($key, $default = null)
    {
        try {
            if ($setting = \App\Models\Setting::find($key)) {
                return $setting->value;
            }
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error("Database error when fetching setting {$key}: " . $e->getMessage());

            // If there's a database connection error, try to get the value from .env
            if (strpos($e->getMessage(), 'SQLSTATE[HY000] [2002]') !== false) {
                // For app_locale specifically, return from .env
                if ($key === 'app_locale') {
                    return env('APP_LOCALE', $default);
                }
            }
        }

        return $default;
    }

    public static function setEnv($key, $value = null): void
    {
        try {
            // Try to use the SetEnv class if it exists
            if (class_exists('SetEnv')) {
                $setEnv = new \SetEnv();
                $setEnv->setKey($key, $value);
                $setEnv->save();
            } else {
                // Fallback: directly modify the .env file
                $path = base_path('.env');

                if (file_exists($path)) {
                    $content = file_get_contents($path);

                    // If the key exists, replace its value
                    if (strpos($content, "{$key}=") !== false) {
                        $content = preg_replace("/{$key}=.*/", "{$key}={$value}", $content);
                    } else {
                        // Otherwise, add the key-value pair
                        $content .= PHP_EOL . "{$key}={$value}";
                    }

                    file_put_contents($path, $content);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error setting environment variable {$key}: " . $e->getMessage());
        }
    }
}
