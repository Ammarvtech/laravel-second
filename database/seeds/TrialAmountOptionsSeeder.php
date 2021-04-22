<?php

use Illuminate\Database\Seeder;

class TrialAmountOptionsSeeder extends Seeder
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
                'name'  => 'trial_amount',
                'value' => '',
                'label' => 'site_config',
                'type'  => 'number'
            ],
        ];
        DB::table('options')->insert($seed);
    }
}
