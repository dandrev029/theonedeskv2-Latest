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
        try {
            // Verify user exists
            $user = User::find($userId);
            if (!$user) {
                \Log::warning('Notification created for non-existent user', [
                    'user_id' => $userId,
                    'title' => $title
                ]);
                return null;
            }

            \Log::info('Creating notification', [
                'user_id' => $userId,
                'title' => $title,
                'type' => $type
            ]);

            // Check for existing notifications with the same content in the last 5 minutes
            $existingNotification = AppNotification::where('user_id', $userId)
                ->where('title', $title)
                ->where('message', $message)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->first();

            if ($existingNotification) {
                \Log::info('Skipping duplicate notification', [
                    'user_id' => $userId,
                    'title' => $title,
                    'existing_id' => $existingNotification->id
                ]);
                return $existingNotification;
            }

            // Create new notification if no duplicate exists
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

            \Log::info('Notification created successfully', [
                'notification_id' => $notification->id,
                'user_id' => $userId
            ]);

            // Broadcast the notification
            try {
                event(new NewNotification($notification));
                \Log::info('Notification broadcast event fired', [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'channel' => 'notifications.' . $userId
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast notification: ' . $e->getMessage(), [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'exception' => $e
                ]);
            }

            return $notification;
        } catch (\Exception $e) {
            \Log::error('Failed to create notification: ' . $e->getMessage(), [
                'user_id' => $userId,
                'title' => $title,
                'exception' => $e
            ]);
            return null;
        }
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