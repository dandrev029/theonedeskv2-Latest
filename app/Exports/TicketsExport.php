<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class TicketsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $tickets;

    public function __construct(Collection $tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->tickets->map(function ($ticket) {
            return [
                'ID' => $ticket->id,
                'UUID' => $ticket->uuid,
                'Subject' => $ticket->subject,
                'Description' => $this->getTicketDescription($ticket),
                'User Name' => $ticket->user ? $ticket->user->name : 'N/A',
                'User Email' => $ticket->user ? $ticket->user->email : 'N/A',
                'User Phone' => $ticket->user ? $ticket->user->phone_number : 'N/A',
                'Unit Number' => $ticket->user ? $ticket->user->unit_number : 'N/A',
                'User Condo Location' => $ticket->user && $ticket->user->condoLocation ? $ticket->user->condoLocation->name : 'N/A',
                'Ticket Condo Location' => $ticket->condoLocation ? $ticket->condoLocation->name : 'N/A',
                'Department' => $ticket->department ? $ticket->department->name : 'N/A',
                'Concern' => $ticket->concern ? $ticket->concern->name : 'N/A',
                'Concern Department' => $ticket->concern && $ticket->concern->department ? $ticket->concern->department->name : 'N/A',
                'Status' => $ticket->status ? $ticket->status->name : 'N/A',
                'Priority' => $ticket->priority ? $ticket->priority->name : 'N/A',
                'Priority Value' => $ticket->priority ? $ticket->priority->value : 'N/A',
                'Agent Name' => $ticket->agent ? $ticket->agent->name : 'N/A',
                'Agent Email' => $ticket->agent ? $ticket->agent->email : 'N/A',
                'Labels' => $ticket->labels->isNotEmpty() ? $ticket->labels->pluck('name')->implode(', ') : '',
                'Voucher Code' => $ticket->voucher_code ?: '',
                'Scheduled Visit' => $ticket->scheduled_visit_at ? $ticket->scheduled_visit_at->format('Y-m-d H:i:s') : '',
                'Created At' => $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i:s') : 'N/A',
                'Updated At' => $ticket->updated_at ? $ticket->updated_at->format('Y-m-d H:i:s') : 'N/A',
                'Closed At' => $ticket->closed_at ? $ticket->closed_at->format('Y-m-d H:i:s') : '',
                'Closed By' => $ticket->closedBy ? $ticket->closedBy->name : '',
                'Response Time (Hours)' => $this->calculateResponseTime($ticket),
                'Resolution Time (Hours)' => $this->calculateResolutionTime($ticket),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Subject',
            'Description',
            'User Name',
            'User Email',
            'User Phone',
            'Unit Number',
            'User Condo Location',
            'Ticket Condo Location',
            'Department',
            'Concern',
            'Concern Department',
            'Status',
            'Priority',
            'Priority Value',
            'Agent Name',
            'Agent Email',
            'Labels',
            'Voucher Code',
            'Scheduled Visit',
            'Created At',
            'Updated At',
            'Closed At',
            'Closed By',
            'Response Time (Hours)',
            'Resolution Time (Hours)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Set background color for header
            'A1:AA1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF18A5A7']
                ],
                'font' => [
                    'color' => ['argb' => 'FFFFFFFF'],
                    'bold' => true
                ]
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'V' => NumberFormat::FORMAT_DATE_DATETIME, // Created At
            'W' => NumberFormat::FORMAT_DATE_DATETIME, // Updated At
            'X' => NumberFormat::FORMAT_DATE_DATETIME, // Closed At
            'U' => NumberFormat::FORMAT_DATE_DATETIME, // Scheduled Visit
            'Z' => NumberFormat::FORMAT_NUMBER_00, // Response Time
            'AA' => NumberFormat::FORMAT_NUMBER_00, // Resolution Time
        ];
    }

    /**
     * Get ticket description from first reply
     */
    private function getTicketDescription($ticket)
    {
        $firstReply = $ticket->ticketReplies()->orderBy('created_at', 'asc')->first();
        if ($firstReply) {
            return strip_tags($firstReply->body);
        }
        return 'N/A';
    }

    /**
     * Calculate response time in hours
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
            return round($diffInHours, 2);
        }

        return 'N/A';
    }

    /**
     * Calculate resolution time in hours
     */
    private function calculateResolutionTime($ticket)
    {
        if ($ticket->closed_at) {
            $diffInHours = $ticket->created_at->diffInHours($ticket->closed_at);
            return round($diffInHours, 2);
        }

        return 'N/A';
    }
}
