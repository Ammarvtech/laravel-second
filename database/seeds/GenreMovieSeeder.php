<?php

use Illuminate\Database\Seeder;

class GenreMovieSeeder extends Seeder
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
                'movie_id' => 1
            ],
            [
                'genre_id' => 2,
                'movie_id' => 2
            ],
            [
                'genre_id' => 2,
                'movie_id' => 2
            ],
            [
                'genre_id' => 3,
                'movie_id' => 2
            ],
            [
                'genre_id' => 1,
                'movie_id' => 3
            ],
            [
                'genre_id' => 2,
                'movie_id' => 3,
            ],
            [
                'genre_id' => 3,
                'movie_id' => 3
            ]
        ];
        DB::table('genre_movie')->insert($seed);
    }
}
