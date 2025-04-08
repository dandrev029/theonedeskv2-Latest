<?php

namespace Database\Seeders;

use App\Models\TicketConcern;
use Illuminate\Database\Seeder;

class TicketConcernsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (TicketConcern::count() === 0) {
            // Add some default ticket concerns
            $concerns = [
                'Technical Issue',
                'Billing Inquiry',
                'Service Request',
                'Complaint',
                'General Inquiry',
                'Maintenance Request',
                'Emergency'
            ];

            foreach ($concerns as $concern) {
                $ticketConcern = new TicketConcern();
                $ticketConcern->name = $concern;
                $ticketConcern->status = true;
                $ticketConcern->save();
            }
        }
    }
}
