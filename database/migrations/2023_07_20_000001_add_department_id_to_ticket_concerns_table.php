<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentIdToTicketConcernsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_concerns', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_concerns', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('assigned_to')->constrained('departments')->nullOnDelete();
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
            if (Schema::hasColumn('ticket_concerns', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }
}
