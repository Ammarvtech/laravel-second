<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDurationForMovieAndEpisode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('movies', function(Blueprint $table){
            //
            $table->integer("length")->default(0);
        });

        Schema::table('episodes', function(Blueprint $table){
            //
            $table->integer("length")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('movies', function(Blueprint $table){
           $table->dropColumn(["length"]);
        });
        Schema::table('episodes', function(Blueprint $table){
           $table->dropColumn(["length"]);
        });
    }
}
