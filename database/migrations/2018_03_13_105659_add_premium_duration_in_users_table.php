<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPremiumDurationInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
//            $table->renameColumn('is_verified','is_premium');
            $table->boolean('is_premium')->default(0);
            $table->timestamp('premium_start_date')->nullable();
            $table->timestamp('premium_end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        // Schema::table('users', function (Blueprint $table) {
        //     //
        //     $table->dropColumn(["premium_start_date","premium_end_date"]);
        // });
    }
}
