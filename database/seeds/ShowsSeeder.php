<?php

use Illuminate\Database\Seeder;

class ShowsSeeder extends Seeder
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
                'slug'         => 'slsal-el-dm',
                'season'       => 2,
                'production'   => '2014',
                'poster_id'       => 1,
                'image_id'     => 1,
                'age'          => '15+',
                'publish_date' => \Carbon\Carbon::now()
            ],
            [
                'slug'         => 'el-ostora',
                'season'       => 1,
                'production'   => '2016',
                'poster_id'       => 1,
                'image_id'     => 1,
                'age'          => '15+',
                'publish_date' => \Carbon\Carbon::now()
            ],
            [
                'slug'         => 'sahb=el-saada',
                'season'       => 1,
                'production'   => '2016',
                'poster_id'       => 1,
                'image_id'     => 1,
                'age'          => '15+',
                'publish_date' => \Carbon\Carbon::now()
            ]
        ];
        DB::table('shows')->insert($seed);
    }
}
