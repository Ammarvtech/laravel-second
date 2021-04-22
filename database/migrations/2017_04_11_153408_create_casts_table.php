
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCastsTable extends Migration
{
    public function up()
    {
        Schema::create('casts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique()->index();
            $table->integer('image_id')->unsigned()->default(0)->index();
            $table->timestamps();
        });

        Schema::create('cast_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cast_id')->unsigned()->index();
            $table->char('locale', 3)->index();
            $table->unique(['cast_id', 'locale']);
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
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
        Schema::dropIfExists('casts');
        Schema::dropIfExists('cast_translations');
    }
}
