<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Setting;
use App\Models\Status;
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
        // Fetch status IDs dynamically to be resilient to ID changes
        $openStatus = Status::where('name', 'Open')->first();
        $pendingStatus = Status::where('name', 'Pending')->first();
        $resolvedStatus = Status::where('name', 'Resolved')->first();
        $closedStatus = Status::where('name', 'Closed')->first();

        $openTicketsCount = 0;
        if ($openStatus) {
            $openTicketsCount = Ticket::where('status_id', $openStatus->id)->count();
        }

        $pendingTicketsCount = 0;
        if ($pendingStatus) {
            $pendingTicketsCount = Ticket::where('status_id', $pendingStatus->id)->count();
        }

        $solvedTicketsCount = 0;
        $solvedStatusIds = [];
        if ($resolvedStatus) {
            $solvedStatusIds[] = $resolvedStatus->id;
        }
        if ($closedStatus) {
            $solvedStatusIds[] = $closedStatus->id;
        }
        if (!empty($solvedStatusIds)) {
            $solvedTicketsCount = Ticket::whereIn('status_id', $solvedStatusIds)->count();
        }

        return response()->json([
            'open_tickets' => $openTicketsCount,
            'pending_tickets' => $pendingTicketsCount,
            'solved_tickets' => $solvedTicketsCount,
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
        // Get the current month and year using Carbon, respecting app timezone
        $appTimezone = config('app.timezone');
        $now = Carbon::now($appTimezone);
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $daysInMonth = $now->daysInMonth;

        // Fetch status IDs dynamically
        $pendingStatus = Status::where('name', 'Pending')->first();
        $resolvedStatus = Status::where('name', 'Resolved')->first();
        $closedStatus = Status::where('name', 'Closed')->first();

        $pendingStatusId = $pendingStatus ? $pendingStatus->id : null;
        $solvedStatusIds = [];
        if ($resolvedStatus) {
            $solvedStatusIds[] = $resolvedStatus->id;
        }
        if ($closedStatus) {
            $solvedStatusIds[] = $closedStatus->id;
        }

        // Initialize the data arrays with zeros for each day of the month
        $openedData = [];
        $pendingData = [];
        $solvedData = [];
        $labels = [];

        // Generate data for each day of the current month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $loopDate = Carbon::create($currentYear, $currentMonth, $day, 0, 0, 0, $appTimezone);
            $startOfDay = $loopDate->copy()->startOfDay();
            $endOfDay = $loopDate->copy()->endOfDay();

            $labels[] = $day; // Just the day number as label

            // Count tickets created on this specific day (opened)
            $openedCount = Ticket::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
            $openedData[] = $openedCount;

            // Count pending tickets (tickets that transitioned to 'Pending' on this day)
            $currentPendingCount = 0;
            if ($pendingStatus) { // Use the $pendingStatus object
                $currentPendingCount = Ticket::where('status_id', $pendingStatus->id)
                                             ->whereBetween('updated_at', [$startOfDay, $endOfDay]) // Assumes updated_at reflects status change
                                             ->count();
            }
            $pendingData[] = $currentPendingCount;

            // Count solved tickets (tickets that transitioned to 'Resolved' or 'Closed' on this day)
            $currentSolvedCount = 0;
            if ($resolvedStatus || $closedStatus) {
                $querySolved = Ticket::query();
                $querySolved->where(function ($q) use ($startOfDay, $endOfDay, $resolvedStatus, $closedStatus) {
                    if ($closedStatus) {
                        $q->orWhere(function ($subQ) use ($startOfDay, $endOfDay, $closedStatus) {
                            $subQ->where('status_id', $closedStatus->id)
                                 ->whereBetween('closed_at', [$startOfDay, $endOfDay]); // Use closed_at for 'Closed' status
                        });
                    }
                    if ($resolvedStatus) {
                        $q->orWhere(function ($subQ) use ($startOfDay, $endOfDay, $resolvedStatus) {
                            $subQ->where('status_id', $resolvedStatus->id)
                                 ->whereBetween('updated_at', [$startOfDay, $endOfDay]); // Assumes updated_at for 'Resolved' status change
                        });
                    }
                });
                // Ensure that we only count if at least one of the statuses is defined
                $currentSolvedCount = $querySolved->count();
            }
            $solvedData[] = $currentSolvedCount;
        }

        return response()->json([
            'labels' => $labels,
            'opened_data' => $openedData,
            'pending_data' => $pendingData,
            'solved_data' => $solvedData,
            'month_name' => $now->format('F'), // Full month name from Carbon instance
            'year' => $currentYear
        ]);
    }

    public function ticketAnalytics(): JsonResponse
    {
        $appTimezone = config('app.timezone');
        $carbonNow = Carbon::now($appTimezone);

        // Get average response time (time between ticket creation and first reply)
        $responseTimeData = [];
        $resolutionTimeData = [];
        $month = 1;

        while ($month <= 12) {
            // Get tickets created in this month
            $tickets = Ticket::whereMonth('created_at', '=', $month)
                ->whereYear('created_at', '=', $carbonNow->year) // Use current year from Carbon
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
        // $currentMonth = date('n'); // Replaced by $carbonNow->month
        // $currentYear = date('Y'); // Replaced by $carbonNow->year

        // Calculate overall metrics for the current month
        $currentMonthTickets = Ticket::whereMonth('created_at', '=', $carbonNow->month)
            ->whereYear('created_at', '=', $carbonNow->year)
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
