<?php

namespace App\Http\Middleware;

use App\Models\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RegisterCondoLocationPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the admin role
        $adminRole = UserRole::find(1);
        
        if ($adminRole) {
            // Make sure the permissions are properly registered
            $controllers = [];
            $permissions = json_decode((string) $adminRole->permissions, true) ?? [];
            
            foreach (Route::getRoutes()->getIterator() as $route) {
                if (strpos($route->uri, 'api/dashboard') !== false && isset($route->action['controller'])) {
                    $path = str_replace('\\', '.', explode('@', str_replace($route->action['controller'].'\\', '', $route->action['controller']))[0]);
                    $controllers[$path] = true;
                }
            }
            
            // Make sure the CondoLocationController is included
            $condoLocationController = 'App.Http.Controllers.Api.Dashboard.Admin.CondoLocationController';
            $controllers[$condoLocationController] = true;
            
            // Update the admin role permissions
            $adminRole->permissions = json_encode(array_keys($controllers));
            $adminRole->save();
        }
        
        return $next($request);
    }
}
