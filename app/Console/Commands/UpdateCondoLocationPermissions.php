<?php

namespace App\Console\Commands;

use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class UpdateCondoLocationPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:update-condo-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update permissions to include Condo Locations controller';

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
        $this->info('Updating permissions for Condo Locations...');
        
        // Get all user roles
        $roles = UserRole::all();
        
        foreach ($roles as $role) {
            $this->info("Processing role: {$role->name}");
            
            // Get current permissions
            $permissions = json_decode((string) $role->permissions, true) ?? [];
            
            // Add the CondoLocationController to permissions
            $condoLocationController = 'App.Http.Controllers.Api.Dashboard.Admin.CondoLocationController';
            
            if ($role->id === 1 || in_array($condoLocationController, $permissions)) {
                $this->info("Adding {$condoLocationController} to permissions");
                
                // For admin role (id=1), we need to make sure all controllers are included
                if ($role->id === 1) {
                    $controllers = [];
                    
                    foreach (Route::getRoutes()->getIterator() as $route) {
                        if (strpos($route->uri, 'api/dashboard') !== false && isset($route->action['controller'])) {
                            $path = str_replace('\\', '.', explode('@', str_replace($route->action['controller'].'\\', '', $route->action['controller']))[0]);
                            $controllers[$path] = true;
                        }
                    }
                    
                    // Make sure the CondoLocationController is included
                    $controllers[$condoLocationController] = true;
                    
                    // Update the role permissions
                    $role->permissions = json_encode(array_keys($controllers));
                } else {
                    // For non-admin roles, just add the controller if it's not already there
                    if (!in_array($condoLocationController, $permissions)) {
                        $permissions[] = $condoLocationController;
                        $role->permissions = json_encode($permissions);
                    }
                }
                
                $role->save();
                $this->info("Permissions updated for role: {$role->name}");
            }
        }
        
        $this->info('Permissions update completed!');
        return 0;
    }
}
