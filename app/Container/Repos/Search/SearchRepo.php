<?php

/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/19/2018
 * Time: 3:25 PM
 */

namespace Repos\Search;


use App\Cast;
use App\Episode;
use App\Http\Resources\ContinueCollection;
use Carbon\Carbon;
use Contracts\Search\SearchContract;
use App\Genre;
use App\Movie;
use App\Show;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class SearchRepo implements SearchContract
{
    private $pagination = 5;

    public function __construct(Show $show, Movie $movie, Cast $cast, Genre $genre, Episode $episode)
    {
        $this->show    = $show;
        $this->movie   = $movie;
        $this->cast    = $cast;
        $this->genre   = $genre;
        $this->episode = $episode;
    }

    private function movies_query($request)
    {
        $binding_values = [];
        if(strtolower($request->type) == "s"){
            return [
                "query" => "",
                "data"  => []
            ];
        }
        $movies = $this->movie->selectRaw("created_at, slug, id , Null as season, poster_id, image_id, views, production, publish_date");

        if( ! is_null($request->slug)){
            $slug             = $request->slug;
            $binding_values[] = $slug;

            $movies->whereHas(
                "genres",
                function($query) use ($slug){
                    $query->where("slug", $slug);
                }
            );

        }


        if( ! is_null($request->keyword)){
            $keyword          = $request->keyword;
            $binding_values[] = '%' . $keyword . '%';
            $binding_values[] = '%' . $keyword . '%';
            $binding_values[] = '%' . $keyword . '%';
            $binding_values[] = '%' . $keyword . '%';

            $movies->orWhere(function($query) use ($keyword, $binding_values){
                $query->whereTranslationLike("title", '%' . $keyword . '%')
                      ->orWhereTranslationLike('desc', 'like', '%' . $keyword . '%')
                      ->orWhere(function($cast_query) use ($keyword){
                          $cast_query->whereHas('casts', function($q) use ($keyword){
                              $q->whereTranslationLike('name', '%' . $keyword . '%');
                          });
                      });
            });
        }

        return [
            "query" => $movies,
            "data"  => ($binding_values)
        ];
    }

    private function series_query($request)
    {
        $binding_values = [];
        if(strtolower($request->type) == "m"){
            return [
                "query" => "",
                "data"  => []
            ];
        }

        $series = $this->show->selectRaw("created_at, slug, id , season, poster_id, image_id, views, production, publish_date");

        if( ! is_null($request->slug)){
            $slug             = $request->slug;
            $binding_values[] = $slug;

            $series->whereHas(
                "genres",
                function($query) use ($slug){
                    $query->where("slug", $slug);
                }
            );

        }

        if( ! is_null($request->keyword)){
            $keyword          = $request->keyword;
            $binding_values[] = '%' . $keyword . '%';
            $binding_values[] = '%' . $keyword . '%';
            $binding_values[] = '%' . $keyword . '%';
            $binding_values[] = '%' . $keyword . '%';

            $series->orWhere(function($query) use ($keyword, $binding_values){
                $query->whereTranslationLike("title", '%' . $keyword . '%')
                      ->orWhereTranslationLike('desc', 'like', '%' . $keyword . '%')
                      ->orWhere(function($cast_query) use ($keyword){
                          $cast_query->whereHas('casts', function($q) use ($keyword){
                              $q->whereTranslationLike('name', '%' . $keyword . '%');
                          });
                      });
            });
        }

        return [
            "query" => $series,
            "data"  => ($binding_values)
        ];
    }

    public function getMovieSeriesListing($request)
    {
        $movie  = $this->movies_query($request);
        $series = $this->series_query($request);

        $movie_query  = $movie["query"];
        $series_query = $series["query"];

        if($movie_query !== "" && $series_query !== ""){
            $final_query = $movie_query->unionAll($series_query);
        } elseif($movie_query !== ""){
            $final_query = $movie_query;
        } else{
            $final_query = $series_query;
        }

        $sql_query = $final_query->toSql();

        $super_query = DB::table(DB::raw("(" . $sql_query . ") as a"));
        $binding     = array_merge(
            $movie['data'],
            $series['data']
        );


        switch($request->sort){
            case "trending":
                $binding[] = 0;
                $super_query->where('views', '>', 0)->orderBy('views', 'desc');
                break;
            case "released_date":
                $super_query->orderBy('production', 'desc');
                break;
            case "coming":
                $binding[] = Carbon::now();
                $super_query->where('publish_date', '>', Carbon::now())->orderBy("publish_date", 'desc');
                break;
            default:
                $super_query->orderBy('id', 'desc');
                break;
        }


        return $super_query->setBindings($binding)->paginate($this->pagination);
    }

    public function pickedForYou($user, $limit = 0)
    {
        $movie_ids  = $user->followingMovies->pluck('id')->toArray();
        $series_ids = $user->followingSeries->pluck('id')->toArray();

        $genres_ids = $user->followingMovies->map(function($movie){
            return $movie->genres->pluck('id')->toArray();
        })->flatten(1)->values()->union(
            $user->followingSeries->map(function($series){
                return $series->genres->pluck('id')->toArray();
            })->flatten(1)->values()->all()
        )->all();


        $series_query = $this->show->selectRaw("created_at, slug, id , season, poster_id, image_id")->whereHas(
            "genres",
            function($query) use ($genres_ids){
                $query->whereIn("genres.id", $genres_ids);
            }
        )->whereNotIn('shows.id', $series_ids);

        $movie_query = $this->movie->selectRaw("created_at, slug, id , Null as season, poster_id, image_id")->whereHas(
            "genres",
            function($query) use ($genres_ids){
                $query->whereIn("genres.id", $genres_ids);
            }
        )->whereNotIn('movies.id', $movie_ids);


        return $movie_query->unionAll($series_query)->take(21)->get();


    }

    public function pickedForYouPaginated($user)
    {
        $movie_ids  = $user->followingMovies->pluck('id')->toArray();
        $series_ids = $user->followingSeries->pluck('id')->toArray();

        $genres_ids = $user->followingMovies->map(function($movie){
            return $movie->genres->pluck('id')->toArray();
        })->flatten(1)->values()->union(
            $user->followingSeries->map(function($series){
                return $series->genres->pluck('id')->toArray();
            })->flatten(1)->values()->all()
        )->all();


        $series_query = $this->show->selectRaw("created_at, slug, id , season, poster_id, image_id")->whereHas(
            "genres",
            function($query) use ($genres_ids){
                $query->whereIn("genres.id", $genres_ids);
            }
        )->whereNotIn('shows.id', $series_ids);

        $movie_query = $this->movie->selectRaw("created_at, slug, id , Null as season, poster_id, image_id")->whereHas(
            "genres",
            function($query) use ($genres_ids){
                $query->whereIn("genres.id", $genres_ids);
            }
        )->whereNotIn('movies.id', $movie_ids);


        $final_query = $movie_query->unionAll($series_query)->toSql();

        return DB::table(DB::raw("(" . $final_query . ") as a"))->setBindings([
            [
                $genres_ids,
                $series_ids
            ],
            [
                $genres_ids,
                $movie_ids

            ]

        ])->paginate($this->pagination);
    }

    /* public function continueWatching($user, $limit = 0)
     {
         $tracks_ids = $user->pause()
                            ->select("movie_id", "series_id")
                            ->take(21)
                            ->get()
                            ->toArray();

         $movies_id = array_column($tracks_ids, "movie_id");
         $series_id = array_column($tracks_ids, "series_id");

         return $this->show->selectRaw("created_at, slug, id , season, poster_id, image_id")
                           ->whereIn('shows.id', $series_id)
                           ->union(
                               $this->movie->selectRaw("created_at, slug, id , Null as season, poster_id, image_id")
                                           ->whereIn('movies.id', $movies_id)
                           )->get();
     }

     public function continueWatchingPaginated($user)
     {
         $track_collection = $user->pause()
                                  ->selectRaw("Null as movie_id, series_id, MAX(episode_id) as episode_id")
                                  ->whereNotNull('series_id')
                                  ->groupBy('series_id')
                                  ->union(
                                      $user->pause()
                                           ->select("movie_id", "series_id", "episode_id")
                                           ->whereNotNull('movie_id')
                                  )
                                  ->paginate($this->pagination);

         $tracks_data = $track_collection->all();
         $movies_id   = array_column($tracks_data, "movie_id");
         $series_id   = array_column($tracks_data, "series_id");

         $arr['data'] = $this->show->selectRaw("created_at, slug, id , season, poster_id, image_id")
                                   ->whereIn('shows.id', $series_id)
                                   ->union(
                                       $this->movie->selectRaw("created_at, slug, id , Null as season, poster_id, image_id")
                                                   ->whereIn('movies.id', $movies_id)
                                   )->get();

         $arr["paging"] = [
             "current_page" => $track_collection->currentPage(),
             "next_page"    => ($track_collection->hasMorePages()) ? $track_collection->currentPage() + 1 : 0,
             "total"        => $track_collection->total(),
         ];

         return $arr;
     }*/

    public function continueWatching($user, $limit = 0)
    {
        $track_collection = $user->pause()
                                 ->selectRaw("Null as movie_id,  MAX(episode_id) as episode_id, series_id,  MAX(percent) as percent")
                                 ->whereNotNull('series_id')
                                 ->groupBy('series_id')
                                 ->union(
                                     $user->pause()
                                          ->select("movie_id", "episode_id", "series_id", "percent")
                                          ->whereNotNull('movie_id')
                                 )
                                 ->with("movies", "episodes")
                                 ->take(21)
                                 ->get();

        return $track_collection;
    }

    public function continueWatchingPaginated($user)
    {
        $track_collection = $user->pause()
                                 ->selectRaw("movie_id, series_id, episode_id, percent")
                                 ->with("movies", "episodes")
                                 ->paginate($this->pagination);

        return $track_collection;
    }

}
