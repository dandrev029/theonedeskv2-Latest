<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function count(): JsonResponse
    {
        return response()->json([
            'open_tickets' => Ticket::where('status_id', 1)->count(),
            'pending_tickets' => Ticket::where('status_id', 2)->count(),
            'solved_tickets' => Ticket::whereIn('status_id', [3, 4])->count(),
            'without_agent' => Ticket::whereNull('agent_id')->count(),
        ]);
    }

    public function registeredUsers(): JsonResponse
    {
        $graph = [];
        $month = 1;
        while ($month <= 12) {
            $graph[] = User::whereMonth('created_at', '=', $month)->count();
            $month++;
        }
        return response()->json($graph);
    }

    public function openedTickets(): JsonResponse
    {
        // Get the current month and year
        $currentMonth = date('n');
        $currentYear = date('Y');

        // Get the number of days in the current month
        $daysInMonth = date('t');

        // Initialize the data arrays with zeros for each day of the month
        $openedData = [];
        $pendingData = [];
        $solvedData = [];
        $labels = [];

        // Generate data for each day of the current month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            $labels[] = $day; // Just the day number as label

            // Count tickets created on this specific day (opened)
            $openedCount = Ticket::whereDate('created_at', $date)->count();
            $openedData[] = $openedCount;

            // Count pending tickets on this specific day (status_id = 2)
            $pendingCount = Ticket::whereDate('created_at', $date)
                ->where('status_id', 2)
                ->count();
            $pendingData[] = $pendingCount;

            // Count solved tickets on this specific day (status_id = 3 or 4)
            $solvedCount = Ticket::whereDate('created_at', $date)
                ->whereIn('status_id', [3, 4])
                ->count();
            $solvedData[] = $solvedCount;
        }

        return response()->json([
            'labels' => $labels,
            'opened_data' => $openedData,
            'pending_data' => $pendingData,
            'solved_data' => $solvedData,
            'month_name' => date('F'), // Full month name
            'year' => $currentYear
        ]);
    }

    public function ticketAnalytics(): JsonResponse
    {
        // Get average response time (time between ticket creation and first reply)
        $responseTimeData = [];
        $resolutionTimeData = [];
        $month = 1;

        while ($month <= 12) {
            // Get tickets created in this month
            $tickets = Ticket::whereMonth('created_at', '=', $month)
                ->whereYear('created_at', '=', date('Y'))
                ->get();

            $totalResponseTime = 0;
            $totalResolutionTime = 0;
            $responseCount = 0;
            $resolutionCount = 0;

            foreach ($tickets as $ticket) {
                // Calculate first response time
                $firstReply = TicketReply::where('ticket_id', $ticket->id)
                    ->where('user_id', '!=', $ticket->user_id) // Only count agent replies
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($firstReply) {
                    $responseTime = $ticket->created_at->diffInHours($firstReply->created_at);
                    $totalResponseTime += $responseTime;
                    $responseCount++;
                }

                // Calculate resolution time for closed tickets
                if ($ticket->closed_at) {
                    $resolutionTime = $ticket->created_at->diffInHours($ticket->closed_at);
                    $totalResolutionTime += $resolutionTime;
                    $resolutionCount++;
                }
            }

            // Calculate averages (in hours)
            $avgResponseTime = $responseCount > 0 ? round($totalResponseTime / $responseCount, 1) : 0;
            $avgResolutionTime = $resolutionCount > 0 ? round($totalResolutionTime / $resolutionCount, 1) : 0;

            $responseTimeData[] = $avgResponseTime;
            $resolutionTimeData[] = $avgResolutionTime;

            $month++;
        }

        // Get current month statistics
        $currentMonth = date('n');
        $currentYear = date('Y');

        // Calculate overall metrics for the current month
        $currentMonthTickets = Ticket::whereMonth('created_at', '=', $currentMonth)
            ->whereYear('created_at', '=', $currentYear)
            ->get();

        $totalResponseTime = 0;
        $totalResolutionTime = 0;
        $responseCount = 0;
        $resolutionCount = 0;
        $ticketsWithFirstResponse = 0;
        $totalTickets = count($currentMonthTickets);

        foreach ($currentMonthTickets as $ticket) {
            // Calculate first response time
            $firstReply = TicketReply::where('ticket_id', $ticket->id)
                ->where('user_id', '!=', $ticket->user_id) // Only count agent replies
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstReply) {
                $responseTime = $ticket->created_at->diffInHours($firstReply->created_at);
                $totalResponseTime += $responseTime;
                $responseCount++;
                $ticketsWithFirstResponse++;
            }

            // Calculate resolution time for closed tickets
            if ($ticket->closed_at) {
                $resolutionTime = $ticket->created_at->diffInHours($ticket->closed_at);
                $totalResolutionTime += $resolutionTime;
                $resolutionCount++;
            }
        }

        // Calculate first response rate
        $firstResponseRate = $totalTickets > 0 ? round(($ticketsWithFirstResponse / $totalTickets) * 100) : 0;

        // Calculate average response and resolution times
        $avgResponseTime = $responseCount > 0 ? round($totalResponseTime / $responseCount, 1) : 0;
        $avgResolutionTime = $resolutionCount > 0 ? round($totalResolutionTime / $resolutionCount, 1) : 0;

        return response()->json([
            'response_time_data' => $responseTimeData,
            'resolution_time_data' => $resolutionTimeData,
            'current_month_stats' => [
                'avg_response_time' => $avgResponseTime,
                'avg_resolution_time' => $avgResolutionTime,
                'first_response_rate' => $firstResponseRate,
                'tickets_with_response' => $ticketsWithFirstResponse,
                'total_tickets' => $totalTickets,
                'resolved_tickets' => $resolutionCount
            ]
        ]);
    }
}
