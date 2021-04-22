<?php

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
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
                'name'  => 'c-panel-login',
                'label' => 'Control Panel Login',
                'type'  => 'view',
                'order' => 0
            ],
            [
                'name'  => 'roles-view',
                'label' => 'Privileges',
                'type'  => 'view',
                'order' => 1
            ],
            [
                'name'  => 'roles-add',
                'label' => 'Privileges',
                'type'  => 'add',
                'order' => 1
            ],
            [
                'name'  => 'roles-edit',
                'label' => 'Privileges',
                'type'  => 'edit',
                'order' => 1
            ],
            [
                'name'  => 'roles-delete',
                'label' => 'Privileges',
                'type'  => 'delete',
                'order' => 1
            ],
            [
                'name'  => 'users-view',
                'label' => 'Users',
                'type'  => 'view',
                'order' => 2
            ],
            [
                'name'  => 'users-add',
                'label' => 'Users',
                'type'  => 'add',
                'order' => 2
            ],
            [
                'name'  => 'users-edit',
                'label' => 'Users',
                'type'  => 'edit',
                'order' => 2
            ],
            [
                'name'  => 'users-delete',
                'label' => 'Users',
                'type'  => 'delete',
                'order' => 2
            ],

        ];
        DB::table('permissions')->insert($seed);
    }
}
