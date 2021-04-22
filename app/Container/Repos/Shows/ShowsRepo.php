<?php

namespace Repos\Shows;

use App\ContinueWatching;
use App\IMDB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Contracts\Shows\ShowsContract;
use Contracts\Images\ImagesContract;
use App\Show;
use App\Review;

class ShowsRepo implements ShowsContract
{
    private $pagination = 21;

    public function __construct(
        Show $show,
        ImagesContract $images,
        ContinueWatching $continue_watching,
        Review $review,
        IMDB $imdb
    ){
        $this->show              = $show;
        $this->images            = $images;
        $this->continue_watching = $continue_watching;
        $this->review            = $review;
        $this->imdb_rate         = $imdb;
    }

    public function get($id)
    {
        return $this->show->findOrFail($id);
    }

    public function getBySlug($slug)
    {
        return $this->show->where('slug', $slug)->with('episodes', 'image', 'poster')->firstOrFail();
    }

    public function getAll()
    {
        return $this->show->all();
    }

    public function getByStatus($id)
    {
        return $this->show->where('status', $id)->where('is_kid',request()->session()->get('kid',0))->get();
    }

    public function getIn($ids)
    {
        return $this->show->whereIn('id', $ids)->where('is_kid',request()->session()->get('kid',0))->where('status', 1)->with('genres')->inRandomOrder()->get();
    }

    public function getPaginated()
    {
        return $this->show->with('poster')->paginate($this->pagination);
    }

    public function backendFilter($request)
    {

        $q = $this->show;

        if(isset($request->keyword) && ! empty($request->keyword)){
            $q = $q->whereTranslationLike('title', '%' . $request->keyword . '%');
        }

        return (object)[
            'data'  => $q->paginate($this->pagination),
            'count' => $q->count()
        ];
    }

    public function countAll(){
        return $this->show->count();
    }

    public function dateFilter($from_date, $to_date){
        $q = $this->show;
        $q =  $q->whereDate('created_at', '<=', $from_date);
        $q =  $q->whereDate('created_at', '>=', $to_date);
        return (object) [
            'count' => $q->count()
        ];
    }

    public function search($title, $limit = -1)
    {
        $query = $this->show->whereTranslationLike('title', '%' . $title . '%')->where('status',1);
        if($limit != -1){
            $query->limit($limit);
        }

        return $query->get();
    }

    public function addView($show)
    {
        return $show->increment('views');
    }

    public function set($data)
    {
        $slug = make_slug($data->title);

        if($show = $this->show->where('slug', 'like', '%' . $slug . '%')->orderBy('slug', 'desc')->first()){
            $slug = $show->slug . '-2';
        }

        $inputs = [
            'slug'       => $slug,
            'season'     => $data->season,
            'production' => $data->production,
            'age'        => $data->age,
            'imdb_url'   => $data->imdb_url,
            // multi lang inputs
            'en'         => [
                'title'           => $data->title,
                'publish_country' => $data->publish_country,
                'desc'            => $data->desc

            ],
            'ar'         => [
                'title'           => $data->ar_title,
                'publish_country' => $data->ar_publish_country,
                'desc'            => $data->ar_desc
            ],
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];
        if(isset($data->publish_date) && ! empty($data->publish_date)){
            $inputs['publish_date'] = $data->publish_date; //Carbon::createFromFormat('Y-m-d', $data->publish_date); //parse($data->publish_date);
        }

        if($data->hasFile('image') && $data->file('image')->isValid()){
            $inputs['image_id'] = $this->images->set($data->image);
        }

        if($data->hasFile('poster') && $data->file('poster')->isValid()){
            $inputs['poster_id'] = $this->images->set($data->poster);
        }

        $show = $this->show->create($inputs);

        if(isset($data->genres) && ! empty($data->genres)){
            $show->genres()->sync($data->genres);
        }

        if(isset($data->casts) && ! empty($data->casts)){
            $show->casts()->sync($data->casts);
        }

        if(isset($data->imdb_url) && ! empty($data->imdb_url)){
            $parse_url                 = parse_url($data->imdb_url);
            if(isset($parse_url['path'])){
                $this->imdb_rate->rate     = imdb_rate($data->imdb_url);
                $this->imdb_rate->movie_id = $show->id;
                $this->imdb_rate->imdb_id  = str_replace(["/", "title"], "", $parse_url['path']);
                (isset($movie->imdb)) ? $show->imdb()->update($this->imdb_rate->toArray()) : $show->imdb()->save($this->imdb_rate);
            }
        }

        return true;
    }

