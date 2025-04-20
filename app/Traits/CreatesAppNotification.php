<?php

namespace App\Traits;

use App\Events\NewNotification;
use App\Models\AppNotification;

trait CreatesAppNotification
{
    /**
     * Create an in-app notification
     *
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string|null $type
     * @param string|null $icon
     * @param string|null $link
     * @param array|null $data
     * @return \App\Models\AppNotification
     */
    protected function createAppNotification(
        int $userId,
        string $title,
        string $message,
        ?string $type = 'ticket',
        ?string $icon = 'font-awesome.ticket-alt-solid',
        ?string $link = null,
        ?array $data = null
    ) {
        try {
            // Ensure user_id is valid and not null
            if (!$userId) {
                \Log::warning('Invalid user_id provided to createAppNotification. Using default admin user (1).', [
                    'title' => $title,
                    'message' => $message,
                    'type' => $type
                ]);
                $userId = 1; // Default to admin user if no valid user_id is provided
            }

            $notification = AppNotification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'icon' => $icon,
                'link' => $link,
                'data' => $data,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't throw it to prevent breaking the main functionality
            \Log::error('Error creating app notification: ' . $e->getMessage(), [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type
            ]);
            return null;
        }

        // Broadcast the notification
        event(new NewNotification($notification));

        return $notification;
    }
}
