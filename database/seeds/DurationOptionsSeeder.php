<?php

use Illuminate\Database\Seeder;

class DurationOptionsSeeder extends Seeder
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
                'name' => 'subscription_duration',
                'value' => '30',
                'type' => 'number',
                'label'  => 'site_config'
            ],
        ];
        DB::table('options')->insert($seed);
    }
}
