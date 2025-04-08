<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignedToToTicketConcernsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_concerns', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_concerns', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_concerns', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_concerns', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            }
        });
    }
}
