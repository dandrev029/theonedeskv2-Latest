<?php
/**
 * Fix Departments Script
 * 
 * This script creates default departments if they don't exist.
 * It can be run directly from the browser or via command line.
 */

// Bootstrap the Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Set up response
$response = [
    'success' => false,
    'message' => '',
    'departments_created' => [],
    'errors' => []
];

try {
    // Check if departments table exists
    if (!Schema::hasTable('departments')) {
        throw new Exception('Departments table does not exist');
    }

    // Get all departments
    $departments = Department::all();
    
    // If no departments found, create default ones
    if ($departments->count() === 0) {
        // Create default departments
        $defaultDepts = [
            ['name' => 'DASMA General Helpdesk', 'public' => true, 'all_agents' => true],
            ['name' => 'CAMPA General Helpdesk', 'public' => true, 'all_agents' => true],
            ['name' => 'Dasma WiFi HELPDESK', 'public' => true, 'all_agents' => true],
            ['name' => 'Campa WiFi Helpdesk', 'public' => true, 'all_agents' => true]
        ];

        $createdDepts = [];
        foreach ($defaultDepts as $dept) {
            $department = Department::create($dept);
            $createdDepts[] = [
                'id' => $department->id,
                'name' => $department->name
            ];
        }

        $response['success'] = true;
        $response['message'] = 'Default departments created successfully';
        $response['departments_created'] = $createdDepts;
    } else {
        $response['success'] = true;
        $response['message'] = 'Departments already exist. No action needed.';
        $response['departments_count'] = $departments->count();
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['errors'][] = $e->getMessage();
}

// Output response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
