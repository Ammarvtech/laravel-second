<?php

namespace App\Http\Controllers\Api;


use App\Http\Resources\ContinueCollection;
use App\Http\Resources\Genre\GenreMovieSeriesCollection;
use App\Http\Resources\Movie\MovieCollection;
use App\Http\Resources\Series\SeriesCollection;
use Auth;
use App\Http\Requests;
use Contracts\API\PushNotification\PushNotificationContract;
use Contracts\Search\SearchContract;
use Contracts\Users\UsersContract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Contracts\Movies\MoviesContract;
use Contracts\Shows\ShowsContract;
use App\Http\Controllers\Api\FormattingData\Home;

class HomeController extends Controller
{
    private $search_limit = 7;

    public function __construct(
        MoviesContract $movies,
        ShowsContract $shows,
        SearchContract $search,
        PushNotificationContract $notification_contract
    ){
        $this->movies = $movies;
        $this->shows  = $shows;
        $this->search = $search;
        $this->notify = $notification_contract;
    }


    // home page
    public function index(Request $request)
    {
        $data['picked']           = $this->search->pickedForYou(\JWTAuth::toUser());
        $data['continueWatching'] = $this->search->continueWatching(\JWTAuth::toUser());

        $data["series"] = [
            "shows" => $this->shows->getLatest(),
        ];
        $data["movies"] = [
            "trending" => $this->movies->getTrending(),
            "recently" => $this->movies->getLatest(),
            "coming"   => $this->movies->getComing()
        ];

        return response()->json(Home::index($data), 200);
    }

    //search
    public function search(Request $request)
    {
        $data['movies'] = (new MovieCollection($this->movies->search(
            $request->key,
            $this->search_limit
        )))->get();
        $data['shows']  = (new SeriesCollection($this->shows->search(
            $request->key,
            $this->search_limit
        )))->get();

        return response()->json($data, 200);
    }

    //recentlyAdded
    public function getComingSoon()
    {
        return response()->json((new MovieCollection($this->movies->getComingWithPagination()))->get(), 200);
    }

    //pickedForYou

    public function getPickedForYou()
    {
        return response()->json(
            (new GenreMovieSeriesCollection($this->search->pickedForYouPaginated(\JWTAuth::toUser())))->get(),
            200
        );
    }

    //continue watching
    public function getContinueWatching()
    {
        $data = (new ContinueCollection($this->search->continueWatchingPaginated(\JWTAuth::toUser())))->get();

        return response()->json(
            $data,
            200
        );
    }

    //notify
    public function notify($type = 'text')
    {
        return $this->notify->send([], ["type" => $type]);
    }
}
