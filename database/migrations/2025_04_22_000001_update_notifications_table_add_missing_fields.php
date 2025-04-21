<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateNotificationsTableAddMissingFields extends Migration
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
            // Add missing fields if they don't exist
            Schema::table('notifications', function (Blueprint $table) {
                // Add title field if it doesn't exist
                if (!Schema::hasColumn('notifications', 'title')) {
                    $table->string('title')->nullable()->after('data');
                }
                
                // Add message field if it doesn't exist
                if (!Schema::hasColumn('notifications', 'message')) {
                    $table->text('message')->nullable()->after('title');
                }
                
                // Add icon field if it doesn't exist
                if (!Schema::hasColumn('notifications', 'icon')) {
                    $table->string('icon')->nullable()->after('message');
                }
                
                // Add link field if it doesn't exist
                if (!Schema::hasColumn('notifications', 'link')) {
                    $table->string('link')->nullable()->after('icon');
                }
                
                // Add is_read field if it doesn't exist (Laravel uses read_at instead)
                if (!Schema::hasColumn('notifications', 'is_read')) {
                    $table->boolean('is_read')->default(false)->after('link');
                }
            });
            
            // Update existing records to set is_read based on read_at
            DB::statement('UPDATE notifications SET is_read = (read_at IS NOT NULL)');
            
            // Extract title, message, icon, and link from data JSON if possible
            $notifications = DB::table('notifications')->get();
            foreach ($notifications as $notification) {
                try {
                    $data = json_decode($notification->data, true);
                    $updates = [];
                    
                    if (isset($data['title']) && empty($notification->title)) {
                        $updates['title'] = $data['title'];
                    }
                    
                    if (isset($data['message']) && empty($notification->message)) {
                        $updates['message'] = $data['message'];
                    }
                    
                    if (isset($data['icon']) && empty($notification->icon)) {
                        $updates['icon'] = $data['icon'];
                    }
                    
                    if (isset($data['link']) && empty($notification->link)) {
                        $updates['link'] = $data['link'];
                    }
                    
                    if (!empty($updates)) {
                        DB::table('notifications')
                            ->where('id', $notification->id)
                            ->update($updates);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error updating notification data: ' . $e->getMessage(), [
                        'notification_id' => $notification->id,
                        'exception' => $e
                    ]);
                }
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
        // We don't want to remove the columns as it might break existing functionality
    }
}
