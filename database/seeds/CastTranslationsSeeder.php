<?php

use Illuminate\Database\Seeder;

class CastTranslationsSeeder extends Seeder
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
                'cast_id' => 1,
                'locale'  => 'en',
                'name'    => 'Kareem abd el azez'
            ],
            [
                'cast_id' => 1,
                'locale'  => 'ar',
                'name'    => 'كريم عبد العزيز'
            ],
            [
                'cast_id' => 2,
                'locale'  => 'en',
                'name'    => 'Ahmed el saqa'
            ],
            [
                'cast_id' => 2,
                'locale'  => 'ar',
                'name'    => 'احمد السقا',
            ],
            [
                'cast_id' => 3,
                'locale'  => 'en',
                'name'    => 'Ahmed helmy'
            ],
            [
                'cast_id' => 3,
                'locale'  => 'ar',
                'name'    => 'احمد حلمى'
            ]
        ];

        DB::table('cast_translations')->insert($seed);
    }
}
