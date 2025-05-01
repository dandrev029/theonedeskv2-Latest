<?php

namespace App\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CustomDatabaseChannel extends DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            $data = $this->getData($notifiable, $notification);

            // Extract fields from data for the notifications table with default values
            $title = $data['title'] ?? 'Notification';
            $message = $data['message'] ?? 'You have a new notification';
            $icon = $data['icon'] ?? 'font-awesome.bell-regular';
            $link = $data['link'] ?? null;
            $type = $data['type'] ?? 'general';

            // Get the notifiable ID
            $notifiableId = $notifiable->getKey();

            // Get the condo_location_id if available
            $condoLocationId = method_exists($notifiable, 'getCondoLocationId')
                ? $notifiable->getCondoLocationId()
                : null;

            // Create the notification record with additional fields
            return $notifiable->notifications()->create([
                'id' => $data['id'] ?? \Illuminate\Support\Str::uuid()->toString(),
                'type' => get_class($notification),
                'data' => $this->getData($notifiable, $notification),
                'read_at' => null,
                'user_id' => $notifiableId, // Add user_id explicitly
                'title' => $title,
                'message' => $message,
                'icon' => $icon,
                'link' => $link,
                'is_read' => false,
                'condo_location_id' => $condoLocationId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating notification in CustomDatabaseChannel: ' . $e->getMessage(), [
                'notifiable_id' => $notifiable->getKey(),
                'notification_class' => get_class($notification),
                'exception' => $e
            ]);

            // Continue without throwing to prevent breaking the main functionality
            return null;
        }
    }
}
