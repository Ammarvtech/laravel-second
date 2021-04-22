<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Contracts\Movies\MoviesContract;
use Contracts\Shows\ShowsContract;
use Contracts\Genres\GenresContract;
use Contracts\Casts\CastsContract;
use Auth;

class MoviesController extends Controller
{
    public function __construct(MoviesContract $movies, ShowsContract $shows, GenresContract $genres, CastsContract $casts)
    {
        $this->movies = $movies;
        $this->shows = $shows;
        $this->genres = $genres;
        $this->casts = $casts;
    }

    public function index()
    {
        $data['movies'] = $this->movies->getPaginated();
        return view('frontend.movies', $data);
    }
    // single page
    public function show($slug)
    {
        // Auth::logout();
        $data['title'] = trans($slug);
        $data['movie'] = $this->movies->getBySlug($slug);
        $data['related'] = $this->movies->getRelated($data['movie']);
        
        $this->movies->addView($data['movie']); // add view to the movie
    return view('frontend.single-movie', $data);
    }

    public function related($slug){
        $data['movies'] = $this->casts->getMovies($slug);
        return view('frontend.related_movies', $data);
    }
}
