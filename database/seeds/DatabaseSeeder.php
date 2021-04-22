<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(optionsSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(ImagesSeeder::class);
        $this->call(CastsSeeder::class);
        $this->call(GenresSeeder::class);
        $this->call(GenresTranslationsSeeder::class);
        $this->call(CastTranslationsSeeder::class);
        $this->call(ShowsSeeder::class);
        $this->call(MoviesSeeder::class);
        $this->call(MoviesTranslationsSeeder::class);
        $this->call(ShowsTranslationsSeeder::class);
        $this->call(EpisodesSeeder::class);
        $this->call(EpisodeTranslationsSeeder::class);
        $this->call(CastMovieSeeder::class);
        $this->call(CastShowSeeder::class);
        $this->call(GenreMovieSeeder::class);
        $this->call(GenreShowSeeder::class);
        $this->call(OtherOptionsSeeder::class);
        $this->call(PagesSeederTable::class);
        $this->call(PagesTranslationsSeeder::class);
        $this->call(AmountOptionsSeeder::class);
        $this->call(LabelOptionsSeeder::class);
        $this->call(TrialAmountOptionsSeeder::class);
        $this->call(DurationOptionsSeeder::class);
        $this->call(UpdatePermissionsTableSeeder::class);
        $this->call(UpdatePermissionRoleTableSeeder::class);
    }
}
