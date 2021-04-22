<?php
$router->group(
    [
        'prefix'     => 'admin',
        'namespace'  => 'Backend',
        'as'         => 'Backend::',
        'middleware' => ['auth','admin']
    ], function() {

	Route::get('/', 'HomeController@index')->name('home');
    Route::get('statistics', 'HomeController@statistics')->name('statistics');
    Route::get('topWatching', 'HomeController@topWatching')->name('topWatching');
    Route::get('allFiles', 'HomeController@allFiles')->name('allFiles');
    Route::get('allUsers', 'HomeController@allUsers')->name('allUsers');
    Route::get('uploadedToday', 'HomeController@uploadedToday')->name('uploadedToday');
    Route::get('home/filter', 'HomeController@filter')->name('home.filter');
    // Shows
    Route::get('shows/filter', 'ShowsController@filter')->name('shows.filter');
    Route::post('shows/status', 'ShowsController@status')->name('shows.status');
    Route::resource('shows', 'ShowsController');

    // episode
    Route::get('episodes/filter', 'EpisodesController@filter')->name('episodes.filter');
    Route::get('shows/episodes/{id}', 'EpisodesController@listing')->name('shows.episodes');
    Route::post('episodes/status', 'EpisodesController@status')->name('episodes.status');
    Route::resource('episodes', 'EpisodesController');

    // movies
    Route::get('movies/filter', 'MoviesController@filter')->name('movies.filter');
    Route::post('movies/status', 'MoviesController@status')->name('movies.status');
	Route::resource('movies', 'MoviesController');

    // roles
    Route::get('roles/search', 'RolesController@filter')->name('roles.filter');
    Route::resource('roles', 'RolesController');

    // users
    Route::get('users/filter', 'UsersController@filter')->name('users.filter');
    Route::post('users/status', 'UsersController@status')->name('users.status');
    Route::get('users/details/{id}', 'UsersController@details')->name('users.details');
    Route::get('users/subscription/{id}', 'UsersController@extendSubscription')->name('users.ex-subscription');
    Route::post('users/update-subscription/{id}', 'UsersController@updateSubscription')->name('users.update.subscription');
    Route::post('users/removeDevices/{id}', 'UsersController@removeDevices')->name('users.remove');
    Route::get('users/logs/{id}', 'UsersController@logs')->name('logs');
    Route::resource('users', 'UsersController');

    // genres
    Route::get('genres/search', 'GenresController@filter')->name('genres.filter');
    Route::post('genres/status', 'GenresController@status')->name('genres.status');
    Route::resource('genres', 'GenresController');

    // casts
    Route::get('casts/filter', 'CastsController@filter')->name('casts.filter');
    Route::post('casts/status', 'CastsController@status')->name('casts.status');
    Route::resource('casts', 'CastsController');

    // options
    Route::post('options/slider', 'OptionsController@slider')->name('slider');
    Route::resource('options', 'OptionsController');

    Route::post('videos/type', 'VideosController@type')->name('videos.type');
    Route::post('videos/episodes', 'VideosController@episodes')->name('videos.episodes');
    Route::resource('videos', 'VideosController');

     // pages
    Route::get('pages/filter', 'PageController@filter')->name('pages.filter');
    Route::resource('pages', 'PageController');

    //Countries
    Route::get('countries/filter', 'CountriesController@filter')->name('countries.filter');
    Route::post('countries/status', 'CountriesController@status')->name('countries.status');
    Route::resource('countries', 'CountriesController');

    //config
    Route::get('config', 'OptionsController@edit')->name('config.index');
    Route::post('config/{id}', 'OptionsController@update')->name('config.update');

    //PDF
    Route::get('print/pdf', 'HomeController@printpdf')->name('print.pdf');     
});
