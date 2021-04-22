<?php

use Illuminate\Database\Seeder;

class AmountOptionsSeeder extends Seeder
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
                'name'  => 'amount',
                'value' => '',
                'type'  => 'number'
            ],
        ];
        DB::table('options')->insert($seed);
    }
}
