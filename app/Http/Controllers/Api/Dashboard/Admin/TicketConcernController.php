<?php

namespace App\Http\Controllers\Api\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Admin\TicketConcern\StoreRequest;
use App\Http\Requests\Dashboard\Admin\TicketConcern\UpdateRequest;
use App\Http\Resources\Department\DepartmentSelectResource;
use App\Http\Resources\TicketConcern\TicketConcernResource;
use App\Http\Resources\TicketConcern\TicketConcernSelectResource;
use App\Http\Resources\User\UserSelectResource;
use App\Models\Department;
use App\Models\TicketConcern;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TicketConcernController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $query = TicketConcern::with(['assignedUser', 'department'])
            ->withCount('tickets');

        // Get the authenticated user
        $user = Auth::guard('sanctum')->user();

        // Check if user has admin role (role_id = 1) or has specific department restrictions
        $isAdmin = $user && $user->role_id === 1;

        // If user is not admin and has dashboard permission, restrict to their departments
        if (!$isAdmin && $user && $user->userRole->dashboard_access) {
            // Get user's departments
            $userDepartments = $user->departments;
            $userDepartmentIds = $userDepartments->pluck('id')->toArray();

            // If user has departments, restrict to those departments
            if (!empty($userDepartmentIds)) {
                $query->whereIn('department_id', $userDepartmentIds);
            }
        }

        // Apply search filter
        if (request()->has('search') && !empty(request('search'))) {
            $search = '%' . request('search') . '%';
            $query->where('name', 'like', $search);
        }

        // Apply department filter
        if (request()->has('department_id') && !empty(request('department_id'))) {
            $query->where('department_id', request('department_id'));
        }

        // Apply status filter
        if (request()->has('status') && request('status') !== '') {
            $status = request('status') === 'true' || request('status') === '1';
            $query->where('status', $status);
        }

        // Get results
        $ticketConcerns = $query->get();

        return TicketConcernResource::collection($ticketConcerns);
    }

    /**
     * Display a listing of the resource for select.
     *
     * @return AnonymousResourceCollection
     */
    public function select(): AnonymousResourceCollection
    {
        $query = TicketConcern::where('status', true);

        // Get the authenticated user
        $user = Auth::guard('sanctum')->user();

        // Check if user has admin role (role_id = 1) or has specific department restrictions
        $isAdmin = $user && $user->role_id === 1;

        // If user is not admin and has dashboard permission, restrict to their departments
        if (!$isAdmin && $user && $user->userRole->dashboard_access) {
            // Get user's departments
            $userDepartments = $user->departments;
            $userDepartmentIds = $userDepartments->pluck('id')->toArray();

            // If user has departments, restrict to those departments
            if (!empty($userDepartmentIds)) {
                $query->whereIn('department_id', $userDepartmentIds);
            }
        }

        return TicketConcernSelectResource::collection($query->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $request->validated();

        // Get the authenticated user
        $user = Auth::guard('sanctum')->user();

        // Check if user has admin role (role_id = 1)
        $isAdmin = $user && $user->role_id === 1;

        // If user is not admin, check if they have access to the selected department
        if (!$isAdmin && $user && $user->userRole->dashboard_access && $request->get('department_id')) {
            // Get user's departments
            $userDepartments = $user->departments;
            $userDepartmentIds = $userDepartments->pluck('id')->toArray();

            // If user doesn't have access to this department, return forbidden
            if (!empty($userDepartmentIds) && !in_array($request->get('department_id'), $userDepartmentIds)) {
                return response()->json([
                    'message' => __('You do not have permission to create ticket concerns for this department'),
                ], 403);
            }
        }

        $ticketConcern = new TicketConcern();
        $ticketConcern->name = $request->get('name');
        $ticketConcern->status = $request->get('status', true);
        $ticketConcern->assigned_to = $request->get('assigned_to');
        $ticketConcern->department_id = $request->get('department_id');

        if ($ticketConcern->save()) {
            return response()->json(['message' => __('Data saved correctly'), 'ticket_concern' => new TicketConcernResource($ticketConcern)]);
        }

        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  TicketConcern  $ticketConcern
     * @return TicketConcernResource
     */
    public function show(TicketConcern $ticketConcern): JsonResponse|TicketConcernResource
    {
        // Get the authenticated user
        $user = Auth::guard('sanctum')->user();

        // Check if user has admin role (role_id = 1)
        $isAdmin = $user && $user->role_id === 1;

        // If user is not admin, check if they have access to the department
        if (!$isAdmin && $user && $user->userRole->dashboard_access) {
            // Get user's departments
            $userDepartments = $user->departments;
            $userDepartmentIds = $userDepartments->pluck('id')->toArray();

            // If user doesn't have access to this department, return forbidden
            if (!empty($userDepartmentIds) && !in_array($ticketConcern->department_id, $userDepartmentIds)) {
                return response()->json([
                    'message' => __('You do not have permission to view ticket concerns from this department'),
                ], 403);
            }
        }

        return new TicketConcernResource($ticketConcern->load('assignedUser'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  TicketConcern  $ticketConcern
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, TicketConcern $ticketConcern): JsonResponse
    {
        $request->validated();

        // Get the authenticated user
        $user = Auth::guard('sanctum')->user();

        // Check if user has admin role (role_id = 1)
        $isAdmin = $user && $user->role_id === 1;

        // If user is not admin, check if they have access to the current department
        if (!$isAdmin && $user && $user->userRole->dashboard_access) {
            // Get user's departments
            $userDepartments = $user->departments;
            $userDepartmentIds = $userDepartments->pluck('id')->toArray();

            // Check if user has access to the current department
            if (!empty($userDepartmentIds) && !in_array($ticketConcern->department_id, $userDepartmentIds)) {
                return response()->json([
                    'message' => __('You do not have permission to edit ticket concerns from this department'),
                ], 403);
            }

            // If department is being changed, check if user has access to the new department
            if ($request->get('department_id') && $request->get('department_id') != $ticketConcern->department_id) {
                if (!empty($userDepartmentIds) && !in_array($request->get('department_id'), $userDepartmentIds)) {
                    return response()->json([
                        'message' => __('You do not have permission to move ticket concerns to this department'),
                    ], 403);
                }
            }
        }

        $ticketConcern->name = $request->get('name');
        $ticketConcern->status = $request->get('status');
        $ticketConcern->assigned_to = $request->get('assigned_to');
        $ticketConcern->department_id = $request->get('department_id');

        if ($ticketConcern->save()) {
            return response()->json(['message' => __('Data updated correctly'), 'ticket_concern' => new TicketConcernResource($ticketConcern->load('assignedUser'))]);
        }

        return response()->json(['message' => __('An error occurred while updating data')], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  TicketConcern  $ticketConcern
     * @return JsonResponse
     */
    public function destroy(TicketConcern $ticketConcern): JsonResponse
    {
        // Get the authenticated user
        $user = Auth::guard('sanctum')->user();

        // Check if user has admin role (role_id = 1)
        $isAdmin = $user && $user->role_id === 1;

        // If user is not admin, check if they have access to the department
        if (!$isAdmin && $user && $user->userRole->dashboard_access) {
            // Get user's departments
            $userDepartments = $user->departments;
            $userDepartmentIds = $userDepartments->pluck('id')->toArray();

            // If user doesn't have access to this department, return forbidden
            if (!empty($userDepartmentIds) && !in_array($ticketConcern->department_id, $userDepartmentIds)) {
                return response()->json([
                    'message' => __('You do not have permission to delete ticket concerns from this department'),
                ], 403);
            }
        }

        if ($ticketConcern->tickets()->count() > 0) {
            return response()->json(['message' => __('Cannot delete concern with associated tickets')], 422);
        }

        if ($ticketConcern->delete()) {
            return response()->json(['message' => __('Data deleted correctly')]);
        }

        return response()->json(['message' => __('An error occurred while deleting data')], 500);
    }

    /**
     * Get users with dashboard access for assignment.
     *
     * @return AnonymousResourceCollection
     */
    public function dashboardUsers(): AnonymousResourceCollection
    {
        // Get all roles with dashboard access
        $dashboardRoles = UserRole::where('dashboard_access', true)->pluck('id');

        // Get all users with those roles
        $users = User::whereIn('role_id', $dashboardRoles)
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return UserSelectResource::collection($users);
    }

    /**
     * Get departments for the dropdown.
     *
     * @return JsonResponse
     */
    public function departments(): JsonResponse
    {
        try {
            // Check if departments table exists
            if (!Schema::hasTable('departments')) {
                Log::error('Departments table does not exist');
                throw new \Exception('Departments table does not exist');
            }

            // Get the authenticated user
            $user = Auth::guard('sanctum')->user();

            // Check if user has admin role (role_id = 1)
            $isAdmin = $user && $user->role_id === 1;

            // If user is admin or not authenticated, get all departments
            if ($isAdmin || !$user || !$user->userRole->dashboard_access) {
                $departments = Department::orderBy('name')->get();
            } else {
                // Get only departments that the user has access to
                $userDepartments = $user->departments;
                $departments = Department::whereIn('id', $userDepartments->pluck('id'))->orderBy('name')->get();
            }

            // Debug: Log the number of departments found
            Log::info('Departments found: ' . $departments->count());

            // If no departments found, create default ones
            if ($departments->count() === 0) {
                Log::warning('No departments found, creating default departments');

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
                    $createdDepts[] = $department;
                }

                // Get all departments again after creating new ones
                $departments = Department::orderBy('name')->get();
                if ($departments->count() > 0) {
                    return response()->json([
                        'data' => DepartmentSelectResource::collection($departments)
                    ]);
                }

                return response()->json([
                    'data' => DepartmentSelectResource::collection(collect($createdDepts))
                ]);
            }

            // Return the departments as a resource collection with data wrapper
            return response()->json([
                'data' => DepartmentSelectResource::collection($departments)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching departments: ' . $e->getMessage());

            // Create default departments in the catch block as a last resort
            try {
                if (Schema::hasTable('departments')) {
                    // Check if any departments exist first
                    $existingCount = Department::count();
                    if ($existingCount > 0) {
                        $existingDepts = Department::orderBy('name')->get();
                        return response()->json([
                            'data' => DepartmentSelectResource::collection($existingDepts)
                        ]);
                    }

                    // Create default departments as a last resort
                    $defaultDepts = [
                        ['name' => 'DASMA General Helpdesk', 'public' => true, 'all_agents' => true],
                        ['name' => 'CAMPA General Helpdesk', 'public' => true, 'all_agents' => true],
                        ['name' => 'Dasma WiFi HELPDESK', 'public' => true, 'all_agents' => true],
                        ['name' => 'Campa WiFi Helpdesk', 'public' => true, 'all_agents' => true]
                    ];

                    $createdDepts = [];
                    foreach ($defaultDepts as $dept) {
                        $department = Department::create($dept);
                        $createdDepts[] = $department;
                    }

                    return response()->json([
                        'data' => DepartmentSelectResource::collection(collect($createdDepts))
                    ]);
                }
            } catch (\Exception $innerException) {
                Log::error('Error creating fallback departments: ' . $innerException->getMessage());
            }

            // Return fallback departments instead of error as a last resort
            return response()->json([
                'data' => [
                    ['id' => 'dasma_general', 'name' => 'DASMA General Helpdesk'],
                    ['id' => 'campa_general', 'name' => 'CAMPA General Helpdesk'],
                    ['id' => 'dasma_wifi', 'name' => 'Dasma WiFi HELPDESK'],
                    ['id' => 'campa_wifi', 'name' => 'Campa WiFi Helpdesk']
                ]
            ]);
        }
    }

    /**
     * Get concerns by department.
     *
     * @param int $departmentId
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function concernsByDepartment(int $departmentId)
    {
        try {
            // First check if the department exists
            $department = Department::find($departmentId);

            if (!$department) {
                Log::warning("Department with ID {$departmentId} not found");
                return response()->json([
                    'message' => __('Department not found'),
                    'concerns' => []
                ], 404);
            }

            // Get the authenticated user
            $user = Auth::guard('sanctum')->user();

            // Check if user has admin role (role_id = 1) or has specific department restrictions
            $isAdmin = $user && $user->role_id === 1;

            // If user is not admin and has dashboard permission, check if they have access to this department
            if (!$isAdmin && $user && $user->userRole->dashboard_access) {
                // Get user's departments
                $userDepartments = $user->departments;
                $userDepartmentIds = $userDepartments->pluck('id')->toArray();

                // If user doesn't have access to this department, return forbidden
                if (!empty($userDepartmentIds) && !in_array($departmentId, $userDepartmentIds)) {
                    return response()->json([
                        'message' => __('You do not have access to this department'),
                        'concerns' => []
                    ], 403);
                }
            }

            $concerns = TicketConcern::where('department_id', $departmentId)
                ->where('status', true)
                ->orderBy('name')
                ->get();

            return TicketConcernSelectResource::collection($concerns);
        } catch (\Exception $e) {
            Log::error("Error fetching concerns for department {$departmentId}: " . $e->getMessage());
            return response()->json([
                'message' => __('Error fetching concerns'),
                'concerns' => []
            ], 500);
        }
    }

    /**
     * Public endpoint to get departments for the dropdown without authentication.
     *
     * @return JsonResponse
     */
    public function publicDepartments(): JsonResponse
    {
        try {
            // Check if departments table exists
            if (!Schema::hasTable('departments')) {
                Log::error('Departments table does not exist');
                throw new \Exception('Departments table does not exist');
            }

            // Get the authenticated user
            $user = Auth::guard('sanctum')->user();

            // Check if user has admin role (role_id = 1)
            $isAdmin = $user && $user->role_id === 1;

            // If user is admin or not authenticated, get all departments
            if ($isAdmin || !$user || !$user->userRole->dashboard_access) {
                $departments = Department::orderBy('name')->get();
            } else {
                // Get only departments that the user has access to
                $userDepartments = $user->departments;
                $departments = Department::whereIn('id', $userDepartments->pluck('id'))->orderBy('name')->get();
            }

            // Debug: Log the number of departments found
            Log::info('Public departments found: ' . $departments->count());

            // If no departments found, create default ones
            if ($departments->count() === 0) {
                Log::warning('No public departments found, creating default departments');

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
                    $createdDepts[] = $department;
                }

                // Get all departments again after creating new ones
                $departments = Department::orderBy('name')->get();
                if ($departments->count() > 0) {
                    return response()->json([
                        'data' => DepartmentSelectResource::collection($departments)
                    ]);
                }

                return response()->json([
                    'data' => DepartmentSelectResource::collection(collect($createdDepts))
                ]);
            }

            // Return the departments as a resource collection with data wrapper
            return response()->json([
                'data' => DepartmentSelectResource::collection($departments)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching public departments: ' . $e->getMessage());

            // Create default departments in the catch block as a last resort
            try {
                if (Schema::hasTable('departments')) {
                    // Check if any departments exist first
                    $existingCount = Department::count();
                    if ($existingCount > 0) {
                        $existingDepts = Department::orderBy('name')->get();
                        return response()->json([
                            'data' => DepartmentSelectResource::collection($existingDepts)
                        ]);
                    }

                    // Create default departments as a last resort
                    $defaultDepts = [
                        ['name' => 'DASMA General Helpdesk', 'public' => true, 'all_agents' => true],
                        ['name' => 'CAMPA General Helpdesk', 'public' => true, 'all_agents' => true],
                        ['name' => 'Dasma WiFi HELPDESK', 'public' => true, 'all_agents' => true],
                        ['name' => 'Campa WiFi Helpdesk', 'public' => true, 'all_agents' => true]
                    ];

                    $createdDepts = [];
                    foreach ($defaultDepts as $dept) {
                        $department = Department::create($dept);
                        $createdDepts[] = $department;
                    }

                    return response()->json([
                        'data' => DepartmentSelectResource::collection(collect($createdDepts))
                    ]);
                }
            } catch (\Exception $innerException) {
                Log::error('Error creating fallback departments: ' . $innerException->getMessage());
            }

            // Return fallback departments instead of error as a last resort
            return response()->json([
                'data' => [
                    ['id' => 1, 'name' => 'DASMA General Helpdesk'],
                    ['id' => 2, 'name' => 'CAMPA General Helpdesk'],
                    ['id' => 3, 'name' => 'Dasma WiFi HELPDESK'],
                    ['id' => 4, 'name' => 'Campa WiFi Helpdesk']
                ]
            ]);
        }
    }
}
