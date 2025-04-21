<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixNotificationsTableSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the notifications table exists
        if (Schema::hasTable('notifications')) {
            // Add user_id column if it doesn't exist
            if (!Schema::hasColumn('notifications', 'user_id')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('id');
                });
            }

            // Make sure the user_id column is nullable
            try {
                DB::statement('ALTER TABLE notifications MODIFY user_id BIGINT UNSIGNED NULL');
            } catch (\Exception $e) {
                \Log::error('Error modifying user_id column: ' . $e->getMessage());
            }

            // Update existing records to set user_id from notifiable_id where notifiable_type is App\Models\User
            try {
                DB::statement("UPDATE notifications SET user_id = notifiable_id WHERE notifiable_type = 'App\\\\Models\\\\User'");
            } catch (\Exception $e) {
                \Log::error('Error updating user_id values: ' . $e->getMessage());
            }
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
