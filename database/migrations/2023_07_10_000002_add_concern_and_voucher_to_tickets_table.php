<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConcernAndVoucherToTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'concern_id')) {
                $table->foreignId('concern_id')->nullable()->after('subject')->constrained('ticket_concerns')->nullOnDelete();
            }
            if (!Schema::hasColumn('tickets', 'voucher_code')) {
                $table->string('voucher_code')->nullable()->after('concern_id');
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
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'concern_id')) {
                $table->dropForeign(['concern_id']);
                $table->dropColumn('concern_id');
            }
            if (Schema::hasColumn('tickets', 'voucher_code')) {
                $table->dropColumn('voucher_code');
            }
        });
    }
}
