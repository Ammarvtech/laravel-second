<?php

namespace Repos\Genres;

use App\Movie;
use App\Show;
use Contracts\Genres\GenresContract;
use App\Genre;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Session\Session;

class GenresRepo implements GenresContract
{
    private $pagination = 20;

    public function __construct(Genre $genre, Movie $movie, Show $show)
    {
        $this->genre = $genre;
        $this->movie = $movie;
        $this->show  = $show;
    }

    public function get($id)
    {
        return $this->genre->findOrFail($id);
    }

    public function getBySlug($slug)
    {
        return $this->genre->where('slug', $slug)->firstOrFail();
    }

    public function getMovies($id)
    {
        return $this->genre->where('id', $id)->with('movies')->get();
    }

    public function getShows($id)
    {
        return $this->genre->where('id', $id)->with('shows')->get();
    }

    public function getByStatus($id)
    {
        return $this->genre->where('status', $id)->where('is_kid',request()->session()->get('kid',0))->with('movies')->get();
    }

    public function getForHomePage($id)
    {
        return $this->genre->where('status', $id)->where('display_on_homepage', $id)
            ->with(['movies' => function ($query) {
                $query->where('status', 1)->where('is_kid',request()->session()->get('kid',0))->orderByDesc('id');
            }])
            ->with(['shows' => function ($query) {
                $query->where('status', 1)->where('is_kid',request()->session()->get('kid',0))->orderByDesc('id');
            }])
            ->where('is_kid',request()->session()->get('kid',0))->orderBy('sort_order','asc')->get();
    }

    public function getForMenue($id)
    {
        return $this->genre->where('status', $id)->where('is_kid',request()->session()->get('kid',0))->orderBy('slug','asc')->get();
        //where('show_in_menue', $id)
    }

    public function getMoviesByGenre($slug, $withLimit = 0)
    {
        return ($withLimit > 0) ?
            $this->getBySlug($slug)->movies()->take($withLimit)->where('status','1')->orderBy('production','desc')->get() :
            $this->getBySlug($slug)->movies()->where('status','1')->orderBy('production','desc')->get();
    }

    public function getShowsByGenre($slug, $withLimit = 0)
    {
        return ($withLimit > 0) ?
            $this->getBySlug($slug)->shows()->take($withLimit)->orderBy('production','desc')->get() :
            $this->getBySlug($slug)->shows()->where('status','1')->orderBy('production','desc')->get();
    }

    public function getAll()
    {
        return $this->genre->latest()->get();
    }

    public function getPaginated()
    {
        return $this->genre->paginate($this->pagination);
    }

    public function backendFilter($request)
    {
        $q = $this->genre;

        if(isset($request->keyword) && ! empty($request->keyword)){
            $q = $q->whereTranslationLike('title', '%' . $request->keyword . '%');
        }

        return (object)[
            'data'  => $q->latest()->paginate($this->pagination),
            'count' => $q->count()
        ];
    }

    public function countAll(){
        return $this->genre->count();
    }

    public function dateFilter($from_date, $to_date){
        $q = $this->genre;
        $q =  $q->whereDate('created_at', '<=', $from_date);
        $q =  $q->whereDate('created_at', '>=', $to_date);
        return (object) [
            'count' => $q->count()
        ];
    }

    public function set($data)
    {
        $slug = make_slug($data->title);

        if($genre = $this->genre->where('slug', 'like', '%' . $slug . '%')->orderBy('slug', 'desc')->first()){
            $slug = $genre->slug . '-2';
        }

        $inputs = [
            'slug' => $slug,
            // multi lang inputs
            'en'   => [
                'title' => $data->title,
            ],
            'ar'   => [
                'title' => $data->ar_title,
            ],
            'sort_order' => $data->sort_order,
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];

        return $this->genre->create($inputs);
    }

    public function update($data, $id)
    {
        $genre  = $this->get($id);
        $inputs = [
            'slug' => make_slug($data->title),
            // multi lang inputs
            'en'   => [
                'title' => $data->title,
            ],
            'ar'   => [
                'title' => $data->ar_title,
            ],
            'sort_order' => $data->sort_order,
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];

        return $genre->update($inputs);
    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }

    public function getAllLinked()
    {
        return $this->genre->orHas('movies', '>', 0)
                           ->orHas('shows', '>', 0)
                           ->latest()->get();
    }

