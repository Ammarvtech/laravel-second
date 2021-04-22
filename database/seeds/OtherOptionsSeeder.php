<?php

use Illuminate\Database\Seeder;

class OtherOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $seed = [
            [
                'name'  => 'trial',
                'value' => '30',
                'type'  => 'number'
            ],
            [
                'name'  => 'freeSignUp',
                'value' => 'true',
                'type'  => 'hidden'
            ],
            [
                'name' => 'subscription_duration',
                'value' => '30',
                'type' => 'number'
            ]
        ];
        DB::table('options')->insert($seed);
    }
}
