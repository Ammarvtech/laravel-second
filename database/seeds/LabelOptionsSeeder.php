<?php

use Illuminate\Database\Seeder;

class LabelOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seed = [
                'label'  => 'site_config'
        ];
        DB::table('options')->update($seed);
    }
}
