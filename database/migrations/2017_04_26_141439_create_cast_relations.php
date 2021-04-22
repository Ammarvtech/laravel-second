<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCastRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cast_movie', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cast_id')->unsigned()->index();
            $table->integer('movie_id')->unsigned()->index();
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
        });

        Schema::create('cast_show', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cast_id')->unsigned()->index();
            $table->integer('show_id')->unsigned()->index();
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->foreign('show_id')->references('id')->on('shows')->onDelete('cascade');
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
        Schema::dropIfExists('cast_movie');
        Schema::dropIfExists('cast_show');
    }
}
