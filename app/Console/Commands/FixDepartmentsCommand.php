<?php

namespace App\Console\Commands;

use App\Models\Department;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class FixDepartmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:departments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing departments in the database';

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
        $this->info('Starting department fix...');

        // Check if departments table exists
        if (!Schema::hasTable('departments')) {
            $this->error('Departments table does not exist. Run migrations first.');
            return 1;
        }

        // Create default departments if they don't exist
        $defaultDepts = [
            ['name' => 'DASMA General Helpdesk', 'public' => true, 'all_agents' => true],
            ['name' => 'CAMPA General Helpdesk', 'public' => true, 'all_agents' => true],
            ['name' => 'Dasma WiFi HELPDESK', 'public' => true, 'all_agents' => true],
            ['name' => 'Campa WiFi Helpdesk', 'public' => true, 'all_agents' => true]
        ];

        $created = [];
        $existing = [];

        foreach ($defaultDepts as $dept) {
            // Check if department already exists
            $existingDept = Department::where('name', $dept['name'])->first();

            if (!$existingDept) {
                // Create the department
                $department = new Department();
                $department->name = $dept['name'];
                $department->public = $dept['public'] ?? true;
                $department->all_agents = $dept['all_agents'] ?? true;
                $department->save();

                $created[] = $department->name;
                $this->info("Created department: {$department->name}");
            } else {
                $existing[] = $existingDept->name;
                $this->line("Department already exists: {$existingDept->name}");
            }
        }

        $this->info('Department fix completed.');
        $this->info('Created: ' . count($created) . ' departments');
        $this->info('Existing: ' . count($existing) . ' departments');

        return 0;
    }
}
