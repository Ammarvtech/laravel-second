<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Genre\GenreMovieCollection;
use App\Http\Resources\Movie\MovieCollection;
use App\Http\Resources\Movie\MovieResource;
use Contracts\Genres\GenresContract;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Contracts\Movies\MoviesContract;
use Auth;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Requests\Api\Movies\Movies as MovieValidation;

class MovieController extends Controller
{
    public function __construct(MoviesContract $movies, GenresContract $genres)
    {
        $this->movies = $movies;
        $this->genres = $genres;
    }

    // single page
    public function show($slug)
    {
        $movie           = $this->movies->getBySlug($slug);
        $data            = (new MovieResource($movie))->get();
        $data['related'] = (new MovieCollection($this->movies->getRelated($movie)))->get();

        return response()->json($data, 200);
    }

    public function index(Request $request)
    {
        if( ! empty($request->slug)){
            return $this->show($request->slug);
        }

        $data = (new GenreMovieCollection($this->genres->getAllGenresWhichHasMovies()))->get();

        return response()->json($data, 200);
    }

    public function paused(MovieValidation $request)
    {
        $code = $this->movies->paused($request, \JWTAuth::toUser()->id);

        if($code != \config("api.response_code.success")){
            return response()->json(["errors" => __('api.response_code.' . $code)], 401);
        }

        return response()->json(["message" => __('api.response_code.' . $code)], 200);
    }

    public function review(MovieValidation $request)
    {
        $code = $this->movies->review($request, \JWTAuth::toUser()->id);

        if ($code != \config("api.response_code.success")) {
            return response()->json(["errors" => __('api.response_code.' . $code)], 401);
        }

        return response()->json(["message" => __('api.response_code.' . $code)], 200);
    }
}
