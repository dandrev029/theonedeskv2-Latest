<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
                Log::warning('Notification created for non-existent user', [
                    'user_id' => $userId,
                    'title' => $title
                ]);
                return null;
            }

            Log::info('Creating notification', [
                'user_id' => $userId,
                'title' => $title,
                'type' => $type
            ]);

            // Check for existing notifications with the same content in the last 30 minutes
            // This helps prevent duplicate notifications when hard reloading

            // First, check for exact match
            $existingNotification = AppNotification::where('user_id', $userId)
                ->where('title', $title)
                ->where('message', $message)
                ->where('created_at', '>=', now()->subMinutes(30))
                ->first();

            // If no exact match, check for similar content (for ticket notifications)
            if (!$existingNotification &&
                (stripos($title, 'ticket') !== false || stripos($message, 'ticket') !== false)) {

                // For ticket notifications, check if there's a similar notification
                // This handles cases where the message might be slightly different but it's the same ticket
                $existingNotification = AppNotification::where('user_id', $userId)
                    ->where('title', 'like', '%' . substr($title, 0, 15) . '%')
                    ->where(function($query) use ($message) {
                        // Look for notifications with similar message content
                        // Extract the first part of the message before any specific details
                        $baseMessage = explode(':', $message)[0];
                        if (strlen($baseMessage) > 5) {
                            $query->where('message', 'like', $baseMessage . '%');
                        } else {
                            $query->where('message', 'like', '%' . substr($message, 0, 20) . '%');
                        }
                    })
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            if ($existingNotification) {
                Log::info('Skipping duplicate notification', [
                    'user_id' => $userId,
                    'title' => $title,
                    'existing_id' => $existingNotification->id,
                    'duplicate_type' => $existingNotification->title === $title ? 'exact' : 'similar'
                ]);
                return $existingNotification;
            }

            // Also check for and remove any existing duplicates that might have slipped through
            $this->removeDuplicateNotifications($userId, $title, $message);

            // Clean up old notifications for this user to prevent buildup
            $this->cleanupOldNotifications($userId);

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

            Log::info('Notification created successfully', [
                'notification_id' => $notification->id,
                'user_id' => $userId
            ]);

            // Broadcast the notification
            try {
                event(new NewNotification($notification));
                Log::info('Notification broadcast event fired', [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'channel' => 'notifications.' . $userId
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to broadcast notification: ' . $e->getMessage(), [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'exception' => $e
                ]);
            }

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage(), [
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

    /**
     * Clean up old notifications for a user.
     * Keeps only the most recent 100 notifications and deletes the rest.
     *
     * @param int $userId
     * @return int Number of deleted notifications
     */
    public function cleanupOldNotifications(int $userId)
    {
        try {
            // Get the IDs of notifications to keep (most recent 100)
            $keepIds = AppNotification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->pluck('id');

            // Delete notifications older than the most recent 100
            $deleted = AppNotification::where('user_id', $userId)
                ->whereNotIn('id', $keepIds)
                ->delete();

            if ($deleted > 0) {
                Log::info("Cleaned up {$deleted} old notifications for user {$userId}");
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to clean up old notifications: ' . $e->getMessage(), [
                'user_id' => $userId,
                'exception' => $e
            ]);
            return 0;
        }
    }

    /**
     * Remove duplicate notifications for a user based on title and message similarity.
     * This helps clean up existing duplicates that might have been created before
     * the enhanced duplicate detection was implemented.
     *
     * @param int $userId
     * @param string $title
     * @param string $message
     * @return int Number of deleted duplicate notifications
     */
    public function removeDuplicateNotifications(int $userId, string $title, string $message)
    {
        try {
            // Find all similar notifications from the last 24 hours
            $similarNotifications = AppNotification::where('user_id', $userId)
                ->where(function($query) use ($title) {
                    // Match on title
                    if (strlen($title) > 10) {
                        $query->where('title', 'like', '%' . substr($title, 0, 15) . '%');
                    } else {
                        $query->where('title', $title);
                    }
                })
                ->where(function($query) use ($message) {
                    // For ticket notifications, be more flexible with the message matching
                    if (stripos($message, 'ticket') !== false) {
                        // Extract the base part of the message (before any specific details)
                        $baseMessage = explode(':', $message)[0];
                        if (strlen($baseMessage) > 5) {
                            $query->where('message', 'like', $baseMessage . '%');
                        } else {
                            $query->where('message', 'like', '%' . substr($message, 0, 20) . '%');
                        }
                    } else {
                        // For other notifications, require closer match
                        $query->where('message', 'like', '%' . substr($message, 0, 30) . '%');
                    }
                })
                ->where('created_at', '>=', now()->subHours(24))
                ->orderBy('created_at', 'desc')
                ->get();

            // If we have more than one similar notification, keep only the newest one
            if ($similarNotifications->count() > 1) {
                // Keep the newest notification
                $keepId = $similarNotifications->first()->id;

                // Delete the duplicates
                $deleteIds = $similarNotifications->slice(1)->pluck('id')->toArray();
                $deleted = AppNotification::whereIn('id', $deleteIds)->delete();

                if ($deleted > 0) {
                    Log::info("Removed {$deleted} duplicate notifications for user {$userId}", [
                        'title' => $title,
                        'kept_id' => $keepId
                    ]);
                }

                return $deleted;
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to remove duplicate notifications: ' . $e->getMessage(), [
                'user_id' => $userId,
                'title' => $title,
                'exception' => $e
            ]);
            return 0;
        }
    }
}