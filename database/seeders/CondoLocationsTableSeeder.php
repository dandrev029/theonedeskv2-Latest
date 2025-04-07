<?php

namespace Database\Seeders;

use App\Models\CondoLocation;
use Illuminate\Database\Seeder;

class CondoLocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (CondoLocation::count() === 0) {
            // Add some default condo locations
            $locations = [
                'Tower A',
                'Tower B',
                'Tower C',
                'Garden Villas',
                'Penthouse Suites'
            ];

            foreach ($locations as $location) {
                $condoLocation = new CondoLocation();
                $condoLocation->name = $location;
                $condoLocation->status = true;
                $condoLocation->save();
            }
        }
    }
}
