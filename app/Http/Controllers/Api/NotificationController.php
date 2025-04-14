<?php

namespace App\Http\Controllers\API;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $notifications = AppNotification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => AppNotification::where('user_id', Auth::id())->where('is_read', false)->count()
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
        $notification = AppNotification::findOrFail($id);
        
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $this->notificationService->markAsRead($id);
        
        return response()->json([
            'message' => 'Notification marked as read',
            'unread_count' => AppNotification::where('user_id', Auth::id())->where('is_read', false)->count()
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsRead(Auth::id());
        
        return response()->json([
            'message' => $count . ' notifications marked as read',
            'unread_count' => 0
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
