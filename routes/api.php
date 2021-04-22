<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', 'Api\UserController@register');
Route::post('login', 'Api\UserController@login');
Route::post('forget-password', 'Api\UserController@forgetPassword');
Route::post('subscribe', 'Api\UserController@userSubscription');

Route::group(['middleware' => ['jwt.auth', 'AccountSubscriptionPeriod']], function(){
    Route::get('payment', 'Api\UserController@paymentDetails');
    Route::post('payment', 'Api\UserController@payment');
    Route::post('edit-profile', 'Api\UserController@editProfile');
    Route::post('upload-avatar', 'Api\UserController@uploadImageProfile');
    Route::post('pause-track', 'Api\UserController@pauseTrack');
    Route::post('cancel-subscription', 'Api\UserController@cancelSubscription');
    Route::post('renewal-subscription', 'Api\UserController@renewalSubscription');
    Route::get('logout', 'Api\UserController@logout');
    /*
     * follow
     */
    Route::post('unfollow', 'Api\UserController@removeFromFollowList');
    Route::post('follow', 'Api\UserController@addToFollowList');
    Route::get('follow', 'Api\UserController@showFollowList');


    Route::get('home', 'Api\HomeController@index');
    Route::get('search', 'Api\HomeController@search');
    Route::get('trending', 'Api\HomeController@getTrending');
    Route::get('recently-added', 'Api\HomeController@getRecentlyAdded');
    Route::get('tv-shows', 'Api\HomeController@getTvShows');
    Route::get('coming-soon', 'Api\HomeController@getComingSoon');
    Route::get('picked-list', 'Api\HomeController@getPickedForYou');
    Route::get('continue-watching', 'Api\HomeController@getContinueWatching');

    Route::post('movies/paused', 'Api\MovieController@paused');
    Route::post('shows/paused', 'Api\EpisodesController@paused');
    Route::post('movies/review', 'Api\MovieController@review');
    Route::post('shows/review', 'Api\ShowController@review');

    Route::resource('movies', "Api\MovieController", [
        "only" => [
            'show',
            'index'
        ]
    ]);
    Route::resource('genres', "Api\GenreController", [
        "only" => [
            'show',
            'index'
        ]
    ]);
    Route::resource('shows', "Api\ShowController", [
        "only" => [
            'show',
            'index'
        ]
    ]);

    Route::resource("search", "Api\SearchController");

    Route::get("page/{slug?}", "Api\PageController@show");

    Route::get("notify/{type?}", "Api\HomeController@notify");
});
