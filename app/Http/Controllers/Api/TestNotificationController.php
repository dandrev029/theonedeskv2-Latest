<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\Ticket\AssignTicketToDepartment;
use App\Traits\CreatesAppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestNotificationController extends Controller
{
    use CreatesAppNotification;
    
    /**
     * Test sending notifications to department agents
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function testDepartmentNotifications(Request $request): JsonResponse
    {
        $results = [];
        
        try {
            // Get a department
            $departmentId = $request->input('department_id');
            $department = Department::find($departmentId);
            
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department not found',
                ], 404);
            }
            
            $results['department'] = [
                'id' => $department->id,
                'name' => $department->name,
                'all_agents' => (bool)$department->all_agents,
            ];
            
            // Get agents for this department
            $agents = $department->agents();
            $results['agents'] = [];
            
            foreach ($agents as $agent) {
                $results['agents'][] = [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'email' => $agent->email,
                ];
            }
            
            // Get a ticket to use for testing
            $ticket = Ticket::latest()->first();
            
            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tickets found for testing',
                ], 404);
            }
            
            $results['ticket'] = [
                'id' => $ticket->id,
                'uuid' => $ticket->uuid,
                'subject' => $ticket->subject,
            ];
            
            // Send notifications to all agents
            $results['notifications'] = [];
            
            foreach ($agents as $agent) {
                try {
                    // Create app notification directly
                    $notification = $this->createAppNotification(
                        $agent->id,
                        'Test Notification',
                        'This is a test notification for ticket: ' . $ticket->subject,
                        'ticket_assigned',
                        'font-awesome.user-tag-solid',
                        '/dashboard/tickets/' . $ticket->uuid . '/manage'
                    );
                    
                    $results['notifications'][] = [
                        'agent_id' => $agent->id,
                        'notification_id' => $notification ? $notification->id : null,
                        'success' => $notification !== null,
                    ];
                    
                    // Also send through the notification system
                    $agent->notify(new AssignTicketToDepartment($ticket, $agent));
                    
                } catch (\Exception $e) {
                    Log::error('Error in test notification for agent: ' . $e->getMessage(), [
                        'agent_id' => $agent->id,
                        'exception' => $e,
                    ]);
                    
                    $results['notifications'][] = [
                        'agent_id' => $agent->id,
                        'error' => $e->getMessage(),
                        'success' => false,
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Test notifications sent',
                'results' => $results,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in test notification controller: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'results' => $results,
            ], 500);
        }
    }
    
    /**
     * Get recent notifications for a user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRecentNotifications(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }
            
            // Get recent app notifications
            $appNotifications = AppNotification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'app_notifications' => $appNotifications,
                'laravel_notifications' => $user->notifications()->take(10)->get(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting recent notifications: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
