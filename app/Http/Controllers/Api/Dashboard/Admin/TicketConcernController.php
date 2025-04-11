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

class TicketConcernController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return TicketConcernResource::collection(TicketConcern::with('assignedUser')->get());
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
        $request->validated();
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
    public function show(TicketConcern $ticketConcern): TicketConcernResource
    {
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
     * @return AnonymousResourceCollection
     */
    public function departments(): AnonymousResourceCollection
    {
        // Get all departments ordered by name
        $departments = Department::orderBy('name')->get();

        // Debug: Log the number of departments found
        \Log::info('Departments found: ' . $departments->count());

        // Return the departments as a resource collection
        return DepartmentSelectResource::collection($departments);
    }

    /**
     * Get concerns by department.
     *
     * @param int $departmentId
     * @return AnonymousResourceCollection
     */
    public function concernsByDepartment(int $departmentId): AnonymousResourceCollection
    {
        $concerns = TicketConcern::where('department_id', $departmentId)
            ->where('status', true)
            ->orderBy('name')
            ->get();
        return TicketConcernSelectResource::collection($concerns);
    }
}
