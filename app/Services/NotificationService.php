<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a new notification.
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
    public function create(
        int $userId,
        string $title,
        string $message,
        ?string $type = 'general',
        ?string $icon = null,
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

    /**
     * Create a notification for multiple users.
     *
     * @param array $userIds
     * @param string $title
     * @param string $message
     * @param string|null $type
     * @param string|null $icon
     * @param string|null $link
     * @param array|null $data
     * @return array
     */
    public function createForMultipleUsers(
        array $userIds,
        string $title,
        string $message,
        ?string $type = 'general',
        ?string $icon = null,
        ?string $link = null,
        ?array $data = null
    ) {
        $notifications = [];

        foreach ($userIds as $userId) {
            $notifications[] = $this->create(
                $userId,
                $title,
                $message,
                $type,
                $icon,
                $link,
                $data
            );
        }

        return $notifications;
    }

    /**
     * Create a notification for all users of a specific role.
     *
     * @param int $roleId
     * @param string $title
     * @param string $message
     * @param string|null $type
     * @param string|null $icon
     * @param string|null $link
     * @param array|null $data
     * @return array
     */
    public function createForRole(
        int $roleId,
        string $title,
        string $message,
        ?string $type = 'general',
        ?string $icon = null,
        ?string $link = null,
        ?array $data = null
    ) {
        $userIds = User::where('role_id', $roleId)->pluck('id')->toArray();
        
        return $this->createForMultipleUsers(
            $userIds,
            $title,
            $message,
            $type,
            $icon,
            $link,
            $data
        );
    }

    /**
     * Create a notification for all users.
     *
     * @param string $title
     * @param string $message
     * @param string|null $type
     * @param string|null $icon
     * @param string|null $link
     * @param array|null $data
     * @return array
     */
    public function createForAllUsers(
        string $title,
        string $message,
        ?string $type = 'general',
        ?string $icon = null,
        ?string $link = null,
        ?array $data = null
    ) {
        $userIds = User::pluck('id')->toArray();
        
        return $this->createForMultipleUsers(
            $userIds,
            $title,
            $message,
            $type,
            $icon,
            $link,
            $data
        );
    }

    /**
     * Mark a notification as read.
     *
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead(int $notificationId)
    {
        $notification = AppNotification::find($notificationId);

        if ($notification) {
            return $notification->markAsRead();
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user.
     *
     * @param int $userId
     * @return int
     */
    public function markAllAsRead(int $userId)
    {
        return AppNotification::markAllAsRead($userId);
    }
}