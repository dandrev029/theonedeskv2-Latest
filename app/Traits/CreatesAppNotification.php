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

        // Broadcast the notification
        event(new NewNotification($notification));

        return $notification;
    }
}
