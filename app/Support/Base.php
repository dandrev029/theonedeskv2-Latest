<?php

namespace App\Support;

use App\Support\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Class Base
 * @package App\Support
 */
class Base
{
    /**
     * @return string|string[]
     */
    public static function locale()
    {
        try {
            return str_replace('_', '-', Setting::get('app_locale', env('APP_LOCALE', app()->getLocale())));
        } catch (\Exception $e) {
            // If there's any error, fallback to env or default locale
            \Illuminate\Support\Facades\Log::error("Error getting locale: " . $e->getMessage());
            return str_replace('_', '-', env('APP_LOCALE', app()->getLocale()));
        }
    }

    /**
     * @return string
     */
    public static function icon(): string
    {
        $icon = Setting::get('app_icon');
        switch ($icon) {
            case null:
            case 'default':
                return asset('images/default/icon.png');
            default:
                if (Storage::disk('public')->exists($icon)) {
                    return URL::to(Storage::disk('public')->url($icon));
                }
                return asset('images/default/icon.png');
        }
    }

    /**
     * @return string
     */
    public static function background(): string
    {
        $icon = Setting::get('app_background');
        switch ($icon) {
            case null:
            case 'default':
                return asset('images/default/background.jpg');
            default:
                if (Storage::disk('public')->exists($icon)) {
                    return URL::to(Storage::disk('public')->url($icon));
                }
                return asset('images/default/background.jpg');
        }
    }
}
