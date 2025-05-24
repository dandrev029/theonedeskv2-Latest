<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Department;
use App\Models\Status;
use App\Models\Priority;
use App\Models\User;
use App\Models\TicketConcern;
use App\Models\CondoLocation;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\TicketsExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get filter options for reports.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilterOptions()
    {
        $user = Auth::user();

        // Get departments based on user permissions
        $departments = $user->userRole->id === 1
            ? Department::all()
            : $user->departments;

        // Get agents/users with dashboard access
        $agents = User::whereHas('userRole', function($query) {
            $query->where('dashboard_access', 1);
        })->get();

        // Get ticket concerns based on user's departments
        $concerns = $user->userRole->id === 1
            ? TicketConcern::with('department')->where('status', 1)->get()
            : TicketConcern::with('department')->where('status', 1)
                ->whereIn('department_id', $user->departments->pluck('id'))
                ->get();

        return response()->json([
            'departments' => $departments->map(function($dept) {
                return ['id' => $dept->id, 'name' => $dept->name];
            }),
            'statuses' => Status::all()->map(function($status) {
                return ['id' => $status->id, 'name' => $status->name, 'color' => $status->color];
            }),
            'priorities' => Priority::all()->map(function($priority) {
                return ['id' => $priority->id, 'name' => $priority->name, 'value' => $priority->value];
            }),
            'agents' => $agents->map(function($agent) {
                return ['id' => $agent->id, 'name' => $agent->name, 'email' => $agent->email];
            }),
            'concerns' => $concerns->map(function($concern) {
                return [
                    'id' => $concern->id,
                    'name' => $concern->name,
                    'department_id' => $concern->department_id,
                    'department_name' => $concern->department ? $concern->department->name : null
                ];
            }),
            'condo_locations' => CondoLocation::all()->map(function($location) {
                return ['id' => $location->id, 'name' => $location->name];
            })
        ]);
    }

    /**
     * Get ticket reports based on filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTicketReports(Request $request)
    {
        $request->validate([
            'period' => 'sometimes|in:daily,weekly,monthly,quarterly,annually',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status_ids' => 'nullable|array',
            'status_ids.*' => 'integer|exists:statuses,id',
            'priority_ids' => 'nullable|array',
            'priority_ids.*' => 'integer|exists:priorities,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
            'agent_ids' => 'nullable|array',
            'agent_ids.*' => 'integer|exists:users,id',
            'concern_ids' => 'nullable|array',
            'concern_ids.*' => 'integer|exists:ticket_concerns,id',
            'condo_location_ids' => 'nullable|array',
            'condo_location_ids.*' => 'integer|exists:condo_locations,id',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:10|max:100'
        ]);

        $user = Auth::user();
        $query = Ticket::query()->with([
            'user.condoLocation',
            'department',
            'priority',
            'status',
            'labels',
            'concern.department',
            'agent',
            'condoLocation'
        ]);

        // Apply user department restrictions if not admin
        if ($user->userRole->id !== 1) {
            $userDepartmentIds = $user->departments->pluck('id');
            $query->where(function($q) use ($userDepartmentIds, $user) {
                $q->whereIn('department_id', $userDepartmentIds)
                  ->orWhere('agent_id', $user->id)
                  ->orWhere('user_id', $user->id);
            });
        }

        // Apply date filters
        $period = $request->input('period');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : null;

        if ($period) {
            switch ($period) {
                case 'daily':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'weekly':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'monthly':
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'quarterly':
                    $quarter = Carbon::now()->quarter;
                    $year = Carbon::now()->year;
                    $startOfQuarter = Carbon::createFromDate($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
                    $endOfQuarter = $startOfQuarter->copy()->addMonths(2)->endOfMonth();
                    $query->whereBetween('created_at', [$startOfQuarter, $endOfQuarter]);
                    break;
                case 'annually':
                    $query->whereYear('created_at', Carbon::now()->year);
                    break;
            }
        } elseif ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        // Note: If no date filters are provided, we show all tickets (no date restriction)

        // Apply additional filters
        if ($request->has('status_ids') && is_array($request->input('status_ids')) && !empty($request->input('status_ids'))) {
            $query->whereIn('status_id', array_map('intval', $request->input('status_ids')));
        }

        if ($request->has('priority_ids') && is_array($request->input('priority_ids')) && !empty($request->input('priority_ids'))) {
            $query->whereIn('priority_id', array_map('intval', $request->input('priority_ids')));
        }

        if ($request->has('department_ids') && is_array($request->input('department_ids')) && !empty($request->input('department_ids'))) {
            $query->whereIn('department_id', array_map('intval', $request->input('department_ids')));
        }

        if ($request->has('agent_ids') && is_array($request->input('agent_ids')) && !empty($request->input('agent_ids'))) {
            $query->whereIn('agent_id', array_map('intval', $request->input('agent_ids')));
        }

        if ($request->has('concern_ids') && is_array($request->input('concern_ids')) && !empty($request->input('concern_ids'))) {
            $query->whereIn('concern_id', array_map('intval', $request->input('concern_ids')));
        }

        if ($request->has('condo_location_ids') && is_array($request->input('condo_location_ids')) && !empty($request->input('condo_location_ids'))) {
            $query->whereIn('condo_location_id', array_map('intval', $request->input('condo_location_ids')));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'LIKE', '%'.$search.'%')
                  ->orWhereHas('ticketReplies', function($subQ) use ($search) {
                      $subQ->where('body', 'LIKE', '%'.$search.'%');
                  })
                  ->orWhereHas('user', function($subQ) use ($search) {
                      $subQ->where('name', 'LIKE', '%'.$search.'%')
                           ->orWhere('email', 'LIKE', '%'.$search.'%');
                  });
            });
        }

        $perPage = $request->input('per_page', 25);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($tickets);
    }

    /**
     * Download ticket report as Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadTicketReport(Request $request)
    {
        $request->validate([
            'period' => 'sometimes|in:daily,weekly,monthly,quarterly,annually',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status_ids' => 'nullable|array',
            'status_ids.*' => 'integer|exists:statuses,id',
            'priority_ids' => 'nullable|array',
            'priority_ids.*' => 'integer|exists:priorities,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
            'agent_ids' => 'nullable|array',
            'agent_ids.*' => 'integer|exists:users,id',
            'concern_ids' => 'nullable|array',
            'concern_ids.*' => 'integer|exists:ticket_concerns,id',
            'condo_location_ids' => 'nullable|array',
            'condo_location_ids.*' => 'integer|exists:condo_locations,id',
            'search' => 'nullable|string|max:255'
        ]);

        $user = Auth::user();
        $query = Ticket::query()->with([
            'user.condoLocation',
            'department',
            'priority',
            'status',
            'labels',
            'concern.department',
            'agent',
            'condoLocation'
        ]);

        // Apply user department restrictions if not admin
        if ($user->userRole->id !== 1) {
            $userDepartmentIds = $user->departments->pluck('id');
            $query->where(function($q) use ($userDepartmentIds, $user) {
                $q->whereIn('department_id', $userDepartmentIds)
                  ->orWhere('agent_id', $user->id)
                  ->orWhere('user_id', $user->id);
            });
        }

        // Apply same filters as getTicketReports method
        $period = $request->input('period');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : null;

        if ($period) {
            switch ($period) {
                case 'daily':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'weekly':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'monthly':
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'quarterly':
                    $quarter = Carbon::now()->quarter;
                    $year = Carbon::now()->year;
                    $startOfQuarter = Carbon::createFromDate($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
                    $endOfQuarter = $startOfQuarter->copy()->addMonths(2)->endOfMonth();
                    $query->whereBetween('created_at', [$startOfQuarter, $endOfQuarter]);
                    break;
                case 'annually':
                    $query->whereYear('created_at', Carbon::now()->year);
                    break;
            }
        } elseif ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        // Apply additional filters
        if ($request->has('status_ids') && is_array($request->input('status_ids')) && !empty($request->input('status_ids'))) {
            $query->whereIn('status_id', array_map('intval', $request->input('status_ids')));
        }

        if ($request->has('priority_ids') && is_array($request->input('priority_ids')) && !empty($request->input('priority_ids'))) {
            $query->whereIn('priority_id', array_map('intval', $request->input('priority_ids')));
        }

        if ($request->has('department_ids') && is_array($request->input('department_ids')) && !empty($request->input('department_ids'))) {
            $query->whereIn('department_id', array_map('intval', $request->input('department_ids')));
        }

        if ($request->has('agent_ids') && is_array($request->input('agent_ids')) && !empty($request->input('agent_ids'))) {
            $query->whereIn('agent_id', array_map('intval', $request->input('agent_ids')));
        }

        if ($request->has('concern_ids') && is_array($request->input('concern_ids')) && !empty($request->input('concern_ids'))) {
            $query->whereIn('concern_id', array_map('intval', $request->input('concern_ids')));
        }

        if ($request->has('condo_location_ids') && is_array($request->input('condo_location_ids')) && !empty($request->input('condo_location_ids'))) {
            $query->whereIn('condo_location_id', array_map('intval', $request->input('condo_location_ids')));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'LIKE', '%'.$search.'%')
                  ->orWhereHas('ticketReplies', function($subQ) use ($search) {
                      $subQ->where('body', 'LIKE', '%'.$search.'%');
                  })
                  ->orWhereHas('user', function($subQ) use ($search) {
                      $subQ->where('name', 'LIKE', '%'.$search.'%')
                           ->orWhere('email', 'LIKE', '%'.$search.'%');
                  });
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        if ($tickets->isEmpty()) {
            return response()->json(['message' => 'No tickets found for the selected criteria.'], 404);
        }

        // Generate filename with timestamp
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "ticket_report_{$timestamp}.csv";

        // Create CSV content
        $csvData = $this->generateCsvData($tickets);

        // Return CSV file with Excel-optimized headers
        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Encoding', 'UTF-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Generate CSV data from tickets collection
     */
    private function generateCsvData($tickets)
    {
        $output = fopen('php://temp', 'r+');

        // Optimized CSV Headers - prioritizing most important columns first
        $headers = [
            'Ticket ID',
            'Subject',
            'Status',
            'Priority',
            'User Name',
            'User Email',
            'User Phone',
            'Unit Number',
            'Department',
            'Concern Category',
            'Agent Assigned',
            'Condo Location',
            'Description',
            'Voucher Code',
            'Created Date',
            'Last Updated',
            'Scheduled Visit',
            'Closed Date',
            'Closed By',
            'Response Time (Hrs)',
            'Resolution Time (Hrs)',
            'Labels',
            'Ticket UUID'
        ];

        fputcsv($output, $headers);

        // CSV Data - organized to match header order
        foreach ($tickets as $ticket) {
            $row = [
                // Core ticket information (most important first)
                $ticket->id,
                $this->cleanText($ticket->subject, 50), // Limit subject length
                $ticket->status ? $ticket->status->name : 'N/A',
                $ticket->priority ? $ticket->priority->name : 'N/A',

                // User information
                $ticket->user ? $this->cleanText($ticket->user->name, 30) : 'N/A',
                $ticket->user ? $ticket->user->email : 'N/A',
                $ticket->user ? $this->formatPhoneNumber($ticket->user->phone_number) : 'N/A',
                $ticket->user ? $ticket->user->unit_number : 'N/A',

                // Department and concern
                $ticket->department ? $this->cleanText($ticket->department->name, 25) : 'N/A',
                $ticket->concern ? $this->cleanText($ticket->concern->name, 30) : 'N/A',
                $ticket->agent ? $this->cleanText($ticket->agent->name, 25) : 'Unassigned',

                // Location
                $this->getCondoLocation($ticket),

                // Description (limited length for readability)
                $this->getTicketDescription($ticket, 100),

                // Additional details
                $ticket->voucher_code ?: '',

                // Timestamps (formatted consistently)
                $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i') : '',
                $ticket->updated_at ? $ticket->updated_at->format('Y-m-d H:i') : '',
                $ticket->scheduled_visit_at ? $ticket->scheduled_visit_at->format('Y-m-d H:i') : '',
                $ticket->closed_at ? $ticket->closed_at->format('Y-m-d H:i') : '',
                $ticket->closedBy ? $this->cleanText($ticket->closedBy->name, 25) : '',

                // Metrics
                $this->calculateResponseTime($ticket),
                $this->calculateResolutionTime($ticket),

                // Less critical information
                $ticket->labels ? $this->cleanText($ticket->labels->pluck('name')->implode(', '), 50) : '',
                $ticket->uuid
            ];

            fputcsv($output, $row);
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        // Add UTF-8 BOM for better Excel compatibility
        $csvData = "\xEF\xBB\xBF" . $csvData;

        return $csvData;
    }

    /**
     * Get ticket description from first reply with length limit
     */
    private function getTicketDescription($ticket, $maxLength = 100)
    {
        $firstReply = $ticket->ticketReplies()->orderBy('created_at', 'asc')->first();
        if ($firstReply) {
            $description = strip_tags($firstReply->body);
            return $this->cleanText($description, $maxLength);
        }
        return 'N/A';
    }

    /**
     * Clean and format text for CSV export
     */
    private function cleanText($text, $maxLength = null)
    {
        if (empty($text)) {
            return '';
        }

        // Remove extra whitespace and line breaks
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Remove or replace problematic characters
        $text = str_replace(['"', "\n", "\r", "\t"], ['""', ' ', ' ', ' '], $text);

        // Limit length if specified
        if ($maxLength && strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength - 3) . '...';
        }

        return $text;
    }

    /**
     * Format phone number consistently
     */
    private function formatPhoneNumber($phone)
    {
        if (empty($phone)) {
            return '';
        }

        // Clean the phone number
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Format if it's a Philippine number
        if (strlen($phone) == 11 && substr($phone, 0, 2) == '09') {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 3) . '-' . substr($phone, 7);
        }

        return $phone;
    }

    /**
     * Get condo location with fallback
     */
    private function getCondoLocation($ticket)
    {
        // Prefer ticket's condo location, fallback to user's condo location
        if ($ticket->condoLocation) {
            return $this->cleanText($ticket->condoLocation->name, 30);
        } elseif ($ticket->user && $ticket->user->condoLocation) {
            return $this->cleanText($ticket->user->condoLocation->name, 30);
        }
        return 'N/A';
    }

    /**
     * Calculate response time in hours with better formatting
     */
    private function calculateResponseTime($ticket)
    {
        $firstReply = $ticket->ticketReplies()
            ->whereHas('user.userRole', function($query) {
                $query->where('dashboard_access', 1);
            })
            ->orderBy('created_at', 'asc')
            ->first();

        if ($firstReply) {
            $diffInHours = $ticket->created_at->diffInHours($firstReply->created_at);

            // Format for better readability
            if ($diffInHours < 1) {
                $diffInMinutes = $ticket->created_at->diffInMinutes($firstReply->created_at);
                return $diffInMinutes . ' min';
            } elseif ($diffInHours < 24) {
                return round($diffInHours, 1) . ' hrs';
            } else {
                $days = floor($diffInHours / 24);
                $hours = $diffInHours % 24;
                return $days . 'd ' . round($hours, 0) . 'h';
            }
        }

        return 'Pending';
    }

    /**
     * Calculate resolution time in hours with better formatting
     */
    private function calculateResolutionTime($ticket)
    {
        if ($ticket->closed_at) {
            $diffInHours = $ticket->created_at->diffInHours($ticket->closed_at);

            // Format for better readability
            if ($diffInHours < 1) {
                $diffInMinutes = $ticket->created_at->diffInMinutes($ticket->closed_at);
                return $diffInMinutes . ' min';
            } elseif ($diffInHours < 24) {
                return round($diffInHours, 1) . ' hrs';
            } else {
                $days = floor($diffInHours / 24);
                $hours = $diffInHours % 24;
                return $days . 'd ' . round($hours, 0) . 'h';
            }
        }

        return 'Open';
    }
}
