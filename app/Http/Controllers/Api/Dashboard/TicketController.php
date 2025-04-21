<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Api\File\FileController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Ticket\StoreRequest;
use App\Http\Requests\Dashboard\Ticket\TicketReplyRequest;
use App\Http\Requests\File\StoreFileRequest;
use App\Http\Resources\CannedReply\CannedReplyResource;
use App\Http\Resources\Department\DepartmentSelectResource;
use App\Http\Resources\Label\LabelSelectResource;
use App\Http\Resources\Priority\PriorityResource;
use App\Http\Resources\Status\StatusResource;
use App\Http\Resources\Ticket\TicketListResource;
use App\Http\Resources\Ticket\TicketManageResource;
use App\Http\Resources\TicketConcern\TicketConcernSelectResource;
use App\Http\Resources\User\UserDetailsResource;
use App\Models\CannedReply;
use App\Models\CondoLocation;
use App\Models\Department;
use App\Models\Label;
use App\Models\Priority;
use App\Models\Setting;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketConcern;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\UserRole;
use App\Notifications\Ticket\NewTicketFromAgent;
use App\Notifications\Ticket\NewTicketReplyFromAgentToUser;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;
use Throwable;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $sort = json_decode($request->get('sort', json_encode(['order' => 'asc', 'column' => 'created_at'], JSON_THROW_ON_ERROR)), true, 512, JSON_THROW_ON_ERROR);
        if ($user->role_id !== 1) {
            $items = Ticket::filter($request->all())
                ->where(function (Builder $query) use ($user) {
                    $query->where('agent_id', $user->id);
                    $query->orWhere('closed_by', $user->id);
                    $query->orWhereIn('department_id', $user->departments()->pluck('id')->toArray());
                    $query->orWhere(function (Builder $query) use ($user) {
                        $departments = array_unique(array_merge($user->departments()->pluck('id')->toArray(), Department::where('all_agents', 1)->pluck('id')->toArray()));
                        $query->whereNull('agent_id');
                        $query->whereIn('department_id', $departments);
                    });
                })
                ->orderBy($sort['column'], $sort['order'])
                ->paginate((int) $request->get('perPage', 10));
        } else {
            $items = Ticket::filter($request->all())
                ->orderBy($sort['column'], $sort['order'])
                ->paginate((int) $request->get('perPage', 10));
        }
        return response()->json([
            'items' => TicketListResource::collection($items->items()),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'perPage' => $items->perPage(),
                'total' => $items->total(),
                'totalPages' => $items->lastPage()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $request->validated();
        $ticket = new Ticket();
        $ticket->uuid = Str::uuid();
        $ticket->subject = $request->get('subject');
        $ticket->concern_id = $request->get('concern_id');
        $ticket->voucher_code = $request->get('voucher_code');
        $ticket->status_id = $request->get('status_id');
        $ticket->priority_id = $request->get('priority_id');
        $ticket->department_id = $request->get('department_id');
        $ticket->user_id = $request->get('user_id');

        if ($request->has('scheduled_visit_at')) {
            $ticket->scheduled_visit_at = $request->get('scheduled_visit_at');
        }

        // Get the user's condo location
        $user = User::find($request->get('user_id'));
        if ($user && $user->condo_location_id) {
            $ticket->condo_location_id = $user->condo_location_id;
        }

        // If the concern has an assigned user, use that user as the agent
        // Otherwise, use the current user
        if ($ticket->concern_id) {
            $concern = TicketConcern::find($ticket->concern_id);
            if ($concern && $concern->assigned_to) {
                $ticket->agent_id = $concern->assigned_to;
            } else {
                $ticket->agent_id = Auth::id();
            }
        } else {
            $ticket->agent_id = Auth::id();
        }
        $ticket->saveOrFail();
        $ticketReply = new TicketReply();
        $ticketReply->user_id = Auth::id();
        $ticketReply->body = $request->get('body');
        if ($ticket->ticketReplies()->save($ticketReply)) {
            if ($request->has('attachments')) {
                $ticketReply->ticketAttachments()->sync(collect($request->get('attachments'))->map(function ($attachment) {
                    return $attachment['id'];
                }));
            }
            try {
                $ticket->user->notify((new NewTicketFromAgent($ticket))->locale(Setting::getDecoded('app_locale')));
            } catch (\Exception $e) {
                // Log the error but don't fail the ticket creation
                \Log::error('Error sending ticket notification: ' . $e->getMessage());
            }
            return response()->json(['message' => __('Data saved correctly'), 'ticket' => new TicketManageResource($ticket)]);
        }
        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  Ticket  $ticket
     * @return JsonResponse
     */
    public function show(Ticket $ticket): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$ticket->verifyUser($user)) {
            return response()->json(['message' => __('You do not have permissions to manage this ticket')], 403);
        }
        return response()->json(new TicketManageResource($ticket));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  TicketReplyRequest  $request
     * @param  Ticket  $ticket
     * @return JsonResponse
     */
    public function reply(Ticket $ticket, TicketReplyRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$ticket->verifyUser($user)) {
            return response()->json(['message' => __('You do not have permissions to manage this ticket')], 403);
        }

        try {
            $request->validated();

            // Ensure user_id is set
            $userId = Auth::id();
            if (!$userId) {
                \Log::warning('No authenticated user found when creating ticket reply. Using default user.');
                $userId = 1; // Default to admin user
            }

            $ticketReply = new TicketReply();
            $ticketReply->user_id = $userId;
            $ticketReply->body = $request->get('body');

            if ($ticket->ticketReplies()->save($ticketReply)) {
                if ($request->has('attachments')) {
                    $ticketReply->ticketAttachments()->sync(collect($request->get('attachments'))->map(function ($attachment) {
                        return $attachment['id'];
                    }));
                }

                $ticket->status_id = $request->get('status_id');
                $ticket->updated_at = Carbon::now();
                $ticket->save();

                // Send notifications with error handling
                try {
                    if ($ticket->user) {
                        $ticket->user->notify((new NewTicketReplyFromAgentToUser($ticket))->locale(Setting::getDecoded('app_locale')));
                    }
                } catch (\Exception $e) {
                    // Log notification error but don't fail the request
                    \Log::error('Error sending ticket reply notification: ' . $e->getMessage());
                }

                return response()->json(['message' => __('Data saved correctly'), 'ticket' => new TicketManageResource($ticket)]);
            }

            return response()->json(['message' => __('An error occurred while saving data')], 500);
        } catch (\Exception $e) {
            \Log::error('Error creating ticket reply: ' . $e->getMessage());
            return response()->json(['message' => __('An error occurred while saving data: ') . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Ticket  $ticket
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Ticket $ticket): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$ticket->verifyUser($user)) {
            return response()->json(['message' => __('You do not have permissions to manage this ticket')], 403);
        }
        $ticket->labels()->sync([]);
        foreach ($ticket->ticketReplies()->get() as $ticketReply) {
            $ticketReply->ticketAttachments()->sync([]);
        }
        $ticket->ticketReplies()->delete();
        if ($ticket->delete()) {
            return response()->json(['message' => 'Data deleted successfully']);
        }
        return response()->json(['message' => __('An error occurred while deleting data')], 500);
    }

    public function filters(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->role_id === 1) {
            $agentList = User::whereIn('role_id', UserRole::whereIn('name', ['admin', 'agent'])->pluck('id'))
                ->orderBy('name')
                ->get();
            $customerList = User::whereIn('role_id', UserRole::whereIn('name', ['user'])->pluck('id'))
                ->orderBy('name')
                ->get();
            $departmentList = Department::orderBy('name')->get();
        } else {
            $agentList = User::whereIn('role_id', UserRole::whereIn('name', ['admin', 'agent'])->pluck('id'))
                ->orderBy('name')
                ->get();
            $customerList = User::whereIn('role_id', UserRole::whereIn('name', ['user'])->pluck('id'))
                ->orderBy('name')
                ->get();
            $departmentList = Department::where(function (Builder $query) use ($user) {
                $query->where('all_agents', 1);
                $query->orWhere(function (Builder $q) use ($user) {
                    $q->whereIn('id', $user->departments()->pluck('id')->toArray());
                });
            })->orderBy('name')->get();
        }

        return response()->json([
            'agents' => UserDetailsResource::collection($agentList),
            'customers' => UserDetailsResource::collection($customerList),
            'departments' => DepartmentSelectResource::collection($departmentList),
            'labels' => LabelSelectResource::collection(Label::all()),
            'statuses' => StatusResource::collection(Status::all()),
            'priorities' => PriorityResource::collection(Priority::orderBy('value')->get()),
        ]);
    }

    /**
     * Get departments filtered by a user's condo location
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function departmentsByUserCondoLocation(Request $request): JsonResponse
    {
        $userId = $request->get('user_id');

        if (!$userId) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $condoLocationId = $user->condo_location_id;

        // If user has a condo location, filter departments that are specific to that location
        if ($condoLocationId) {
            $condoLocation = CondoLocation::find($condoLocationId);

            if ($condoLocation) {
                // Get the condo location name
                $locationName = strtolower($condoLocation->name);

                // Get all departments that belong to this condo location
                // Using a more precise filtering approach:
                // 1. Use exact word boundaries when possible
                // 2. Match department names that explicitly mention the condo location
                $departmentsQuery = Department::where('public', true);

                // If location name contains multiple words, we handle it differently
                $locationWords = explode(' ', $locationName);

                if (count($locationWords) > 1) {
                    // For multi-word locations, match departments containing the full location name
                    // or at least the distinctive parts of it
                    $departmentsQuery->where(function($query) use ($locationName, $locationWords) {
                        // Try to match the full location name
                        $query->where('name', 'like', '%' . $locationName . '%');

                        // Also match if it contains distinctive parts (locations often have "One", "The", etc.)
                        // which are less useful for filtering
                        foreach ($locationWords as $word) {
                            // Skip very short words or common words like "the", "one", "two", "a", etc.
                            if (strlen($word) > 3 && !in_array($word, ['the', 'one', 'two', 'and', 'for'])) {
                                $query->orWhere('name', 'like', '%' . $word . '%');
                            }
                        }
                    });
                } else {
                    // For single-word locations, we can be more precise
                    $departmentsQuery->where('name', 'like', '%' . $locationName . '%');
                }

                $departments = $departmentsQuery->orderBy('name')->get();

                // If no departments found specifically for this location, don't fall back
                // to showing all departments - this prevents location-specific departments
                // from being shown to users from other locations
                if ($departments->isEmpty()) {
                    // Return an empty collection instead of all departments
                    return response()->json(DepartmentSelectResource::collection(collect([])));
                }

                return response()->json(DepartmentSelectResource::collection($departments));
            }
        }

        // Default: return all public departments
        return response()->json(DepartmentSelectResource::collection(
            Department::where('public', true)->orderBy('name')->get()
        ));
    }

    public function cannedReplies(): JsonResponse
    {
        return response()->json(CannedReplyResource::collection(CannedReply::where(function ($builder) {
            /** @var Builder|CannedReply $builder */
            $builder->where('shared', '=', true)
                ->orWhere('user_id', '=', Auth::id());
        })->get()));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function quickActions(Request $request): JsonResponse
    {
        $action = $request->get('action');
        /** @var User $user */
        $user = Auth::user();
        $tickets = Ticket::whereIn('id', $request->get('tickets'));
        if ($user && $user->role_id !== 1) {
            $tickets->where(function (Builder $query) use ($user) {
                $query->where('agent_id', $user->id);
                $query->orWhere('closed_by', $user->id);
                $query->orWhereIn('department_id', $user->departments()->pluck('id')->toArray());
                $query->orWhere(function (Builder $query) use ($user) {
                    $departments = array_unique(array_merge($user->departments()->pluck('id')->toArray(), Department::where('all_agents', 1)->pluck('id')->toArray()));
                    $query->whereNull('agent_id');
                    $query->whereIn('department_id', $departments);
                });
            });
        }
        if ($tickets->count() < 1) {
            return response()->json(['message' => __('You have not selected a ticket or do not have permissions to perform this action')], 403);
        }
        if ($action === 'agent') {
            $tickets->update(['agent_id' => $request->get('value')]);
            return response()->json(['message' => __('Tickets assigned to the selected agent')]);
        }
        if ($action === 'department') {
            $tickets->update(['department_id' => $request->get('value')]);
            return response()->json(['message' => __('Tickets assigned to the selected department')]);
        }
        if ($action === 'label') {
            foreach ($tickets->get() as $ticket) {
                /** @var Ticket $ticket */
                $ticket->labels()->syncWithoutDetaching($request->get('value'));
                $ticket->updated_at = Carbon::now();
                $ticket->save();
            }
            return response()->json(['message' => __('The label has been added to selected tickets')]);
        }
        if ($action === 'priority') {
            $tickets->update(['priority_id' => $request->get('value')]);
            return response()->json(['message' => __('The priority of the selected tickets has been changed')]);
        }
        if ($action === 'delete') {
            foreach ($tickets->get() as $ticket) {
                /** @var Ticket $ticket */
                $ticket->labels()->sync([]);
                foreach ($ticket->ticketReplies()->get() as $ticketReply) {
                    $ticketReply->ticketAttachments()->sync([]);
                }
                $ticket->ticketReplies()->delete();
                $ticket->delete();
            }
            return response()->json(['message' => __('The selected tickets have been deleted')]);
        }
        return response()->json(['message' => __('Quick action not found')], 404);
    }

    /**
     * @param  Ticket  $ticket
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function ticketQuickActions(Ticket $ticket, Request $request): JsonResponse
    {
        $value = $request->get('value');
        $action = $request->get('action');
        /** @var User $user */
        $user = Auth::user();
        if (!$ticket->verifyUser($user)) {
            return response()->json(['message' => __('You do not have permissions to manage this ticket')], 403);
        }
        if ($action === 'agent') {
            $ticket->agent_id = $value;
            $ticket->saveOrFail();
            return response()->json(['message' => __('Ticket assigned to the selected agent'), 'ticket' => new TicketManageResource($ticket), 'access' => $ticket->verifyUser($user)]);
        }
        if ($action === 'department') {
            $ticket->department_id = $value;
            $ticket->saveOrFail();
            return response()->json(['message' => __('Ticket assigned to the selected department'), 'ticket' => new TicketManageResource($ticket), 'access' => $ticket->verifyUser($user)]);
        }
        if ($action === 'label') {
            $ticket->labels()->syncWithoutDetaching($request->get('value'));
            $ticket->updated_at = Carbon::now();
            $ticket->save();
            return response()->json(['message' => __('Label has been assigned to ticket'), 'ticket' => new TicketManageResource($ticket), 'access' => true]);
        }
        if ($action === 'priority') {
            $ticket->priority_id = $value;
            $ticket->saveOrFail();
            return response()->json(['message' => __('Ticket priority has been changed'), 'ticket' => new TicketManageResource($ticket), 'access' => true]);
        }
        return response()->json(['message' => __('Quick action not found')], 404);
    }

    public function removeLabel(Ticket $ticket, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$ticket->verifyUser($user)) {
            return response()->json(['message' => __('You do not have permissions to manage this ticket')], 403);
        }
        if ($ticket->labels()->detach($request->get('label'))) {
            return response()->json(['message' => __('Data saved correctly'), 'ticket' => new TicketManageResource($ticket)]);
        }
        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    public function uploadAttachment(StoreFileRequest $request): JsonResponse
    {
        return (new FileController())->uploadAttachment($request);
    }

    /**
     * Get ticket updates since a specific timestamp
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updates(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $since = $request->get('since');

        if (!$since) {
            return response()->json(['updates' => []]);
        }

        // Query for tickets updated since the given timestamp
        $query = Ticket::where('updated_at', '>', $since);

        // Apply user permissions
        if ($user->role_id !== 1) {
            $query->where(function (Builder $query) use ($user) {
                $query->where('agent_id', $user->id);
                $query->orWhere('closed_by', $user->id);
                $query->orWhereIn('department_id', $user->departments()->pluck('id')->toArray());
                $query->orWhere(function (Builder $query) use ($user) {
                    $departments = array_unique(array_merge(
                        $user->departments()->pluck('id')->toArray(),
                        Department::where('all_agents', 1)->pluck('id')->toArray()
                    ));
                    $query->whereNull('agent_id');
                    $query->whereIn('department_id', $departments);
                });
            });
        }

        // Get the updated tickets
        $updatedTickets = $query->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'updates' => TicketListResource::collection($updatedTickets)
        ]);
    }
}
