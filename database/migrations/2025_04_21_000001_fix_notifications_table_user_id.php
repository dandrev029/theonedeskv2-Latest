<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixNotificationsTableUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First check if the notifications table exists
        if (Schema::hasTable('notifications')) {
            // Check if the user_id column doesn't exist
            if (!Schema::hasColumn('notifications', 'user_id')) {
                // Add the user_id column
                Schema::table('notifications', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('id');
                });

                // Update existing records to set user_id from notifiable_id where notifiable_type is App\Models\User
                DB::statement('UPDATE notifications SET user_id = notifiable_id WHERE notifiable_type = "App\\\\Models\\\\User"');
            }
        }

        // Run a raw SQL query to fix the notifications table
        try {
            DB::statement('
                ALTER TABLE notifications
                MODIFY COLUMN user_id BIGINT UNSIGNED NULL DEFAULT NULL
            ');
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error fixing notifications table: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // We don't want to remove the column as it might break existing functionality
    }
}
