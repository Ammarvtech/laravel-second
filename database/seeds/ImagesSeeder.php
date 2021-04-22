<?php

use Illuminate\Database\Seeder;

class ImagesSeeder extends Seeder
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
                'ext'            => 'jpeg',
                'title'          => '67730642da0664249dc59b746bb2e7368016',
                'size'           => '8361',
                'mime'           => 'image/jpeg',
                'original_title' => 'dummy.jpg',
                'type'           => 'thumbnail',
                'created_at'     => \Carbon\Carbon::now(),
            ]
        ];
        DB::table('images')->insert($seed);
    }
}
