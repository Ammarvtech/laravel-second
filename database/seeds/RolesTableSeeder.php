<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seed = [
            [
                'name'  => 'subscriber',
                'label' => 'Subscriber'
            ],
            [
                'name'  => 'admin',
                'label' => 'Administrator'
            ]
        ];
        DB::table('roles')->insert($seed);
    }
}
