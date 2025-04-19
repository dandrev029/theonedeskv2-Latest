<?php

namespace App\Http\Controllers\Api\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FixDepartmentsController extends Controller
{
    /**
     * Create missing departments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fixDepartments(Request $request): JsonResponse
    {
        try {
            // Check if departments table exists
            if (!Schema::hasTable('departments')) {
                Log::error('Departments table does not exist');
                return response()->json([
                    'success' => false,
                    'message' => 'Departments table does not exist'
                ], 500);
            }

            // Get departments from request or use defaults
            $departmentsToCreate = $request->input('departments', [
                ['name' => 'DASMA General Helpdesk', 'public' => true, 'all_agents' => true],
                ['name' => 'CAMPA General Helpdesk', 'public' => true, 'all_agents' => true],
                ['name' => 'Dasma WiFi HELPDESK', 'public' => true, 'all_agents' => true],
                ['name' => 'Campa WiFi Helpdesk', 'public' => true, 'all_agents' => true]
            ]);

            $created = [];
            $existing = [];

            foreach ($departmentsToCreate as $deptData) {
                // Check if department already exists
                $existingDept = Department::where('name', $deptData['name'])->first();
                
                if (!$existingDept) {
                    // Create the department
                    $department = new Department();
                    $department->name = $deptData['name'];
                    $department->public = $deptData['public'] ?? true;
                    $department->all_agents = $deptData['all_agents'] ?? true;
                    $department->save();
                    
                    $created[] = $department->name;
                } else {
                    $existing[] = $existingDept->name;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Departments processed successfully',
                'created' => $created,
                'existing' => $existing
            ]);
        } catch (\Exception $e) {
            Log::error('Error fixing departments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fixing departments: ' . $e->getMessage()
            ], 500);
        }
    }
}
