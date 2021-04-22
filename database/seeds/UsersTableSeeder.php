<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seed = [
            'name'     => 'Muhammed',
            'email'    => 'admin@tasali.com',
            'password' => bcrypt('admin123'),
            'role_id'  => 2
        ];
        DB::table('users')->insert($seed);
    }
}
