<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\TicketConcern;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class FixTicketConcernDepartments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:ticket-concern-departments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix ticket concern departments by creating default departments if they don\'t exist';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to fix ticket concern departments...');

        try {
            // Check if departments table exists
            if (!Schema::hasTable('departments')) {
                $this->error('Departments table does not exist');
                return 1;
            }

            // Get all departments
            $departments = Department::all();
            
            // If no departments found, create default ones
            if ($departments->count() === 0) {
                $this->info('No departments found. Creating default departments...');
                
                // Create default departments
                $defaultDepts = [
                    ['name' => 'DASMA General Helpdesk', 'public' => true, 'all_agents' => true],
                    ['name' => 'CAMPA General Helpdesk', 'public' => true, 'all_agents' => true],
                    ['name' => 'Dasma WiFi HELPDESK', 'public' => true, 'all_agents' => true],
                    ['name' => 'Campa WiFi Helpdesk', 'public' => true, 'all_agents' => true]
                ];

                $createdDepts = [];
                foreach ($defaultDepts as $dept) {
                    $department = Department::create($dept);
                    $createdDepts[] = $department;
                    $this->info("Created department: {$department->name} (ID: {$department->id})");
                }

                $this->info('Default departments created successfully');
            } else {
                $this->info("Found {$departments->count()} existing departments");
            }

            // Check for ticket concerns with invalid department IDs
            $this->info('Checking for ticket concerns with invalid department IDs...');
            $invalidConcerns = TicketConcern::whereNotNull('department_id')
                ->whereNotIn('department_id', Department::pluck('id'))
                ->get();

            if ($invalidConcerns->count() > 0) {
                $this->warn("Found {$invalidConcerns->count()} ticket concerns with invalid department IDs");
                
                // Get the first department to use as default
                $defaultDepartment = Department::first();
                
                if ($defaultDepartment) {
                    foreach ($invalidConcerns as $concern) {
                        $oldDeptId = $concern->department_id;
                        $concern->department_id = $defaultDepartment->id;
                        $concern->save();
                        $this->info("Updated ticket concern '{$concern->name}' from department ID {$oldDeptId} to {$defaultDepartment->id}");
                    }
                    $this->info('All invalid ticket concerns updated successfully');
                } else {
                    $this->error('No departments available to assign to invalid ticket concerns');
                    return 1;
                }
            } else {
                $this->info('No ticket concerns with invalid department IDs found');
            }

            $this->info('Ticket concern departments fixed successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
