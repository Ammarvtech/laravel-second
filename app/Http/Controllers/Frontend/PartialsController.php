<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Contracts\Genres\GenresContract;
use Contracts\Pages\PageContract;
use Contracts\Shows\ShowsContract;
use Contracts\Movies\MoviesContract;
use Contracts\Subscriptions\SubscriptionContract;
use Symfony\Component\HttpFoundation\Session\Session;
use Auth;

class PartialsController extends Controller
{
	public function __construct(GenresContract $genres, PageContract $pages, ShowsContract $shows, MoviesContract $movies, SubscriptionContract $subscription)
	{
		$this->genres = $genres;
		$this->pages = $pages;
		$this->shows = $shows;
		$this->movies = $movies;
		$this->subscription = $subscription;
		
	}

    public function navbar(View $view)
    {
    	$data['is_kid'] =  request()->session()->get('kid',0);
		//$data['genres'] = $this->genres->getAll();
		$data['shows']    = $this->shows->getByStatus(1);
        $data['genres']    = $this->genres->getForMenue(1);
        //dd($data['genres']);
        //$data['forHomePage']    = $this->genres->getForHomePage(1);
        $data['forMenue']    = $this->genres->getForMenue(1);
        $data['subscriptions'] = $this->subscription->getAll(\Auth::user()->id);
        $moviesNotifications = $this->movies->getForNotification();
    	$showsNotifications = $this->shows->getForNotification();
    	$data['notifications'] = array_merge($moviesNotifications, $showsNotifications);
    	$view->with($data);
	}
	
	public function footer(View $view )
    {
		$data['pages']  = $this->pages->getLinks(true);
    	$view->with($data);
    }
}