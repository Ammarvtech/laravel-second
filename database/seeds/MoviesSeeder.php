<?php

use Illuminate\Database\Seeder;

class MoviesSeeder extends Seeder
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
                'slug'         => 'eljazera',
                'production'   => '2014',
                'poster_id'       => 1,
                'image_id'     => 1,
                'age'          => '17+',
                'publish_date' => \Carbon\Carbon::now(),
            ],
            [
                'slug'         => 'alf-mabrook',
                'production'   => '2015',
                'poster_id'       => 1,
                'image_id'     => 1,
                'age'          => '13',
                'publish_date' => \Carbon\Carbon::now(),
            ],
            [
                'slug'         => 'bdl-faqed',
                'production'   => '2013',
                'poster_id'       => 1,
                'image_id'     => 1,
                'age'          => '14',
                'publish_date' => \Carbon\Carbon::now(),
            ]
        ];
        DB::table('movies')->insert($seed);
    }
}
