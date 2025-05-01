<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Try to connect to the database
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            // Log the error
            Log::error('Database connection failed: ' . $e->getMessage());
            
            // For API requests, return a JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Database connection failed',
                    'message' => 'The application is currently unable to connect to the database. Please try again later.'
                ], 503);
            }
            
            // For web requests, you could redirect to a maintenance page
            // or continue and let the application handle it with fallbacks
        }
        
        return $next($request);
    }
}
