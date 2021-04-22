<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSeoFieldsToGenresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('genres', function (Blueprint $table) {
            $table->string("meta_tags",500)->nullable();
            $table->longText("meta_description")->nullable();
            $table->integer("sort_order")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('genres', function (Blueprint $table) {
            $table->dropColumn(["meta_tags"]);
            $table->dropColumn(["meta_description"]);
            $table->dropColumn(["sort_order"]);
        });
    }
}
