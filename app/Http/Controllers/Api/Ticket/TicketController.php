<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreRequest;
use App\Http\Requests\Ticket\TicketReplyRequest;
use App\Http\Resources\Department\DepartmentSelectResource;
use App\Http\Resources\Priority\PriorityResource;
use App\Http\Resources\Status\StatusResource;
use App\Http\Resources\Ticket\TicketDetailsResource;
use App\Http\Resources\Ticket\TicketListResource;
use App\Http\Resources\Ticket\TicketManageResource;
use App\Http\Resources\TicketConcern\TicketConcernSelectResource;
use App\Models\CondoLocation;
use App\Models\Department;
use App\Models\Setting;
use App\Models\Priority;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketConcern;
use App\Models\TicketReply;
use App\Notifications\Ticket\AssignTicketToDepartment;
use App\Notifications\Ticket\NewTicketReplyFromUserToAgent;
use App\Notifications\Ticket\NewTicketReplyFromUserToUser;
use App\Notifications\Ticket\NewTicketRequest;
use Auth;
use Carbon\Carbon;
use Exception;
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
        $sort = json_decode($request->get('sort', json_encode(['order' => 'asc', 'column' => 'created_at'], JSON_THROW_ON_ERROR)), true, 512, JSON_THROW_ON_ERROR);
        $items = Ticket::filter($request->all())
            ->where('user_id', Auth::id())
            ->orderBy($sort['column'], $sort['order'])
            ->paginate((int) $request->get('perPage', 10));
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
        $ticket->status_id = 1;
        if ($request->has('department_id')) {
            $ticket->department_id = $request->get('department_id');
        }
        if ($request->has('priority_id')) {
            $ticket->priority_id = $request->get('priority_id');
        }

        // Get the authenticated user
        $user = Auth::user();

        // Set the condo_location_id from the user's condo_location_id
        if ($user && $user->condo_location_id) {
            $ticket->condo_location_id = $user->condo_location_id;
        }

        // If the concern has an assigned user, automatically assign the ticket to that user
        if ($ticket->concern_id) {
            $concern = TicketConcern::find($ticket->concern_id);
            if ($concern && $concern->assigned_to) {
                $ticket->agent_id = $concern->assigned_to;
            }
        }
        $ticket->user_id = Auth::id();
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
            $ticket->user->notify((new NewTicketRequest($ticket))->locale(Setting::getDecoded('app_locale')));
            if ($ticket->department_id !== null) {
                foreach ($ticket->department->agents() as $agent) {
                    $agent->notify(new AssignTicketToDepartment($ticket, $agent));
                }
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
        if ($ticket->user_id !== Auth::id()) {
            return response()->json(['message' => __('Not found')], 404);
        }
        return response()->json(new TicketDetailsResource($ticket));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  TicketReplyRequest  $request
     * @param  Ticket  $ticket
     * @return JsonResponse
     */
    public function reply(TicketReplyRequest $request, Ticket $ticket): JsonResponse
    {
        if ($ticket->user_id !== Auth::id()) {
            return response()->json(['message' => __('Not found')], 404);
        }
        $request->validated();
        $ticketReply = new TicketReply();
        $ticketReply->user_id = Auth::id();
        $ticketReply->body = $request->get('body');
        if ($ticket->ticketReplies()->save($ticketReply)) {
            if ($request->has('attachments')) {
                $ticketReply->ticketAttachments()->sync(collect($request->get('attachments'))->map(function ($attachment) {
                    return $attachment['id'];
                }));
            }
            $ticket->updated_at = Carbon::now();
            $ticket->save();
            $ticket->user->notify((new NewTicketReplyFromUserToUser($ticket))->locale(Setting::getDecoded('app_locale')));
            if ($ticket->agent) {
                $ticket->agent->notify((new NewTicketReplyFromUserToAgent($ticket, $ticket->agent))->locale(Setting::getDecoded('app_locale')));
            }
            return response()->json(['message' => __('Data saved correctly'), 'ticket' => new TicketManageResource($ticket)]);
        }
        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    public function departments(): JsonResponse
    {
        return response()->json(DepartmentSelectResource::collection(Department::where('public', '=', true)->get()));
    }

    public function statuses(): JsonResponse
    {
        return response()->json(StatusResource::collection(Status::all()));
    }

    public function concerns(): JsonResponse
    {
        return response()->json(TicketConcernSelectResource::collection(TicketConcern::where('status', true)->get()));
    }

    /**
     * Get concerns by department.
     *
     * @param Department $department
     * @return JsonResponse
     */
    public function concernsByDepartment(Department $department): JsonResponse
    {
        return response()->json(TicketConcernSelectResource::collection(
            TicketConcern::where('department_id', $department->id)
                ->where('status', true)
                ->orderBy('name')
                ->get()
        ));
    }

    public function priorities(): JsonResponse
    {
        return response()->json(PriorityResource::collection(Priority::orderBy('value')->get()));
    }

    public function condoLocations(): JsonResponse
    {
        return response()->json(CondoLocation::where('status', true)->select('id', 'name')->get());
    }
}