    /* private function movies_query($request)
     {
         $binding_values = [];
         if ($request->type == "series") {
             return "";
         }
         $movies = $this->movie->selectRaw("created_at, slug, id , Null as season, poster_id, image_id");

         if ( ! is_null($request->slug)) {
             $slug = $request->slug;
             $movies->whereHas("genres",
                 function ($query) use ($slug) {
                     $query->whereRaw("slug = '" . $slug . "'");
                 });

         }

         if ( ! is_null($request->sort)) {
             switch ($request->sort) {
                 case "latest":
                     $movies->orderBy('id', 'desc');
                     break;
                 case "trending":
                     $movies->where('views', '>', 0)->orderBy('views', 'desc');
                     break;
                 default:
                     break;
             }
         }

         if ( ! is_null($request->keyword)) {
             $keyword          = $request->keyword;
             $binding_values[] = '%'.$keyword.'%';
             $binding_values[] = '%'.$keyword.'%';
             $binding_values[] = '%'.$keyword.'%';
             $binding_values[] = '%'.$keyword.'%';

    //            $movies->whereTranslationLike("title", '%' . $keyword . '%');
             $movies->orWhere(function ($query) use ($keyword,$binding_values) {
                 $query->whereTranslationLike("title", '%' . $keyword . '%')
                       ->orWhereTranslationLike('desc', 'like', '%' . $keyword . '%')
                       ->orWhere(function ($cast_query) use ($keyword) {
                           $cast_query->whereHas('casts',function ($q) use ($keyword) {
                               $q->whereTranslationLike('name',  '%' . $keyword . '%');
                           });
                       })
                 ;
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
         if ($request->type == "movies") {
             return "";
         }

         $series = $this->show->selectRaw("created_at, slug, id , season, poster_id, image_id");
         if ( ! is_null($request->slug)) {
             $slug             = $request->slug;
             $binding_values[] = $slug;

             $series->whereHas("genres",
                 function ($query) use ($slug) {
                     $query->where("slug ", $slug);
                 });

         }

         if ( ! is_null($request->sort)) {
             switch ($request->sort) {
                 case "latest":
                     $series->orderBy('id', 'desc');
                     break;
                 case "trending":
                     $series->where('views', '>', 0)->orderBy('views', 'desc');
                     break;
                 default:
                     break;
             }
         }

         if ( ! is_null($request->keyword)) {
             $keyword          = $request->keyword;
             $binding_values[] = '%'.$keyword.'%';
             $binding_values[] = '%'.$keyword.'%';
             $binding_values[] = '%'.$keyword.'%';
             $binding_values[] = '%'.$keyword.'%';

    //            $series->whereTranslationLike("title", '%' . $keyword . '%');

             $series->orWhere(function ($query) use ($keyword,$binding_values) {
                 $query->whereTranslationLike("title", '%' . $keyword . '%')
                       ->orWhereTranslationLike('desc', 'like', '%' . $keyword . '%')
                       ->orWhere(function ($cast_query) use ($keyword) {
                           $cast_query->whereHas('casts',function ($q) use ($keyword) {
                               $q->whereTranslationLike('name',  '%' . $keyword . '%');
                           });
                       })
                 ;
             });
         }

         return [
             "query" => $series,
             "data"  => ($binding_values)
         ];
     }

     public function getMovieSeries($request)
     {
         $movie  = $this->movies_query($request);
         $series = $this->series_query($request);

         $movie_query  = $movie["query"];
         $series_query = $series["query"];

         if ($movie_query !== "" && $series_query !== "") {
             $final_query = $movie_query->unionAll($series_query);
         } elseif ($movie_query !== "") {
             $final_query = $movie_query;
         } else {
             $final_query = $series_query;
         }

         $sql_query = $final_query->toSql();


         /* return $movies->unionAll($series)->orderBy('created_at')
                        ->skip(($page - 1) * $this->pagination)
                        ->take($this->pagination)
                        ->get();*/

    /* return DB::table(DB::raw("(" . $sql_query . ") as a"))->setBindings(array_merge($movie['data'],
         $series['data']))->paginate($this->pagination);
    }*/

    public function getAllGenresWhichHasSeries()
    {
        return $this->genre->has('shows', '>', 0)->latest()->get();
    }

    public function getAllGenresWhichHasMovies()
    {
        return $this->genre->has('movies', '>', 0)->latest()->get();
    }
}
