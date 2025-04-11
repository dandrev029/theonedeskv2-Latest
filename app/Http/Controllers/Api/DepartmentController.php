<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Get all departments for dropdowns.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get all departments
        $departments = Department::orderBy('name')->get(['id', 'name']);
        
        // Log the number of departments found
        \Log::info('Public departments endpoint called. Found: ' . $departments->count());
        
        // Return as JSON
        return response()->json($departments);
    }
}
