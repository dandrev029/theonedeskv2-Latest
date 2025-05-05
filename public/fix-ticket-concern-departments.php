<?php

// This is a simple script to fix departments for ticket concerns
// It will create the default departments if they don't exist

// Bootstrap the Laravel application
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Department;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

header('Content-Type: application/json');

try {
    // Check if departments table exists
    if (!Schema::hasTable('departments')) {
        throw new Exception('Departments table does not exist');
    }

    // Default departments to create
    $defaultDepts = [
        ['name' => 'DASMA General Helpdesk', 'public' => true, 'all_agents' => true],
        ['name' => 'CAMPA General Helpdesk', 'public' => true, 'all_agents' => true],
        ['name' => 'Dasma WiFi HELPDESK', 'public' => true, 'all_agents' => true],
        ['name' => 'Campa WiFi Helpdesk', 'public' => true, 'all_agents' => true]
    ];

    $created = [];
    $existing = [];

    foreach ($defaultDepts as $dept) {
        // Check if department already exists
        $existingDept = Department::where('name', $dept['name'])->first();

        if (!$existingDept) {
            // Create the department
            $department = new Department();
            $department->name = $dept['name'];
            $department->public = $dept['public'] ?? true;
            $department->all_agents = $dept['all_agents'] ?? true;
            $department->save();

            $created[] = $department->name;
            Log::info("Created department: {$department->name}");
        } else {
            $existing[] = $existingDept->name;
            Log::info("Department already exists: {$existingDept->name}");
        }
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Departments fixed successfully',
        'created' => $created,
        'existing' => $existing
    ]);

} catch (Exception $e) {
    // Log the error
    Log::error('Error fixing departments: ' . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error fixing departments: ' . $e->getMessage()
    ]);
}
