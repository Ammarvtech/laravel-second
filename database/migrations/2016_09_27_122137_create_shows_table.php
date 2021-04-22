<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShowsTable extends Migration
{
    public function up()
    {
        Schema::create('shows', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique()->index();
            $table->integer('season')->unsigned()->default(1);
            $table->integer('production')->unsigned()->nullable();
            $table->integer('poster')->unsigned();
            $table->integer('image_id')->unsigned();
            $table->string('age');
            $table->date('publish_date')->nullable();
            $table->timestamps();
        });

        Schema::create('show_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('show_id')->unsigned()->index();
            $table->string('title');
            $table->string('publish_country')->nullable();
            $table->text('desc')->nullable();
            $table->char('locale', 3);
            $table->unique(['show_id', 'locale']);
            $table->foreign('show_id')->references('id')->on('shows')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::drop('shows');
        Schema::drop('show_translations');
    }
}
