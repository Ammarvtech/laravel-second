<?php

namespace Repos\Movies;

use App\ContinueWatching;
use App\IMDB;
use Contracts\Movies\MoviesContract;
use Contracts\Images\ImagesContract;
use Contracts\Videos\VideosContract;
use App\Movie;
use App\Video;
use Carbon\Carbon;
use PhpParser\Node\Stmt\TryCatch;
use App\Review;
use Illuminate\Support\Facades\DB;

class MoviesRepo implements MoviesContract
{
    private $pagination = 21;

    public function __construct(
        Movie $movie,
        ImagesContract $images,
        VideosContract $videos,
        ContinueWatching $continue_watching,
        Review $review,
        IMDB $imdb
    ) {
        $this->movie             = $movie;
        $this->images            = $images;
        $this->videos            = $videos;
        $this->continue_watching = $continue_watching;
        $this->review            = $review;
        $this->imdb_rate         = $imdb;
    }

    public function get($id)
    {
        return $this->movie->findOrFail($id);
    }

    public function getBySlug($slug)
    {
        return $this->movie->where('slug', $slug)->with('genres', 'casts', 'image', 'poster')->firstOrFail();
    }

    public function getAll()
    {
        return $this->movie->all();
    }

    public function getIn($ids)
    {
        return $this->movie->whereIn('id', $ids)->where('is_kid',request()->session()->get('kid',0))->where('status',1)->with('genres')->inRandomOrder()->get();
    }

