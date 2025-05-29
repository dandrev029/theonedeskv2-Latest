<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Added sanctum/csrf-cookie path

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], // Specified methods

    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:8000')], // Use env variable, restrict origins

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'X-XSRF-TOKEN'], // Specified headers

    'exposed_headers' => [], // Consider adding 'XSRF-TOKEN' if frontend needs to read it directly

    'max_age' => 3600, // Cache preflight requests for 1 hour

    'supports_credentials' => true, // Crucial for SPA authentication

];
