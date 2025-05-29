<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TicketUpdatedBroadcastingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;

    /**
     * Create a new event instance.
     *
     * @param Ticket $ticket
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast to the user associated with the ticket (customer)
        // and potentially to the agent if they are different and involved.
        // For dashboard users, they might be listening on their own user channel
        // or a general admin channel if that's how notifications are set up.
        // For simplicity, we'll broadcast on the private channel of the user (customer)
        // associated with the ticket.
        // The frontend will listen on `private-notifications.{userId}`.
        // Laravel automatically prefixes this with `private-` for Pusher.
        // So, the channel name here should be `notifications.{userId}`.
        
        // We need to ensure the event is broadcast to relevant dashboard users.
        // If a dashboard user (agent/admin) is performing the action, they might expect to see the update.
        // If the ticket is assigned to an agent, that agent should be notified.
        // If the ticket belongs to a user, that user should be notified.

        $channels = [];
        if ($this->ticket->user_id) {
            $channels[] = new PrivateChannel('notifications.' . $this->ticket->user_id);
        }
        if ($this->ticket->agent_id && $this->ticket->agent_id !== $this->ticket->user_id) {
            $channels[] = new PrivateChannel('notifications.' . $this->ticket->agent_id);
        }
        
        // To notify all admins or users with specific dashboard permissions,
        // you might need a more complex channel setup, e.g., a general admin channel
        // or iterate through users with specific roles/permissions and add their private channels.
        // For now, this targets the ticket owner and assigned agent.
        // The frontend for dashboard users will listen on their own user ID channel.
        // If an admin (user ID 5) updates a ticket for user ID 10,
        // user 10 gets it. Admin (user 5) also needs to get it if their frontend is listening.
        // The current setup in app.js listens to `private-notifications.{loggedInUserId}`.
        // So, if the logged-in user is an admin, they will get notifications sent to their own channel.
        // This event should perhaps be broadcast to the *actor's* channel as well if they are not the user/agent.
        // However, the default notification system usually targets the "notifiable" entity.

        // For now, let's assume the dashboard user (who is making the change or viewing the list)
        // is listening on their own `notifications.{auth()->id()}` channel.
        // The notification classes `NewTicketFromAgent` and `NewTicketReplyFromAgentToUser`
        // are sent TO `$ticket->user`.
        // This `TicketUpdatedBroadcastingEvent` should probably also be directed.
        // If an admin updates a ticket, the notification should go to the ticket's user and assigned agent.
        // The admin's own dashboard should update because their frontend is listening to *their* user channel,
        // and if the event is also broadcast *to them*, they'll get it.

        // Let's refine: this event is about a ticket being updated.
        // It should be broadcast on a channel related to the *ticket* or its *participants*.
        // The frontend (dashboard user) will listen on `private-notifications.{auth()->user()->id}`.
        // If the updated ticket is relevant to this authenticated user (e.g., they are admin, or it's in their department),
        // then the frontend logic should process it.
        // The broadcast itself should go to channels of users who *need to know* about this specific ticket update.

        // The `NewTicketFromAgent` and `NewTicketReplyFromAgentToUser` notifications are sent to `$ticket->user`.
        // This `TicketUpdatedBroadcastingEvent` should probably be similar.
        // If the goal is to update any dashboard user viewing the list, then a more general channel might be needed,
        // or the frontend logic needs to be smart.

        // Let's stick to notifying the user of the ticket and the agent.
        // The dashboard user (if different) will rely on their frontend to listen to their own channel
        // and if this event is also sent to *them* (e.g. if they are the agent), they'll get it.
        // This is tricky because `broadcastOn` doesn't know who is *currently viewing* the dashboard.
        // It knows who to notify based on the event's context (the ticket).

        // A common pattern is to broadcast on a channel for the resource itself, e.g., `ticket.{ticketId}`.
        // Or, as done with Laravel notifications, to the user channels.
        // The existing setup uses `notifications.{userId}`.
        
        // For ticket updates via quick actions, the primary recipient of the "update" information
        // would be the user whose ticket it is, and the agent assigned.
        // Other dashboard users (admins) viewing the list would see the update if their frontend
        // refreshes or if they are also subscribed to a relevant channel where this event is broadcast.

        // Let's ensure the event is broadcast to the ticket's user and agent.
        // The dashboard user (e.g. admin making the change) will have their *own* frontend listening
        // to *their own* user channel. If they are also the agent on the ticket, they'll get it.
        // If not, they won't get this specific broadcast event unless we add more channels.
        // This is a limitation of this direct event broadcasting vs. a full notification.

        // For now, target user and agent.
        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'ticket.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Reload to get the latest relations if any were eager-loaded or changed
        $this->ticket->refresh(); 
        return [
            'id' => $this->ticket->id,
            'uuid' => $this->ticket->uuid,
            'subject' => $this->ticket->subject,
            'user_id' => $this->ticket->user_id,
            'agent_id' => $this->ticket->agent_id,
            'status_id' => $this->ticket->status_id,
            'priority_id' => $this->ticket->priority_id,
            'department_id' => $this->ticket->department_id,
            'created_at' => $this->ticket->created_at->toIso8601String(),
            'updated_at' => $this->ticket->updated_at->toIso8601String(),
            'closed_at' => $this->ticket->closed_at ? $this->ticket->closed_at->toIso8601String() : null,
            // Include any other data the frontend needs to update the ticket list/card
            // e.g., status name, priority name, agent name, user name, labels
            // For simplicity, sending IDs. Frontend might need to map these to names or make a small fetch.
            // Or, include hydrated data:
            'status' => $this->ticket->status ? ['id' => $this->ticket->status->id, 'name' => $this->ticket->status->name, 'color' => $this->ticket->status->color] : null,
            'priority' => $this->ticket->priority ? ['id' => $this->ticket->priority->id, 'name' => $this->ticket->priority->name] : null,
            'agent' => $this->ticket->agent ? ['id' => $this->ticket->agent->id, 'name' => $this->ticket->agent->name, 'avatar' => $this->ticket->agent->avatar, 'gravatar' => $this->ticket->agent->gravatar] : null,
            'user' => $this->ticket->user ? ['id' => $this->ticket->user->id, 'name' => $this->ticket->user->name, 'email' => $this->ticket->user->email, 'avatar' => $this->ticket->user->avatar, 'gravatar' => $this->ticket->user->gravatar] : null,
            'labels' => $this->ticket->labels->map(function($label) { return ['id' => $label->id, 'name' => $label->name, 'color' => $label->color]; }),
            'lastReply' => $this->ticket->lastReply ? ['body' => Str::limit($this->ticket->lastReply->body, 50)] : null, // Basic info for list
            'condoLocation' => $this->ticket->condoLocation ? ['id' => $this->ticket->condoLocation->id, 'name' => $this->ticket->condoLocation->name] : null,
            'scheduled_visit_at' => $this->ticket->scheduled_visit_at ? $this->ticket->scheduled_visit_at->toIso8601String() : null,
            'voucher_code' => $this->ticket->voucher_code,
            'type' => 'ticket_updated' // Custom type for frontend
        ];
    }
}
