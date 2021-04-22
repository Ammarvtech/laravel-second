<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique()->index();
            $table->integer('production')->nullable();
            $table->integer('poster')->unsigned()->nullable();
            $table->integer('image_id')->unsigned()->nullable();
            $table->string('age');
            $table->date('publish_date')->nullable();
            $table->timestamps();
        });

        Schema::create('movie_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('movie_id')->unsigned()->index();
            $table->string('title');
            $table->string('publish_country')->nullable();
            $table->text('desc')->nullable();
            $table->char('locale', 3);
            $table->unique(['movie_id', 'locale']);
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
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
        Schema::dropIfExists('movies');
        Schema::dropIfExists('movie_translations');
    }
}
