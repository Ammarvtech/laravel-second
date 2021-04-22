<?php

use Illuminate\Database\Seeder;

class optionsSeeder extends Seeder
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
                'name'  => 'slider',
                'value' => '[{"id":1,"type":"show","order":1},{"id":1,"type":"movie","order":2}]',
                'type'  => 'hidden'
            ]
        ];
        DB::table('options')->insert($seed);
    }
}
