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
     * @return \App\Models\AppNotification|null
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

            // Check if user exists
            $user = \App\Models\User::find($userId);
            if (!$user) {
                \Log::warning('User not found for notification. Using default admin user (1).', [
                    'user_id' => $userId,
                    'title' => $title,
                    'type' => $type
                ]);
                $userId = 1; // Default to admin user if user not found
            }

            \Log::info('Creating app notification', [
                'user_id' => $userId,
                'title' => $title,
                'type' => $type,
                'link' => $link
            ]);

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

            \Log::info('App notification created successfully', [
                'notification_id' => $notification->id,
                'user_id' => $userId
            ]);

            // Broadcast the notification
            try {
                // Check if a Laravel notification with the same content already exists
                $existingNotification = \DB::table('notifications')
                    ->where('notifiable_id', $userId)
                    ->where('notifiable_type', 'App\\Models\\User')
                    ->where('title', $notification->title)
                    ->where('message', $notification->message)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->first();

                // Only create a Laravel notification if one doesn't already exist
                if (!$existingNotification) {
                    try {
                        // Create a database record directly to avoid the user_id issue
                        \DB::table('notifications')->insert([
                            'id' => \Illuminate\Support\Str::uuid()->toString(),
                            'type' => 'App\\Notifications\\InAppNotification',
                            'notifiable_type' => 'App\\Models\\User',
                            'notifiable_id' => $userId,
                            'user_id' => $userId, // Add user_id explicitly
                            'data' => json_encode([
                                'id' => $notification->id,
                                'title' => $notification->title,
                                'message' => $notification->message,
                                'type' => $notification->type,
                                'icon' => $notification->icon,
                                'link' => $notification->link,
                                'created_at' => $notification->created_at->toDateTimeString(),
                            ]),
                            'read_at' => null,
                            'title' => $notification->title, // Add title explicitly
                            'message' => $notification->message, // Add message explicitly
                            'icon' => $notification->icon, // Add icon explicitly
                            'link' => $notification->link, // Add link explicitly
                            'is_read' => false, // Add is_read explicitly
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        \Log::info('Database notification created successfully', [
                            'user_id' => $userId
                        ]);
                    } catch (\Exception $dbEx) {
                        \Log::error('Error creating database notification: ' . $dbEx->getMessage(), [
                            'user_id' => $userId,
                            'exception' => $dbEx
                        ]);
                        // Continue even if database notification fails
                    }
                } else {
                    \Log::info('Skipped creating duplicate Laravel notification', [
                        'user_id' => $userId,
                        'title' => $notification->title
                    ]);
                }

                // Broadcast the real-time notification
                event(new NewNotification($notification));
                \Log::info('Notification broadcast event fired', [
                    'notification_id' => $notification->id,
                    'user_id' => $userId
                ]);
            } catch (\Exception $broadcastEx) {
                \Log::error('Error broadcasting notification: ' . $broadcastEx->getMessage(), [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'exception' => $broadcastEx
                ]);
                // Continue even if broadcasting fails
            }

            return $notification;
        } catch (\Exception $e) {
            // Log the error but don't throw it to prevent breaking the main functionality
            \Log::error('Error creating app notification: ' . $e->getMessage(), [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'exception' => $e
            ]);
            return null;
        }
    }
}
