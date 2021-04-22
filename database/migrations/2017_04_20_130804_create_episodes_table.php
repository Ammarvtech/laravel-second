<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('image_id')->unsigned()->nullable();
            $table->integer('show_id')->unsigned()->index();
            $table->timestamps();
            $table->foreign('show_id')->references('id')->on('shows')->onDelete('cascade');
            $table->foreign('image_id')->references('id')->on('images')->onDelete('cascade');
        });

        Schema::create('episode_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('episode_id')->unsigned();
            $table->foreign('episode_id')->references('id')->on('episodes')->onDelete('cascade');
            $table->string('title');
            $table->char('locale', 3)->index();
            $table->unique(['episode_id', 'locale']);
        });

        Schema::table('genre_translations', function (Blueprint $table) {
            $table->string('title');
            $table->integer('genre_id')->unsigned()->index();
            $table->char('locale', 3)->index();
            $table->unique(['genre_id', 'locale']);
            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('cascade');
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
        Schema::dropIfExists('episodes');
        Schema::dropIfExists('episode_translations');
        Schema::dropIfExists('genre_translations');
    }
}
