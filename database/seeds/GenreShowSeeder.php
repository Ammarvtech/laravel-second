<?php

use Illuminate\Database\Seeder;

class GenreShowSeeder extends Seeder
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
                'genre_id' => 1,
                'show_id'  => 1
            ],
            [
                'genre_id' => 2,
                'show_id'  => 1
            ],
            [
                'genre_id' => 3,
                'show_id'  => 1
            ],
            [
                'genre_id' => 1,
                'show_id'  => 2
            ],
            [
                'genre_id' => 2,
                'show_id'  => 2
            ],
            [
                'genre_id' => 1,
                'show_id'  => 3
            ],
            [
                'genre_id' => 3,
                'show_id'  => 3
            ]
        ];
        DB::table('genre_show')->insert($seed);
    }
}
