<?php

// This is a simple script to fix departments from the browser
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
        echo json_encode([
            'success' => false,
            'message' => 'Departments table does not exist'
        ]);
        exit;
    }

    // Create default departments if they don't exist
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
        } else {
            $existing[] = $existingDept->name;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Departments processed successfully',
        'created' => $created,
        'existing' => $existing
    ]);
} catch (Exception $e) {
    Log::error('Error fixing departments: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fixing departments: ' . $e->getMessage()
    ]);
}
