<?php

namespace App\Http\Controllers\API;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class NotificationController extends Controller
{
    protected $notificationService;

    /**
     * Create a new controller instance.
     *
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'notifications' => ['data' => []],
                'laravel_notifications' => ['data' => []],
                'unread_count' => 0,
                'app_unread_count' => 0,
                'laravel_unread_count' => 0
            ]);
        }

        // Get app notifications - limit to 25 for better performance
        $appNotifications = AppNotification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        // Get Laravel notifications
        $user = Auth::user();
        $laravelNotifications = collect(['data' => []]); // Default empty collection
        $laravelUnreadCount = 0;

        // Only try to get Laravel notifications if user exists
        if ($user) {
            try {
                $laravelNotifications = $user->notifications()
                    ->orderBy('created_at', 'desc')
                    ->paginate(25); // Limit to 25 for better performance

                $laravelUnreadCount = $user->unreadNotifications()->count();
            } catch (\Exception $e) {
                Log::error('Error fetching Laravel notifications: ' . $e->getMessage());
            }
        }

        // Count unread notifications from both sources
        $appUnreadCount = AppNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        // Get the actual count of unread notifications that will be displayed
        // This ensures the count matches what the user actually sees in the UI
        $displayedAppNotifications = $appNotifications->filter(function($notification) {
            return $notification->is_read === false;
        })->count();

        $displayedLaravelNotifications = 0;
        if ($laravelNotifications && isset($laravelNotifications['data'])) {
            $displayedLaravelNotifications = collect($laravelNotifications['data'])->filter(function($notification) {
                return $notification->read_at === null;
            })->count();
        }

        // Use the displayed count instead of the total count
        $displayedUnreadCount = $displayedAppNotifications + $displayedLaravelNotifications;
        $totalUnreadCount = $appUnreadCount + $laravelUnreadCount;

        // Log if there's a discrepancy
        if ($displayedUnreadCount !== $totalUnreadCount) {
            Log::info("Notification count discrepancy: Total unread: {$totalUnreadCount}, Displayed unread: {$displayedUnreadCount}");
        }

        return response()->json([
            'notifications' => $appNotifications,
            'laravel_notifications' => $laravelNotifications,
            'unread_count' => $displayedUnreadCount, // Use the displayed count instead of total count
            'app_unread_count' => $displayedAppNotifications,
            'laravel_unread_count' => $displayedLaravelNotifications,
            'total_unread_count' => $totalUnreadCount // Keep the total count for reference
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string',
            'icon' => 'nullable|string',
            'link' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $notification = $this->notificationService->create(
            $request->user_id,
            $request->title,
            $request->message,
            $request->type,
            $request->icon,
            $request->link,
            $request->data
        );

        return response()->json([
            'message' => 'Notification created successfully',
            'notification' => $notification
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notification = AppNotification::findOrFail($id);

        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($notification);
    }

    /**
     * Mark notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $appNotification = null;
        $laravelNotification = null;

        // Try to find the notification in app_notifications
        try {
            $appNotification = AppNotification::find($id);

            // Check if the notification belongs to the authenticated user
            if ($appNotification && $appNotification->user_id === Auth::id()) {
                $this->notificationService->markAsRead($id);
            }
        } catch (\Exception $e) {
            Log::warning('Error marking app notification as read: ' . $e->getMessage());
        }

        // Try to find the notification in Laravel notifications
        try {
            $laravelNotification = $user->notifications()->where('id', $id)->first();

            if ($laravelNotification) {
                $laravelNotification->markAsRead();
            }
        } catch (\Exception $e) {
            Log::warning('Error marking Laravel notification as read: ' . $e->getMessage());
        }

        // If neither notification was found, return 404
        if (!$appNotification && !$laravelNotification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        // Count unread notifications from both sources
        $appUnreadCount = AppNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        $laravelUnreadCount = $user->unreadNotifications()->count();

        $totalUnreadCount = $appUnreadCount + $laravelUnreadCount;

        return response()->json([
            'message' => 'Notification marked as read',
            'unread_count' => $totalUnreadCount,
            'app_unread_count' => $appUnreadCount,
            'laravel_unread_count' => $laravelUnreadCount
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $appCount = 0;
        $laravelCount = 0;

        // Mark all app notifications as read
        try {
            $appCount = $this->notificationService->markAllAsRead(Auth::id());
        } catch (\Exception $e) {
            Log::warning('Error marking all app notifications as read: ' . $e->getMessage());
        }

        // Mark all Laravel notifications as read
        try {
            $laravelCount = $user->unreadNotifications()->count();
            $user->unreadNotifications()->update(['read_at' => now()]);
        } catch (\Exception $e) {
            Log::warning('Error marking all Laravel notifications as read: ' . $e->getMessage());
        }

        $totalCount = $appCount + $laravelCount;

        return response()->json([
            'message' => $totalCount . ' notifications marked as read',
            'unread_count' => 0,
            'app_count' => $appCount,
            'laravel_count' => $laravelCount
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notification = AppNotification::findOrFail($id);

        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully',
            'unread_count' => AppNotification::where('user_id', Auth::id())->where('is_read', false)->count()
        ]);
    }

    /**
     * Create a notification for multiple users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createForMultipleUsers(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string',
            'icon' => 'nullable|string',
            'link' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $notifications = $this->notificationService->createForMultipleUsers(
            $request->user_ids,
            $request->title,
            $request->message,
            $request->type,
            $request->icon,
            $request->link,
            $request->data
        );

        return response()->json([
            'message' => count($notifications) . ' notifications created successfully',
            'count' => count($notifications)
        ], 201);
    }

    /**
     * Create a notification for all users with a specific role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createForRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:user_roles,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string',
            'icon' => 'nullable|string',
            'link' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $notifications = $this->notificationService->createForRole(
            $request->role_id,
            $request->title,
            $request->message,
            $request->type,
            $request->icon,
            $request->link,
            $request->data
        );

        return response()->json([
            'message' => count($notifications) . ' notifications created successfully',
            'count' => count($notifications)
        ], 201);
    }

    /**
     * Create a notification for all users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createForAllUsers(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string',
            'icon' => 'nullable|string',
            'link' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $notifications = $this->notificationService->createForAllUsers(
            $request->title,
            $request->message,
            $request->type,
            $request->icon,
            $request->link,
            $request->data
        );

        return response()->json([
            'message' => count($notifications) . ' notifications created successfully',
            'count' => count($notifications)
        ], 201);
    }
}
