<?php

use Illuminate\Database\Seeder;

class CastShowSeeder extends Seeder
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
                'show_id' => 1
            ],
            [
                'cast_id' => 2,
                'show_id' => 1
            ],
            [
                'cast_id' => 1,
                'show_id' => 2
            ],
            [
                'cast_id' => 3,
                'show_id' => 2
            ],
            [
                'cast_id' => 2,
                'show_id' => 3
            ],
            [
                'cast_id' => 3,
                'show_id' => 3
            ]
        ];
        DB::table('cast_show')->insert($seed);
    }
}
