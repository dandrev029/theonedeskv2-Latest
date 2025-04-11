<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create default departments
        $departments = [
            [
                'name' => 'IT Support',
                'public' => true,
                'all_agents' => true,
            ],
            [
                'name' => 'Customer Service',
                'public' => true,
                'all_agents' => true,
            ],
            [
                'name' => 'Maintenance',
                'public' => true,
                'all_agents' => true,
            ],
            [
                'name' => 'Security',
                'public' => true,
                'all_agents' => true,
            ],
            [
                'name' => 'Billing',
                'public' => true,
                'all_agents' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
