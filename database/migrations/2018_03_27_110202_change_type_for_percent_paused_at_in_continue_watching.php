<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeForPercentPausedAtInContinueWatching extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('continue_watching', function(Blueprint $table){
            //
//            $table->dropColumn(["percent", "paused_at"]);
            $table->float("percent")->nuallable()->change();
            $table->float("paused_at")->nuallable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('continue_watching', function(Blueprint $table){
            //
            $table->dropColumn(["percent", "paused_at"]);
        });
    }
}
