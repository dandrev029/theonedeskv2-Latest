<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusesColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Set default colors for statuses as specified
        // Open = RED, Pending = YELLOW, Resolved = GREEN, Closed = BLUE
        $statuses = [
            'Open' => '#FF0000',     // Red
            'Pending' => '#FFFF00',  // Yellow
            'Resolved' => '#00FF00', // Green
            'Closed' => '#0000FF',   // Blue
        ];

        foreach ($statuses as $name => $color) {
            $status = Status::where('name', $name)->first();
            if ($status) {
                $status->color = $color;
                $status->save();
            }
        }
    }
}
