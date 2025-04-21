<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixNotificationsCondoLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:notifications-condo-location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix notifications table by setting condo_location_id to NULL for existing records';

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
        $this->info('Starting to fix notifications table...');

        try {
            // Check if the notifications table exists
            if (!Schema::hasTable('notifications')) {
                $this->error('Notifications table does not exist');
                return 1;
            }

            // Check if the condo_location_id column exists
            if (!Schema::hasColumn('notifications', 'condo_location_id')) {
                $this->error('condo_location_id column does not exist in notifications table');
                return 1;
            }

            // Update all existing notifications to set condo_location_id to NULL
            $count = DB::table('notifications')
                ->whereNull('condo_location_id')
                ->update(['condo_location_id' => null]);

            $this->info("Fixed {$count} notifications by setting condo_location_id to NULL");

            // Try to update notifications with user information
            $this->info('Updating notifications with user condo_location_id...');
            
            // For notifications where notifiable_type is 'App\Models\User'
            $updatedCount = DB::statement("
                UPDATE notifications n
                JOIN users u ON n.notifiable_id = u.id
                SET n.condo_location_id = u.condo_location_id
                WHERE n.notifiable_type = 'App\\\\Models\\\\User'
            ");

            $this->info('Notifications table fixed successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
