<?php

use Illuminate\Database\Seeder;

class GenresTranslationsSeeder extends Seeder
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
                'title'    => 'comedy',
                'genre_id' => 1,
                'locale'    => 'en'
            ],
            [
                'title'    => 'كوميدى',
                'genre_id' => 1,
                'locale'    => 'ar'
            ],
            [
                'title'    => 'Drama',
                'genre_id' => 2,
                'locale'    => 'en'
            ],
            [
                'title'    => 'دراما',
                'genre_id' => 2,
                'locale'    => 'ar'
            ],
            [
                'title'    => 'Tragedy',
                'genre_id' => 3,
                'locale'    => 'en'
            ],
            [
                'title'    => 'تراجيدى',
                'genre_id' => 3,
                'locale'    => 'ar'
            ]
        ];

        DB::table('genre_translations')->insert($seed);
    }
}
