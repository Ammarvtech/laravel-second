<?php

use Illuminate\Database\Seeder;

class GenresSeeder extends Seeder
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
                'id' => 1,
                'slug' => 'comedy'
            ],
            [
                'id' => 2,
                'slug' => 'drama'
            ],
            [
                'id' => 3,
                'slug' => 'tragedy'
            ]
        ];
        DB::table('genres')->insert($seed);
    }
}
