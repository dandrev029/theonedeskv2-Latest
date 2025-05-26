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
        try {
            // Get the admin role
            $adminRole = UserRole::find(1);

            if ($adminRole) {
                // Make sure the permissions are properly registered
                $controllers = [];

                // Handle permissions properly - check if it's already an array or needs decoding
                $permissions = [];
                if (is_string($adminRole->permissions)) {
                    $permissions = json_decode($adminRole->permissions, true) ?? [];
                } elseif (is_array($adminRole->permissions)) {
                    $permissions = $adminRole->permissions;
                }

                foreach (Route::getRoutes()->getIterator() as $route) {
                    if (strpos($route->uri, 'api/dashboard') !== false && isset($route->action['controller'])) {
                        $controllerAction = $route->action['controller'];
                        if (is_string($controllerAction)) {
                            // Extract controller class name properly
                            $parts = explode('@', $controllerAction);
                            if (count($parts) > 0) {
                                $controllerClass = $parts[0];
                                $path = str_replace('\\', '.', $controllerClass);
                                $controllers[$path] = true;
                            }
                        }
                    }
                }

                // Make sure the CondoLocationController is included
                $condoLocationController = 'App.Http.Controllers.Api.Dashboard.Admin.CondoLocationController';
                $controllers[$condoLocationController] = true;

                // Update the admin role permissions
                $adminRole->permissions = json_encode(array_keys($controllers));
                $adminRole->save();
            }
        } catch (\Exception $e) {
            // Log the error but don't break the request
            \Log::error('RegisterCondoLocationPermission middleware error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
