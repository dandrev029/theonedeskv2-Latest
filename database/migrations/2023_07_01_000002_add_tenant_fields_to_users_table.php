<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTenantFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'unit_number')) {
                $table->string('unit_number')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('users', 'condo_location_id')) {
                $table->foreignId('condo_location_id')->nullable()->after('unit_number')
                    ->constrained('condo_locations')->nullOnDelete();
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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'condo_location_id')) {
                $table->dropForeign(['condo_location_id']);
                $table->dropColumn('condo_location_id');
            }
            if (Schema::hasColumn('users', 'unit_number')) {
                $table->dropColumn('unit_number');
            }
            if (Schema::hasColumn('users', 'phone_number')) {
                $table->dropColumn('phone_number');
            }
        });
    }
}
