<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGenresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('genre_translations', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('genre_movie', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('genre_id')->unsigned()->index();
            $table->integer('movie_id')->unsigned()->index();
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('cascade');
        });

        Schema::create('genre_show', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('genre_id')->unsigned()->index();
            $table->integer('show_id')->unsigned()->index();
            $table->foreign('show_id')->references('id')->on('shows')->onDelete('cascade');
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
        Schema::dropIfExists('genres');
        Schema::dropIfExists('genre_translations');
        Schema::dropIfExists('genre_show');
        Schema::dropIfExists('genre_movie');
    }
}