    public function update($data, $id)
    {
        $show = $this->get($id);

        $inputs = [
            'season'     => $data->season,
            'production' => $data->production,
            'age'        => $data->age,
            'imdb_url'   => $data->imdb_url,
            // multi lang inputs
            'en'         => [
                'title'           => $data->title,
                'publish_country' => $data->publish_country,
                'desc'            => $data->desc

            ],
            'ar'         => [
                'title'           => $data->ar_title,
                'publish_country' => $data->ar_publish_country,
                'desc'            => $data->ar_desc
            ],
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];
        if(isset($data->publish_date) && ! empty($data->publish_date)){
            $inputs['publish_date'] = $data->publish_date;
            //$inputs['publish_date'] = Carbon::parse($data->publish_date);
        }

        if($data->hasFile('image') && $data->file('image')->isValid()){
            $inputs['image_id'] = $this->images->set($data->image);
        }

        if($data->hasFile('poster') && $data->file('poster')->isValid()){
            $inputs['poster_id'] = $this->images->set($data->poster);
        }

        $show->update($inputs);

        if(isset($data->genres) && ! empty($data->genres)){
            $show->genres()->sync($data->genres);
        }

        if(isset($data->casts) && ! empty($data->casts)){
            $show->casts()->sync($data->casts);
        }

        if(isset($data->imdb_url) && ! empty($data->imdb_url)){
            $parse_url                 = parse_url($data->imdb_url);
            if(isset($parse_url['path'])){
                $this->imdb_rate->rate     = imdb_rate($data->imdb_url) ?? 0;
                $this->imdb_rate->movie_id = $show->id;
                $this->imdb_rate->imdb_id  = str_replace(["/", "title"], "", $parse_url['path']);
                (isset($movie->imdb)) ? $show->imdb()->update($this->imdb_rate->toArray()) : $show->imdb()->save($this->imdb_rate);
            }
        }

        return true;
    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }

    public function getLatest()
    {
        return $this->show->with('poster')->where('is_kid',request()->session()->get('kid',0))->orderBy('id', 'desc')->take(18)->get();
    }

    public function getForNotification()
    {
        return $this->show->with('poster')->where('is_kid',request()->session()->get('kid',0))->where('status',1)->orderBy('id', 'desc')->take(5)->get()->toArray();
    }

    public function getTrending($limit)
    {
        return $this->show->with('poster')->where('views', '>', 0)->where('is_kid',request()->session()->get('kid',0))->orderBy('views', 'desc')->take($limit)->get();
    }

    public function getLatestWithPagination()
    {
        return $this->show->with('poster')->orderBy('id', 'desc')->paginate($this->pagination);
    }

    public function getRelated($series)
    {
        $genres = array_column($series->genres->toArray(), 'id');

        return $this->show->whereHas("genres", function($query) use ($genres){
            $query->whereIn("genres.id", $genres);
        })->whereNotIn("id", [$series->id])->get();

    }

    public function paused($data, $user_id)
    {
        $paused = $this->continue_watching
            ->where('user_id', $user_id)
            ->where('series_id', $data->id)
            ->where('episode_id', $data->episode_id)
            ->get()->toArray();

        $series = $this->show->find($data->id);
        if(is_null($series)){
            return config("api.response_code.not_found");
        }

        $this->continue_watching->user_id    = $user_id;
        $this->continue_watching->series_id  = $data->id;
        $this->continue_watching->episode_id = $data->episode_id;
        $this->continue_watching->paused_at  = $data->paused_at;
        $this->continue_watching->percent    = round(($data->paused_at / $data->duration) * 100, 1);

        if(count($paused) > 0){
            $res = $series->pause()->update($this->continue_watching->toArray());
        } else{
            $res = $series->pause()->save($this->continue_watching);
        }

        return ($res) ?
            config("api.response_code.success") :
            config("api.response_code.error");
    }

    public function getPausedEpisodes($user_id, $show_id){
        $paused = $this->continue_watching
            ->where('user_id', $user_id)
            ->where('series_id', $show_id)
            ->get()->toArray();

        $paused_episodes = array();
        if(!empty($paused)){
            foreach ($paused as $key => $value) {
                $paused_episodes[$value['episode_id']] =  $value;
            }
        }
        return $paused_episodes;

    }

    public function review($data, $user_id)
    {
        $review = $this->review
            ->where('user_id', $user_id)
            ->where('show_id', $data->item_id)
            ->get()->toArray();

        $series = $this->show->find($data->item_id);
        if(is_null($series)){
            return config("api.response_code.not_found");
        }

        $this->review->user_id = $user_id;
        $this->review->rating  = $data->rate;
        $this->review->comment = (isset($data->comment)) ? $data->comment : "";

        if(count($review) > 0){
            $series->reviews()->update($this->review->toArray());
        } else{
            $series->reviews()->save($this->review);
        }

        $series->rating_avg   = round($series->reviews()->avg("rating"), 1);
        $series->rating_count = $series->reviews()->count();
        $res                  = $series->save();

        return ($res) ?
            config("api.response_code.success") :
            config("api.response_code.error");
    }
}
