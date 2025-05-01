<?php

namespace App\Events;

use App\Models\AppNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The notification instance.
     *
     * @var \App\Models\AppNotification
     */
    public $notification;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\AppNotification $notification
     * @return void
     */
    public function __construct(AppNotification $notification)
    {
        $this->notification = $notification;
        Log::info('NewNotification event constructed', [
            'notification_id' => $notification->id,
            'user_id' => $notification->user_id,
            'title' => $notification->title
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channel = 'notifications.' . $this->notification->user_id;
        Log::info('Broadcasting notification on channel', [
            'channel' => $channel,
            'notification_id' => $this->notification->id,
            'user_id' => $this->notification->user_id
        ]);
        return new PrivateChannel($channel);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'notification.new';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Use the actual created_at timestamp for consistency
        $timestamp = $this->notification->created_at->timestamp;

        $data = [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'type' => $this->notification->type,
            'icon' => $this->notification->icon,
            'link' => $this->notification->link,
            'created_at' => $this->notification->created_at->toIso8601String(), // Use ISO format for consistency
            'created_at_human' => $this->notification->created_at->diffForHumans(),
            'is_read' => false,
            'timestamp' => $timestamp, // Use actual timestamp for deduplication
            'user_id' => $this->notification->user_id, // Include user_id for debugging
            'unique_id' => $this->notification->id . '_' . $timestamp // Add a unique ID to prevent duplicates
        ];

        Log::info('Broadcasting notification data', [
            'notification_id' => $this->notification->id,
            'user_id' => $this->notification->user_id,
            'timestamp' => $timestamp,
            'data' => $data
        ]);

        return $data;
    }
}
