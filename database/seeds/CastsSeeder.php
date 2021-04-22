<?php

use Illuminate\Database\Seeder;

class CastsSeeder extends Seeder
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
                'id'       => 1,
                'slug'     => 'kareem-abd-elazez',
                'image_id' => 1
            ],
            [
                'id'       => 2,
                'slug'     => 'ahmed-elsaqa',
                'image_id' => 1
            ],
            [
                'id'       => 3,
                'slug'     => 'ahmed-helmy',
                'image_id' => 1
            ]
        ];
        DB::table('casts')->insert($seed);
    }
}
