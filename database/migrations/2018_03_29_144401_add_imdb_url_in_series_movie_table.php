<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImdbUrlInSeriesMovieTable extends Migration
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
            $table->text("imdb_url")->nullable();
        });

        Schema::table('shows', function(Blueprint $table){
            //
            $table->text("imdb_url")->nullable();
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
            //
            $table->dropColumn(["imdb_url"]);
        });

        Schema::table('shows', function(Blueprint $table){
            //
            $table->dropColumn(["imdb_url"]);
        });
    }
}
