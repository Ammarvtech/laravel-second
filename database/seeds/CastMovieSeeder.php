<?php

use Illuminate\Database\Seeder;

class CastMovieSeeder extends Seeder
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
                'cast_id'  => 1,
                'movie_id' => 1
            ],
            [
                'cast_id'  => 2,
                'movie_id' => 1
            ],
            [
                'cast_id'  => 1,
                'movie_id' => 2
            ],
            [
                'cast_id'  => 3,
                'movie_id' => 2
            ],
            [
                'cast_id'  => 2,
                'movie_id' => 3
            ],
            [
                'cast_id'  => 3,
                'movie_id' => 3
            ]
        ];
        DB::table('cast_movie')->insert($seed);
    }
}
