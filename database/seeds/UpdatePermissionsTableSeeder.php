<?php

use Illuminate\Database\Seeder;

class UpdatePermissionsTableSeeder extends Seeder
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
                'name'  => 'shows-view',
                'label' => 'Shows',
                'type'  => 'view',
                'order' => 3
            ],
            [
                'name'  => 'shows-add',
                'label' => 'Shows',
                'type'  => 'add',
                'order' => 3
            ],
            [
                'name'  => 'shows-edit',
                'label' => 'Shows',
                'type'  => 'edit',
                'order' => 3
            ],
            [
                'name'  => 'shows-delete',
                'label' => 'Shows',
                'type'  => 'delete',
                'order' => 3
            ],
            [
                'name'  => 'episodes-view',
                'label' => 'Episodes',
                'type'  => 'view',
                'order' => 4
            ],
            [
                'name'  => 'episodes-add',
                'label' => 'Episodes',
                'type'  => 'add',
                'order' => 4
            ],
            [
                'name'  => 'episodes-edit',
                'label' => 'Episodes',
                'type'  => 'edit',
                'order' => 4
            ],
            [
                'name'  => 'episodes-delete',
                'label' => 'Episodes',
                'type'  => 'delete',
                'order' => 4
            ],
            [
                'name'  => 'movies-view',
                'label' => 'Movies',
                'type'  => 'view',
                'order' => 5
            ],
            [
                'name'  => 'movies-add',
                'label' => 'Movies',
                'type'  => 'add',
                'order' => 5
            ],
            [
                'name'  => 'movies-edit',
                'label' => 'Movies',
                'type'  => 'edit',
                'order' => 5
            ],
            [
                'name'  => 'movies-delete',
                'label' => 'Movies',
                'type'  => 'delete',
                'order' => 5
            ],
            [
                'name'  => 'genres-view',
                'label' => 'Genres',
                'type'  => 'view',
                'order' => 6
            ],
            [
                'name'  => 'genres-add',
                'label' => 'Genres',
                'type'  => 'add',
                'order' => 6
            ],
            [
                'name'  => 'genres-edit',
                'label' => 'Genres',
                'type'  => 'edit',
                'order' => 6
            ],
            [
                'name'  => 'genres-delete',
                'label' => 'Genres',
                'type'  => 'delete',
                'order' => 6
            ],
            [
                'name'  => 'casts-view',
                'label' => 'Casts',
                'type'  => 'view',
                'order' => 7
            ],
            [
                'name'  => 'casts-add',
                'label' => 'Casts',
                'type'  => 'add',
                'order' => 7
            ],
            [
                'name'  => 'casts-edit',
                'label' => 'Casts',
                'type'  => 'edit',
                'order' => 7
            ],
            [
                'name'  => 'casts-delete',
                'label' => 'Casts',
                'type'  => 'delete',
                'order' => 7
            ],
            [
                'name'  => 'pages-view',
                'label' => 'Pages',
                'type'  => 'view',
                'order' => 8
            ],
            [
                'name'  => 'pages-add',
                'label' => 'Pages',
                'type'  => 'add',
                'order' => 8
            ],
            [
                'name'  => 'pages-edit',
                'label' => 'Pages',
                'type'  => 'edit',
                'order' => 8
            ],
            [
                'name'  => 'pages-delete',
                'label' => 'Pages',
                'type'  => 'delete',
                'order' => 8
            ],
            [
                'name'  => 'videos-view',
                'label' => 'Videos',
                'type'  => 'view',
                'order' => 9
            ],
            [
                'name'  => 'videos-add',
                'label' => 'Videos',
                'type'  => 'add',
                'order' => 9
            ],
            [
                'name'  => 'videos-edit',
                'label' => 'Videos',
                'type'  => 'edit',
                'order' => 9
            ],
            [
                'name'  => 'videos-delete',
                'label' => 'Videos',
                'type'  => 'delete',
                'order' => 9
            ],
            [
                'name'  => 'config-view',
                'label' => 'Config',
                'type'  => 'view',
                'order' => 10
            ],
            [
                'name'  => 'config-add',
                'label' => 'Config',
                'type'  => 'add',
                'order' => 10
            ],
            [
                'name'  => 'config-edit',
                'label' => 'Config',
                'type'  => 'edit',
                'order' => 10
            ],
            [
                'name'  => 'config-delete',
                'label' => 'Config',
                'type'  => 'delete',
                'order' => 10
            ],

        ];
        DB::table('permissions')->insert($seed);
    }
}