    public function search($title, $limit = -1)
    {
        $query = $this->movie->whereTranslationLike('title', '%' . $title . '%')->where('status',1);
        if ($limit != -1) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getLatest()
    {
        return $this->movie->where('is_kid',request()->session()->get('kid',0))->orderBy('id', 'desc')->take(18)->get();
    }

    public function getForNotification()
    {
        return $this->movie->with('poster')->where('is_kid',request()->session()->get('kid',0))->where('status',1)->orderBy('id', 'desc')->take(5)->get()->toArray();
    }

    public function getLatestWithPagination()
    {
        return $this->movie->orderBy('id', 'desc')->paginate($this->pagination);
    }

    public function getComing()
    {
        $date = Carbon::now();

        return $this->movie->where('publish_date', '>', $date)->take(21)->get();
    }

    public function getComingWithPagination()
    {
        $date = Carbon::now();

        return $this->movie->where('publish_date', '>', $date)->paginate($this->pagination);
    }

    public function getTrending($limit)
    {
        return $this->movie->where('views', '>', 0)->where('is_kid',request()->session()->get('kid',0))->orderBy('views', 'desc')->take($limit)->get();
    }

    public function getTrendingWithPagination()
    {
        return $this->movie->where('views', '>', 0)->orderBy('views', 'desc')->paginate($this->pagination);
    }

    public function getPaginated()
    {
        return $this->movie->with('poster')->paginate($this->pagination);
    }

    public function backendFilter($request)
    {
        $q = $this->movie;

        if (isset($request->keyword) && !empty($request->keyword)) {
            $q = $q->whereTranslationLike('title', '%' . $request->keyword . '%');
        }

        if (isset($request->genre) && $request->genre != 'all') {
            $q = $q->whereHas('genres', function ($query) use ($request) {
                $query->where('genre_id', $request->genre);
            });
        }

        if (isset($request->date) && !empty($request->date)) {
            $q = $q->where('publish_date', $request->date);
        }

        return (object) [
            'data'  => $q->latest()->paginate($this->pagination),
            'count' => $q->count()
        ];
    }

    public function countAll(){
        return $this->movie->count();
    }

    public function dateFilter($from_date, $to_date){
        $q = $this->movie;
        $q =  $q->whereDate('created_at', '<=', $from_date);
        $q =  $q->whereDate('created_at', '>=', $to_date);
        return (object) [
            'count' => $q->count()
        ];
    }

    public function getRelated($movie)
    {

        $genres = $movie->genres->mapWithKeys(function ($item) {
            return [$item['id'] => $item];
        });
        $genres = $genres->keys()->all();
        //return $movie->with('genres')->whereIn('genres.id', $genres->keys()->all())->get();
        return $movie::select('movies.*')->where('movies.id', '!=', $movie->id)->where('movies.status',1)->join(
            'genre_movie',
            'genre_movie.movie_id',
            '=',
            'movies.id'
        )->whereIn('genre_movie.genre_id', $genres)->distinct()->get();
    }

    public function addView($movie)
    {
        return $movie->increment('views');
    }

    public function set($data)
    {
        $slug = make_slug($data->title);

        if ($movie = $this->movie->where('slug', 'like', '%' . $slug . '%')->orderBy('slug', 'desc')->first()) {
            $slug = $movie->slug . '-2';
        }

        $inputs = [
            'slug'       => $slug,
            'production' => $data->production,
            'age'        => $data->age,
            'imdb_url'   => $data->imdb_url,
            'length'     => $data->length,
            // multi lang inputs
            'en'         => [
                'title'           => $data->title,
                'desc'            => $data->desc,
                'publish_country' => $data->publish_country,
            ],
            'ar'         => [
                'title'           => $data->ar_title,
                'desc'            => $data->ar_desc,
                'publish_country' => $data->ar_publish_country,
            ],
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];


        if (isset($data->publish_date) && !empty($data->publish_date)) {
            $inputs['publish_date'] = $data->publish_date;
            //$inputs['publish_date'] = Carbon::parse($data->publish_date);
        }

        if ($data->hasFile('image') && $data->file('image')->isValid()) {
            $inputs['image_id'] = $this->images->set($data->image);
        }

        if ($data->hasFile('poster') && $data->file('poster')->isValid()) {
            $inputs['poster_id'] = $this->images->set($data->poster);
        }

        // create
        $movie = $this->movie->create($inputs);

        $movie_id = $this->movie->orderBy('id', 'desc')->pluck('id')->first();

        if (isset($data->video_id) && !empty($data->video_id)) {
            $v = $this->videos->get($data->video_id);
            $v = json_decode(json_encode($v));
            $video_id = $data->video_id;
            $data->movie = $movie_id;
            $data->original_title = $v->original_title;
            $this->videos->update($data, $video_id);
        }

        if (isset($data->genres) && !empty($data->genres)) {
            $movie->genres()->sync($data->genres);
        }

        if (isset($data->casts) && !empty($data->casts)) {
            $movie->casts()->sync($data->casts);
        }

        if (isset($data->imdb_url) && !empty($data->imdb_url)) {
            $parse_url                 = parse_url($data->imdb_url);
            if(isset($parse_url['path'])){
                $this->imdb_rate->rate     = IMDB_rate($data->imdb_url);
                $this->imdb_rate->movie_id = $movie->id;
                $this->imdb_rate->imdb_id  = str_replace(["/", "title"], "", $parse_url['path']);
                $movie->imdb()->save($this->imdb_rate);
            }
        }

        return true;
    }

    public function update($data, $id)
    {
        $movie = $this->get($id);

        $inputs = [
            'production' => $data->production,
            'age'        => $data->age,
            'length'     => $data->length,
            'imdb_url'   => $data->imdb_url,
            // multi lang inputs
            'en'         => [
                'title'           => $data->title,
                'desc'            => $data->desc,
                'publish_country' => $data->publish_country,
            ],
            'ar'         => [
                'title'           => $data->ar_title,
                'desc'            => $data->ar_desc,
                'publish_country' => $data->ar_publish_country,
            ],
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];

        if (isset($data->publish_date) && !empty($data->publish_date)) {
            $inputs['publish_date'] = $data->publish_date;
            //$inputs['publish_date'] = Carbon::parse($data->publish_date);
        }

        if ($data->hasFile('image') && $data->file('image')->isValid()) {
            $inputs['image_id'] = $this->images->set($data->image);
        }

        if ($data->hasFile('poster') && $data->file('poster')->isValid()) {
            $inputs['poster_id'] = $this->images->set($data->poster);
        }

        $movie->update($inputs);

        if ($data->hasFile('video') && $data->file('video')->isValid()) {
            $this->videos->set($data->file('video'), $movie->id);
        }

        if (isset($data->video_id) && !empty($data->video_id)) {
            $v = $this->videos->get($data->video_id);
            $v = json_decode(json_encode($v));
            $video_id = $data->video_id;
            $data->movie = $id;
            $data->original_title = $v->original_title;
            $old_video = Video::where('parent', $id)->where('parent_type', ($data->type == 'trailer' ? 'trailer_' : null) . 'movie')->first();
                if($old_video != null){
                    $old_video->parent = 0;
                    $old_video->parent_type = NULL;
                    $old_video->update();
                }
                $new_video = Video::where('id', $video_id)->first();
                $new_video->parent = $id;
                $new_video->parent_type = ($data->type == 'trailer' ? 'trailer_' : null) . 'movie';
                $new_video->update();
            //$this->videos->update($data, $video_id);
        }

        if (isset($data->genres) && !empty($data->genres)) {
            $movie->genres()->sync($data->genres);
        }

        if (isset($data->casts) && !empty($data->casts)) {
            $movie->casts()->sync($data->casts);
        }
        
        if (isset($data->imdb_url) && !empty($data->imdb_url)) {
            $parse_url                 = parse_url($data->imdb_url);
            if(isset($parse_url['path'])){
                $this->imdb_rate->rate     = imdb_rate($data->imdb_url);
                $this->imdb_rate->movie_id = $movie->id;
                $this->imdb_rate->imdb_id  = str_replace(["/", "title"], "", $parse_url['path']);
                (isset($movie->imdb)) ? $movie->imdb()->update($this->imdb_rate->toArray()) : $movie->imdb()->save($this->imdb_rate);
            }
        }

        return true;
    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }

    public function paused($data, $user_id)
    {   
        if((int)$data->paused_at >= (int)$data->duration){
            $this->continue_watching->where("movie_id", $data->id)->where("user_id", $user_id)->delete();
            return config("api.response_code.success");
        }else if($data->paused_at > 0 && $data->paused_at != $data->duration){   
            $paused = $this->continue_watching
                ->where("movie_id", $data->id)
                ->where("user_id", $user_id)
                ->get()->toArray();
            $movie  = $this->movie->find($data->id);
            if (is_null($movie)) {
                return config("api.response_code.not_found");
            }

            $this->continue_watching->user_id   = $user_id;
            $this->continue_watching->paused_at = $data->paused_at;
            $this->continue_watching->percent   = round(($data->paused_at / $data->duration) * 100, 1);

            if (count($paused) > 0) {
                $res = $movie->pause()->update($this->continue_watching->toArray());
            } else {
                $res = $movie->pause()->save($this->continue_watching);
            }

            return ($res) ?
            config("api.response_code.success") : config("api.response_code.error");
        }
    }

    public function review($data, $user_id)
    {
        $review = $this->review
            ->where('user_id', $user_id)
            ->where('movie_id', $data->item_id)
            ->get()->toArray();

        $movie = $this->movie->find($data->item_id);
        if (is_null($movie)) {
            return config("api.response_code.not_found");
        }

        $this->review->user_id = $user_id;
        $this->review->rating  = $data->rate;
        $this->review->comment = (isset($data->comment)) ? $data->comment : "";

        if (count($review) > 0) {
            $movie->reviews()->update($this->review->toArray());
        } else {
            $movie->reviews()->save($this->review);
        }

        $movie->rating_avg   = round($movie->reviews()->avg("rating"), 1);
        $movie->rating_count = $movie->reviews()->count();
        $res                 = $movie->save();

        return ($res) ?
            config("api.response_code.success") : config("api.response_code.error");
    }

    public function userlog($data, $user_id){
        \DB::table('user_logs')->insert([
            "user_id" => $user_id, 
            "ip" => request()->session()->get('ip'), 
            "country" => request()->session()->get('country_name'), 
            "movie_id" => $data->id,
            "paused_at" =>   $data->paused_at,
            "percent" => round(($data->paused_at / $data->duration) * 100, 1),
            "created_at" => Carbon::now()->toDateTimeString()
        ]);

        return config("api.response_code.success");
    }

    public function getNameById($id){
        return DB::table('movie_translations')->select('title')->where('id', $id)->where('locale', 'en')->pluck('title')->first();
    }
}
