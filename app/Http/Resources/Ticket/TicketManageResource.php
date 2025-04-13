<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\Department\DepartmentSelectResource;
use App\Http\Resources\Label\LabelSelectResource;
use App\Http\Resources\Priority\PriorityResource;
use App\Http\Resources\Status\StatusResource;
use App\Http\Resources\TicketConcern\TicketConcernSelectResource;
use App\Http\Resources\TicketReply\TicketReplyDetailsResource;
use App\Http\Resources\User\UserDetailsResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketManageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Ticket $ticket */
        $ticket = $this;
        return [
            'id' => $ticket->id,
            'uuid' => $ticket->uuid,
            'subject' => $ticket->subject,
            'concern' => new TicketConcernSelectResource($ticket->concern),
            'concern_id' => $ticket->concern_id,
            'condo_location' => $ticket->condoLocation ? ['id' => $ticket->condoLocation->id, 'name' => $ticket->condoLocation->name] : null,
            'condo_location_id' => $ticket->condo_location_id,
            'voucher_code' => $ticket->voucher_code,
            'scheduled_visit_at' => $ticket->scheduled_visit_at ? $ticket->scheduled_visit_at->toISOString() : null,
            'status' => new StatusResource($ticket->status),
            'status_id' => $ticket->status_id,
            'priority' => new PriorityResource($ticket->priority),
            'priority_id' => $ticket->priority_id,
            'department' => new DepartmentSelectResource($ticket->department),
            'department_id' => $ticket->department_id,
            'labels' => LabelSelectResource::collection($ticket->labels),
            'user' => new UserDetailsResource($ticket->user),
            'user_id' => $ticket->user_id,
            'agent' => new UserDetailsResource($ticket->agent),
            'agent_id' => $ticket->agent_id,
            'closedBy' => new UserDetailsResource($ticket->closedBy),
            'closed_by' => $ticket->closed_by,
            'closed_at' => $ticket->closed_at ? $ticket->closed_at->toISOString() : null,
            'created_at' => $ticket->created_at->toISOString(),
            'updated_at' => $ticket->updated_at->toISOString(),
            'ticketReplies' => TicketReplyDetailsResource::collection($ticket->ticketReplies()->orderByDesc('created_at')->get()),
        ];
    }
}
