<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class FixDepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if departments table exists
        if (!Schema::hasTable('departments')) {
            $this->command->error('Departments table does not exist. Run migrations first.');
            return;
        }

        // Create default departments if none exist
        if (Department::count() === 0) {
            $this->command->info('No departments found. Creating default departments...');
            
            $departments = [
                [
                    'name' => 'DASMA General Helpdesk',
                    'public' => true,
                    'all_agents' => true,
                ],
                [
                    'name' => 'CAMPA General Helpdesk',
                    'public' => true,
                    'all_agents' => true,
                ],
                [
                    'name' => 'Dasma WiFi HELPDESK',
                    'public' => true,
                    'all_agents' => true,
                ],
                [
                    'name' => 'Campa WiFi Helpdesk',
                    'public' => true,
                    'all_agents' => true,
                ],
            ];

            foreach ($departments as $department) {
                Department::create($department);
            }
            
            $this->command->info('Default departments created successfully.');
        } else {
            $this->command->info('Departments already exist. Checking for required departments...');
            
            // Check for specific departments and add them if they don't exist
            $requiredDepartments = [
                'DASMA General Helpdesk',
                'CAMPA General Helpdesk',
                'Dasma WiFi HELPDESK',
                'Campa WiFi Helpdesk'
            ];
            
            foreach ($requiredDepartments as $deptName) {
                if (!Department::where('name', $deptName)->exists()) {
                    Department::create([
                        'name' => $deptName,
                        'public' => true,
                        'all_agents' => true,
                    ]);
                    $this->command->info("Added missing department: {$deptName}");
                }
            }
        }
    }
}
