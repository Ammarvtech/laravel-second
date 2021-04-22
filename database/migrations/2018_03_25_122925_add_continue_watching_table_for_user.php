<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContinueWatchingTableForUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('continue_watching', function(Blueprint $table){
            $table->increments('id');
            $table->integer('movie_id')->nullable();
            $table->integer('series_id')->nullable();
            $table->integer("episode_id")->nullable();
            $table->integer('user_id')->nullable();
            $table->string('paused_at')->nullable();
            $table->string('percent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('continue_watching');
    }
}
