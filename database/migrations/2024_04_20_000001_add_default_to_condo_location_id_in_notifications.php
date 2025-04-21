<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultToCondoLocationIdInNotifications extends Migration
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
            // Check if the condo_location_id column exists
            if (Schema::hasColumn('notifications', 'condo_location_id')) {
                // Make the column nullable to fix the immediate issue
                Schema::table('notifications', function (Blueprint $table) {
                    $table->foreignId('condo_location_id')->nullable()->change();
                });
            } else {
                // If the column doesn't exist, add it as nullable
                Schema::table('notifications', function (Blueprint $table) {
                    $table->foreignId('condo_location_id')->nullable()->after('notifiable_id');
                });
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
        // We don't want to remove the column, just revert it to non-nullable if it exists
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'condo_location_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreignId('condo_location_id')->nullable(false)->change();
            });
        }
    }
}
