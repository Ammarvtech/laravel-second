<?php
Route::get('allowme', 'HomeController@allow_me');
Route::any('contactUs', 'HomeController@contactUs');
Route::delete('remove/device/{id}', 'HomeController@removeDevice')->name('remove.device');
//Route::get('thumbnails', 'HomeController@makeThumbnails');
$router->group([
	'namespace' => 'Frontend',
	'middleware' => 'localization'

], function () use ($router) {

	Route::get('language/{lang}', 'HomeController@changeLanguage')->name('lang');
	//Route::get('country', 'HomeController@country')->name('country');

	Route::get('/', 'HomeController@index')->name('home')->middleware('premium');
	Auth::routes();
	Route::get('admin/login', 'Auth\AuthController@getLogin');
	Route::post('admin/login', 'Auth\AuthController@postLogin');

	//facebook login
	Route::get('login/facebook', 'Auth\LoginController@redirectToProvider')->name('loginFacebook');
	Route::any('login/callback/facebook', 'Auth\LoginController@handleProviderCallback')->name('callbackFacebook');

	// register steps
	Route::get('register/payment', 'PaymentController@step2')->name('registerStep2')->middleware('premimum.true');
	Route::get('register/pay', 'PaymentController@step3')->name('registerStep3')->middleware('premimum.true');
	Route::post('register/payment', 'PaymentController@payment')->name('payment');
	Route::post('register/stripe', 'PaymentController@stripePayment')->name('stripe');
	Route::any('register/renewPackage', 'PaymentController@renewPackage')->name('renewPackage');
	Route::post('register/paypalPayment', 'PaymentController@paypalPayment');
	Route::any('register/paypalCheckout', 'PaymentController@paypalCheckout');
	Route::get('get-cities-list', 'Auth\RegisterController@getCitiesList');

	//Route::get('register/pay', 'Auth\RegisterController@step3')->name('registerStep3');

	//logout
	Route::get('/logout', 'Auth\LoginController@logout')
		->name('logout');

	//payment
	Route::get('payment/callback', 'PaymentController@response')->name('payment_callback');

	//pages
	Route::get('page/{slug}', 'PageController@show')->name('page.show');


	// authenticated users only
	$router->middleware(['auth', 'premium'])->group(function () {
		Route::get('movies/{slug}', 'MoviesController@show')->name('single.movie');
		Route::get('movies', 'MoviesController@index')->name('movie');
		Route::get('shows/{slug}', 'ShowsController@show')->name('single.show');
		Route::get('shows', 'ShowsController@index')->name('show');
		Route::get('genres/{slug}', 'GenresController@show')->name('genre');
		Route::get('search', 'HomeController@search')->name('search');
		Route::get('episode/{id}', 'EpisodesController@show')->name('episode');
		Route::get('related/{id}', 'MoviesController@related')->name('related');
		// video
		Route::get('video/{type}/{id}', 'VideosController@get')->name('video');
		//profile
		Route::get('profile', 'UserController@profile')->name('profile');
		Route::get('profile/edit/{field}', 'UserController@editProfile')->name('editProfile');
		Route::post('profile/update', 'UserController@updateProfile')->name('updateProfile');
		
		/////////////////////////// Manage Profile  ////////////////////////////////
		Route::get('all-profiles', 'UserController@allProfiles')->name('all.profiles');
		Route::get('profile/setting/{id}', 'UserController@profileSetting')->name('profile.setting');
		Route::get('profile/delete/{id}', 'UserController@deleteProfile')->name('remove.profile');
		Route::get('add-profile', 'UserController@addProfile')->name('add.profile');
		Route::post('store-profile', 'UserController@storeProfile')->name('store.profile');
		Route::post('profile/user/update', 'UserController@updateUserProfile')->name('update.profile');
		Route::get('user/edit/{field}/{id}', 'UserController@editUserProfile');
		
		//Payment
		Route::get('add/payment', 'UserController@addPayment')->name('add_payment');
		Route::post('add/payment', 'UserController@storePayment')->name('store_payment');
		Route::delete('delete/payment/{id}', 'UserController@deletePayment')->name('delete_payment');
		Route::get('primary/payment/{id}', 'UserController@primaryPayment')->name('primary_payment');
		Route::get('cancel/subscription/{id}', 'UserController@cancelSubscription')->name('cancel_subscription');
		Route::delete('delete/device/{id}', 'UserController@deleteDevice')->name('delete_device');
		//my list
		Route::get('my-list', 'UserController@getMyList')->name('getMyList');
		//Route::get('add/favorite/{type}/{id}', 'UserController@addToList')->name('addToList');
		Route::post('add/favorite', 'UserController@addToList')->name('addToList');
		//Route::get('remove/favorite/{type}/{id}', 'UserController@removeFromList')->name('removeFromList');
		Route::post('remove/favorite', 'UserController@removeFromList')->name('removeFromList');
		Route::get('kid-section', 'HomeController@kidSection')->name('kidSection');
		//review
		Route::post('review', 'UserController@review')->name('review');
		Route::post('resume', 'VideosController@resume')->name('resume');
		Route::post('userlog', 'VideosController@userlog')->name('userlog');
		Route::post('remove/watching', 'VideosController@removeWatching')->name('removeWatching');
		//Route::post('remove/watching', 'HomeController@removeWatching')->name('removeWatching');
		Route::post('check/session', 'UserController@checkSession')->name('check.session');
	});
});
