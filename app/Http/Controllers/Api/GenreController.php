<?php

namespace App\Http\Controllers\Api;

use Contracts\Search\SearchContract;
use App\Http\Controllers\Api\FormattingData\Genre;
use App\Http\Controllers\Api\FormattingData\MovieSeries;
use App\Http\Resources\Genre\GenreCollection;
use App\Http\Resources\Genre\GenreMovieCollection;
use App\Http\Resources\Genre\GenreMovieSeriesCollection;
use App\Http\Resources\Genre\GenreResource;
use App\Http\Resources\Genre\GenreSeriesCollection;
use App\Http\Resources\Movie\MovieCollection;
use App\Http\Resources\Series\SeriesCollection;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Contracts\Genres\GenresContract;
use Auth;

class GenreController extends Controller
{
    public function __construct(GenresContract $genres, SearchContract $search)
    {
        $this->genres = $genres;
        $this->search = $search;
    }

    public function show(Request $request)
    {
        $data = (new GenreMovieSeriesCollection($this->search->getMovieSeriesListing($request)))->get();


        return response()->json($data, 200);

        /*$data['movies'] = (new MovieCollection($this->genres->getMoviesByGenre($slug, 21)))->get();
        $data['series'] = (new SeriesCollection($this->genres->getShowsByGenre($slug, 21)))->get();*/

        /*$data ['data']  = $this->genres->getBySlug($slug);
        $data['movies'] = $this->genres->getMoviesByGenre($slug, 21);
        $data['series'] = $this->genres->getShowsByGenre($slug, 21);

        return response()->json(Genre::index($data), 200);*/
    }

    public function index(Request $request)
    {
        if (isset($request->slug) || isset($request->type) || isset($request->page)) {
            return $this->show($request);
        }
        $data = (new GenreCollection($this->genres->getAllLinked()))->get();

        return response()->json(Genre::index($data), 200);
    }

    public function getMovies($slug, Request $request)
    {
        $data = (new MovieCollection($this->genres->getMoviesByGenre($slug)))->get();

        return response()->json($data, 200);
    }

    public function getShows($slug, Request $request)
    {
        $data = (new SeriesCollection($this->genres->getShowsByGenre($slug)))->get();

        return response()->json($data, 200);
    }

    public function getAllMovies()
    {
        $data = (new GenreMovieCollection($this->genres->getAll()))->get();

        return response()->json($data, 200);
    }

    public function getAllShows()
    {
        $data = (new GenreSeriesCollection($this->genres->getAll()))->get();

        return response()->json($data, 200);
    }
}
