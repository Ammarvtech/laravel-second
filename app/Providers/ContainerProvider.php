<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ContainerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('Contracts\Users\UsersContract', 'Repos\Users\UsersRepo');
        $this->app->bind('Contracts\Countries\CountriesContract', 'Repos\Countries\CountriesRepo');
        $this->app->bind('Contracts\Roles\RolesContract', 'Repos\Roles\RolesRepo');
        $this->app->bind('Contracts\Permissions\PermissionsContract', 'Repos\Permissions\PermissionsRepo');
        $this->app->bind('Contracts\Shows\ShowsContract', 'Repos\Shows\ShowsRepo');
        $this->app->bind('Contracts\Images\ImagesContract', 'Repos\Images\ImagesRepo');
        $this->app->bind('Contracts\Episodes\EpisodesContract', 'Repos\Episodes\EpisodesRepo');
        $this->app->bind('Contracts\Movies\MoviesContract', 'Repos\Movies\MoviesRepo');
        $this->app->bind('Contracts\Genres\GenresContract', 'Repos\Genres\GenresRepo');
        $this->app->bind('Contracts\Casts\CastsContract', 'Repos\Casts\CastsRepo');
        $this->app->bind('Contracts\Options\OptionsContract', 'Repos\Options\OptionsRepo');
        $this->app->bind('Contracts\Payment\PaymentContract', 'Repos\Payment\PaymentRepo');
        $this->app->bind('Contracts\Videos\VideosContract', 'Repos\Videos\VideosRepo');
        $this->app->bind('Contracts\Search\SearchContract', 'Repos\Search\SearchRepo');
        $this->app->bind('Contracts\Subscriptions\SubscriptionContract', 'Repos\Subscriptions\SubscriptionRepo');
        $this->app->bind('Contracts\CreditCards\CreditCardContract', 'Repos\CreditCards\CreditCardRepo');
        $this->app->bind('Contracts\JWTAuth\JWTAuthContract', 'Repos\JWTAuth\JWTAuthRepo');
        $this->app->bind('Contracts\Pages\PageContract', 'Repos\Pages\PageRepo');
        $this->app->bind('Contracts\API\PushNotification\PushNotificationContract',
            'Repos\API\PushNotification\PushNotificationRepo');

//        //API
//        $this->app->bind('Contracts\API\Users\UsersContract', 'Repos\API\Users\UsersRepo');
//        $this->app->bind('Contracts\API\Shows\ShowsContract', 'Repos\API\Shows\ShowsRepo');
//        $this->app->bind('Contracts\API\Movies\MoviesContract', 'Repos\API\Movies\MoviesRepo');
    }
}
