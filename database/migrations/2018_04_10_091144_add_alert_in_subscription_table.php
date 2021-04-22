<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAlertInSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function(Blueprint $table){
            //
            $table->boolean('is_alerted')->default(true);
            $table->string('plan_name')->nullable();
        });

        Schema::table('credit_cards', function(Blueprint $table){
            //
            $table->string('name_on_card')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function(Blueprint $table){
            //
            $table->dropColumn(['is_alerted', 'plan_name']);
        });
        Schema::table('credit_cards', function(Blueprint $table){
            //
            $table->dropColumn(['name_on_card']);
        });
    }
}
