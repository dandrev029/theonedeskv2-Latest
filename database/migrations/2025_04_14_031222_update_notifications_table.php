<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('title');
                $table->text('message');
                $table->string('type')->default('general'); // general, ticket, system, etc.
                $table->string('icon')->nullable();
                $table->text('data')->nullable(); // JSON data for additional info
                $table->boolean('is_read')->default(false);
                $table->string('link')->nullable(); // URL to redirect when clicked
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } else {
            Schema::table('notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('notifications', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->after('id');
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
                
                if (!Schema::hasColumn('notifications', 'title')) {
                    $table->string('title')->after('user_id');
                }
                
                if (!Schema::hasColumn('notifications', 'message')) {
                    $table->text('message')->after('title');
                }
                
                if (!Schema::hasColumn('notifications', 'type')) {
                    $table->string('type')->default('general')->after('message');
                }
                
                if (!Schema::hasColumn('notifications', 'icon')) {
                    $table->string('icon')->nullable()->after('type');
                }
                
                if (!Schema::hasColumn('notifications', 'data')) {
                    $table->text('data')->nullable()->after('icon');
                }
                
                if (!Schema::hasColumn('notifications', 'is_read')) {
                    $table->boolean('is_read')->default(false)->after('data');
                }
                
                if (!Schema::hasColumn('notifications', 'link')) {
                    $table->string('link')->nullable()->after('is_read');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // We don't want to drop the table if it already existed
        // Instead, we'll just remove the columns we added
        Schema::table('notifications', function (Blueprint $table) {
            // Only drop columns if they exist and were added by this migration
            $columns = ['user_id', 'title', 'message', 'type', 'icon', 'data', 'is_read', 'link'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
