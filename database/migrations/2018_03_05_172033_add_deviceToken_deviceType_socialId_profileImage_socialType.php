<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeviceTokenDeviceTypeSocialIdProfileImageSocialType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('social_id')->nullable();
            $table->string('social_type')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_token')->nullable();
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
        // Schema::table('users', function (Blueprint $table) {
        //     //
        //     $table->dropColumn([
        //         'social_id',
        //         'social_type',
        //         'profile_picture',
        //         'device_type',
        //         'device_token'
        //     ]);
        // });
    }
}
