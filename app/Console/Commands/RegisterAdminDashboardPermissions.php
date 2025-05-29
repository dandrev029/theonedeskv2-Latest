<?php

namespace App\Console\Commands;

use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class RegisterAdminDashboardPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:register-admin-dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registers all dashboard API routes and CondoLocationController as permissions for the admin role (ID 1).';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Registering admin dashboard permissions...');

        try {
            // Get the admin role
            $adminRole = UserRole::find(1);

            if (!$adminRole) {
                $this->error('Admin role (ID 1) not found.');
                return Command::FAILURE;
            }

            // Make sure the permissions are properly registered
            $controllers = [];

            // Handle permissions properly - check if it's already an array or needs decoding
            $currentPermissions = [];
            if (is_string($adminRole->permissions)) {
                $currentPermissions = json_decode($adminRole->permissions, true) ?? [];
            } elseif (is_array($adminRole->permissions)) {
                $currentPermissions = $adminRole->permissions;
            }
            
            // Initialize controllers with current permissions to avoid losing manually added ones not found in routes
            foreach ($currentPermissions as $perm) {
                $controllers[$perm] = true;
            }

            foreach (Route::getRoutes()->getIterator() as $route) {
                if (strpos($route->uri(), 'api/dashboard') !== false && isset($route->getAction()['controller'])) {
                    $controllerAction = $route->getAction()['controller'];
                    if (is_string($controllerAction)) {
                        // Extract controller class name properly
                        $parts = explode('@', $controllerAction);
                        if (count($parts) > 0) {
                            $controllerClass = $parts[0];
                            // Convert namespace to dot notation as stored in permissions
                            $path = str_replace('\\', '.', $controllerClass);
                            $controllers[$path] = true;
                        }
                    }
                }
            }

            // Make sure the CondoLocationController is included
            // Note: The original middleware used 'App.Http.Controllers.Api.Dashboard.Admin.CondoLocationController'
            // Ensure this matches how it's represented if discovered via routes, or add it explicitly if it's a special case.
            $condoLocationControllerPath = 'App.Http.Controllers.Api.Dashboard.Admin.CondoLocationController';
            $controllers[$condoLocationControllerPath] = true;

            // Update the admin role permissions
            $newPermissionsArray = array_keys($controllers);
            sort($newPermissionsArray); // Sort for consistency

            $adminRole->permissions = json_encode($newPermissionsArray);
            
            if ($adminRole->save()) {
                $this->info('Admin dashboard permissions successfully registered.');
                $this->info(count($newPermissionsArray) . ' permissions set.');
                Log::info('Admin dashboard permissions registered via command.', ['permissions_count' => count($newPermissionsArray)]);
            } else {
                $this->error('Failed to save admin role permissions.');
                Log::error('Failed to save admin role permissions via command.');
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Error registering admin dashboard permissions: ' . $e->getMessage());
            Log::error('RegisterAdminDashboardPermissions command error: ' . $e->getMessage(), ['exception' => $e]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
