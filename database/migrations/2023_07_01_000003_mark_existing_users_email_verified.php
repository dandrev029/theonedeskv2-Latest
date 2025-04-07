<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarkExistingUsersEmailVerified extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Mark all existing users' emails as verified
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => Carbon::now()]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to revert this as we don't know which users were previously unverified
    }
}
