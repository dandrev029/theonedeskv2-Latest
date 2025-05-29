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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketConcernController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $user = auth()->user();
        $userDepartments = $user->departments()->pluck('departments.id')->toArray(); // Ensure we get IDs from the pivot table correctly
        $userCondoLocationId = $user->condo_location_id;

        $query = TicketConcern::with(['assignedUser', 'department'])
            ->withCount('tickets')
            ->where(function ($q) use ($userDepartments, $userCondoLocationId) {
                if (!empty($userDepartments)) {
                    $q->whereIn('department_id', $userDepartments);
                }
                // Assuming TicketConcern might also be linked to a condo_location_id directly or via its department.
                // If TicketConcern has a direct condo_location_id:
                // $q->orWhere('condo_location_id', $userCondoLocationId);

                // If TicketConcern is linked to CondoLocation via Department,
                // and Department model has a condo_location_id,
                // we might need a more complex query or ensure departments are already filtered by condo location.
                // For now, focusing on department_id and a potential direct condo_location_id on User.
                // If the requirement is that a user sees concerns from their department OR their condo location (if not department specific)
                // and TicketConcern has a condo_location_id field.
                if ($userCondoLocationId) {
                    // This part assumes TicketConcern has a 'condo_location_id' field.
                    // If not, this 'orWhere' might need adjustment based on how condo locations relate to ticket concerns.
                    // For example, if ticket concerns are only tied to departments, and departments are tied to condo locations.
                    // The current user model has `condo_location_id`. We need to see how `TicketConcern` relates to `CondoLocation`.
                    // Let's assume for now that a TicketConcern can also have a `condo_location_id`.
                    // $q->orWhere('ticket_concerns.condo_location_id', $userCondoLocationId);

                    // A more robust way, if concerns are strictly departmental but departments can be location-specific:
                    // Query departments associated with the user's condo_location_id
                    $departmentsInUserCondo = Department::where('condo_location_id', $userCondoLocationId)->pluck('id')->toArray();
                    if (!empty($departmentsInUserCondo)) {
                        // Add these departments to the list of allowed departments
                        $allowedDepartmentIds = array_unique(array_merge($userDepartments, $departmentsInUserCondo));
                        if (!empty($allowedDepartmentIds)) {
                             $q->whereIn('department_id', $allowedDepartmentIds);
                        } else {
                            // If user has a condo location but no departments are explicitly assigned to it,
                            // and no departments are assigned to the user directly, this might result in no concerns.
                            // This logic branch might need refinement based on business rules for users with
                            // a condo_location_id but no specific department assignments.
                            // For now, if no allowed departments, it will correctly show nothing.
                        }
                    } else if (empty($userDepartments)) {
                        // If user has a condo location, but that location has no departments,
                        // AND the user is not part of any other department, they see nothing.
                        // To prevent an empty whereIn clause if both are empty.
                         $q->whereRaw('1 = 0'); // No results if no departments and no condo-specific departments
                    }
                } elseif (empty($userDepartments)) {
                    // If user has no departments and no condo location, they see nothing.
                    $q->whereRaw('1 = 0'); // No results if no departments and no condo location
                }
            });

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
        return TicketConcernSelectResource::collection(TicketConcern::where('status', true)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $ticketConcern = new TicketConcern();
        $ticketConcern->name = $validatedData['name'];
        $ticketConcern->status = $validatedData['status'] ?? true; // Default to true if not provided
        // $ticketConcern->condo_location_id = $validatedData['condo_location_id'] ?? null; // Removed as column likely doesn't exist on ticket_concerns table

        $actualDepartmentId = null;
        $actualAssignedToUserId = $validatedData['assigned_to'] ?? null;

        if ($actualAssignedToUserId) {
            $user = User::find($actualAssignedToUserId);
            // The StoreRequest validation ensures user exists and has at least one department
            if ($user && $user->departments()->exists()) {
                // If user is in multiple departments, we'll use the first one.
                // This logic might need adjustment if a specific department selection is required.
                $actualDepartmentId = $user->departments()->first()->id;
            } else {
                // This case should ideally be prevented by StoreRequest validation.
                return response()->json(['message' => __('Invalid user or user is not assigned to any department.')], 422);
            }
        } elseif (isset($validatedData['department_id'])) {
            // This department_id comes from selecting a department queue
            $actualDepartmentId = $validatedData['department_id'];
        }

        if (is_null($actualDepartmentId)) {
            // This should also be prevented by StoreRequest validation (one of them must be chosen)
            return response()->json(['message' => __('A department must be determined for the concern.')], 422);
        }

        $ticketConcern->department_id = $actualDepartmentId;
        $ticketConcern->assigned_to = $actualAssignedToUserId; // Assuming 'assigned_to' on model is for user_id

        if ($ticketConcern->save()) {
            return response()->json(['message' => __('Data saved correctly'), 'ticket_concern' => new TicketConcernResource($ticketConcern->load(['assignedUser', 'department']))]);
        }

        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  TicketConcern  $ticketConcern
     * @return TicketConcernResource
     */
    public function show(TicketConcern $ticketConcern): TicketConcernResource
    {
        return new TicketConcernResource($ticketConcern->load('assignedUser', 'department'));
    }

    /**
     * Get tickets associated with a specific concern.
     *
     * @param  TicketConcern  $ticketConcern
     * @param  Request  $request
     * @return JsonResponse
     */
    public function tickets(TicketConcern $ticketConcern, Request $request): JsonResponse
    {
        try {
            $sort = json_decode($request->get('sort', json_encode(['order' => 'desc', 'column' => 'created_at'], JSON_THROW_ON_ERROR)), true, 512, JSON_THROW_ON_ERROR);

            $query = $ticketConcern->tickets()
                ->with(['user', 'agent', 'status', 'priority', 'department', 'concern', 'condoLocation'])
                ->orderBy($sort['column'], $sort['order']);

            $tickets = $query->paginate((int) $request->get('perPage', 15));

            return response()->json([
                'data' => \App\Http\Resources\Ticket\TicketListResource::collection($tickets->items()),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'last_page' => $tickets->lastPage(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error("Error fetching tickets for concern {$ticketConcern->id}: " . $e->getMessage());
            return response()->json([
                'message' => __('Error fetching tickets'),
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                    'last_page' => 1,
                    'from' => null,
                    'to' => null
                ]
            ], 500);
        }
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
        $validatedData = $request->validated();

        $ticketConcern->name = $validatedData['name'];
        $ticketConcern->status = $validatedData['status']; // 'required' in UpdateRequest, so it will be present
        // $ticketConcern->condo_location_id = $validatedData['condo_location_id'] ?? null; // Removed as column likely doesn't exist on ticket_concerns table

        $actualDepartmentId = null;
        $actualAssignedToUserId = $validatedData['assigned_to_user_id'] ?? null;

        if ($actualAssignedToUserId) {
            $user = User::find($actualAssignedToUserId);
            // UpdateRequest validation ensures user exists and has a department_id
            if ($user && $user->department_id) {
                $actualDepartmentId = $user->department_id;
            } else {
                return response()->json(['message' => __('Invalid user or user department setup for update.')], 422);
            }
        } elseif (isset($validatedData['department_id'])) {
            // This department_id comes from selecting a department queue
            $actualDepartmentId = $validatedData['department_id'];
        } else {
             // If neither is provided, but validation requires one, this path shouldn't be hit.
             // However, if condo_location_id is the only thing changing and assignment is cleared,
             // assigned_to_user_id and department_id might both be null in payload if allowed by rules (e.g. if not required_without)
             // The current rules (required_without) ensure one is present.
            return response()->json(['message' => __('A department must be determined for the concern for update.')], 422);
        }
        
        $ticketConcern->department_id = $actualDepartmentId;
        // If actualAssignedToUserId is null (meaning a department queue was chosen or assignment cleared),
        // set assigned_to to null.
        $ticketConcern->assigned_to = $actualAssignedToUserId;


        if ($ticketConcern->save()) {
            return response()->json(['message' => __('Data updated correctly'), 'ticket_concern' => new TicketConcernResource($ticketConcern->load(['assignedUser', 'department']))]);
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
        $departmentId = request()->input('department_id');

        // Get all roles with dashboard access
        $dashboardRoles = UserRole::where('dashboard_access', true)->pluck('id');

        $usersQuery = User::whereIn('role_id', $dashboardRoles)
            ->where('status', true);

        if ($departmentId) {
            $usersQuery->whereHas('departments', function ($query) use ($departmentId) {
                $query->where('departments.id', $departmentId);
            });
        }
        // If no department_id is provided, it lists all dashboard users.
        // Alternatively, if no department_id, we could restrict to users in the admin's accessible departments.
        // For now, this matches the previous behavior if department_id is omitted,
        // but filters if department_id is present.

        $users = $usersQuery->orderBy('name')->get();

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
            if (!\Schema::hasTable('departments')) {
                \Log::error('Departments table does not exist');
                throw new \Exception('Departments table does not exist');
            }

            // Get all departments ordered by name
            $departments = Department::orderBy('name')->get();

            // Debug: Log the number of departments found
            \Log::info('Departments found: ' . $departments->count());

            // If no departments found, create default ones
            if ($departments->count() === 0) {
                \Log::warning('No departments found, creating default departments');

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
            \Log::error('Error fetching departments: ' . $e->getMessage());

            // Create default departments in the catch block as a last resort
            try {
                if (\Schema::hasTable('departments')) {
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
                \Log::error('Error creating fallback departments: ' . $innerException->getMessage());
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
                \Log::warning("Department with ID {$departmentId} not found");
                return response()->json([
                    'message' => __('Department not found'),
                    'concerns' => []
                ], 404);
            }

            $concerns = TicketConcern::where('department_id', $departmentId)
                ->where('status', true)
                ->orderBy('name')
                ->get();

            return TicketConcernSelectResource::collection($concerns);
        } catch (\Exception $e) {
            \Log::error("Error fetching concerns for department {$departmentId}: " . $e->getMessage());
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
            if (!\Schema::hasTable('departments')) {
                \Log::error('Departments table does not exist');
                throw new \Exception('Departments table does not exist');
            }

            // Get all departments ordered by name
            $departments = Department::orderBy('name')->get();

            // Debug: Log the number of departments found
            \Log::info('Public departments found: ' . $departments->count());

            // If no departments found, create default ones
            if ($departments->count() === 0) {
                \Log::warning('No public departments found, creating default departments');

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
            \Log::error('Error fetching public departments: ' . $e->getMessage());

            // Create default departments in the catch block as a last resort
            try {
                if (\Schema::hasTable('departments')) {
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
                \Log::error('Error creating fallback departments: ' . $innerException->getMessage());
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

    /**
     * Get departments accessible by the authenticated user.
     *
     * @return JsonResponse
     */
    public function userAccessibleDepartments(): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => __('User not authenticated')], 401);
            }

            $userDepartmentIds = $user->departments()->pluck('departments.id')->toArray();
            $departmentsFromCondo = [];

            if ($user->condo_location_id) {
                $departmentsFromCondo = Department::where('condo_location_id', $user->condo_location_id)
                                                ->pluck('id')->toArray();
            }

            $accessibleDepartmentIds = array_unique(array_merge($userDepartmentIds, $departmentsFromCondo));

            if (empty($accessibleDepartmentIds)) {
                 // If user has no specific departments and their condo location has no departments (or no condo location)
                 // Should they see all public departments or a restricted list?
                 // For now, let's return only explicitly accessible ones.
                 // If the business rule is to fall back to all assignable departments if none are directly linked,
                 // then this logic would need to change, perhaps to fetch all departments marked as 'public' or similar.
                 // Based on strict segregation, if no departments are linked, they can't assign to any.
                $departments = collect();
            } else {
                $departments = Department::whereIn('id', $accessibleDepartmentIds)->orderBy('name')->get();
            }

            return response()->json([
                'data' => DepartmentSelectResource::collection($departments)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching user accessible departments: ' . $e->getMessage());
            return response()->json(['message' => __('Error fetching departments')], 500);
        }
    }
}
