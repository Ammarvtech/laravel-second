<?php
namespace Repos\Episodes;

use App\ContinueWatching;
use Contracts\Episodes\EpisodesContract;
use Contracts\Images\ImagesContract;
use Contracts\Videos\VideosContract;
use App\Episode;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Session\Session;

class EpisodesRepo implements EpisodesContract
{
    private $pagination = 20;

    public function __construct(
        Episode $episode,
        ImagesContract $images,
        VideosContract $videos,
        ContinueWatching $continue_watching
    )
    {
        $this->episode = $episode;
        $this->images  = $images;
        $this->videos            = $videos;
        $this->continue_watching = $continue_watching;
    }

    public function get($id)
    {
        return $this->episode->findOrFail($id);
    }

    public function getByShow($id)
    {
        return $this->episode->where('show_id', $id)->get();
    }

    public function getShowEpisodesByEpisodeId($id)
    {
        $episode = $this->get($id);

        return (object) [
            'show'     => $episode->show_id,
            'episodes' => $this->getByShow($episode->show_id)
        ];
    }

    public function getAll()
    {
        return $this->episode->all();
    }

    public function getPaginated()
    {
        return $this->episode->paginate($this->pagination);
    }

    public function backendFilter($request)
    {
        $q = $this->episode;

        if (isset($request->keyword) && !empty($request->keyword))
            $q = $q->whereTranslationLike('title', '%' . $request->keyword . '%');

        if (isset($request->show) && $request->show != 'all')
            $q = $q->where('show_id', $request->show);

        return (object) [
            'data'  => $q->latest()->paginate($this->pagination),
            'count' => $q->count()
        ];
    }

    public function set($data)
    {
        $inputs = [
            'show_id' => $data->show_id,
            'season'  => $data->season,
            'length'  => $data->length,
            // multi lang inputs
            'en' => [
                'title' => $data->title,
            ],
            'ar' => [
                'title' => $data->ar_title,
            ],
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];

        if ($data->hasFile('image') && $data->file('image')->isValid())
            $inputs['image_id'] = $this->images->set($data->image);

        $episode = $this->episode->create($inputs);

        $episode_id = $this->episode->orderBy('id', 'desc')->pluck('id')->first();

        if (isset($data->video_id) && !empty($data->video_id)) {
            $v = $this->videos->get($data->video_id);
            $v = json_decode(json_encode($v));
            $video_id = $data['video_id'];
            $data->episode = $episode_id;
            $data->original_title = $v->original_title;
            $this->videos->update($data, $video_id);
        }

        if ($data->hasFile('video') && $data->file('video')->isValid())
            $this->videos->set($data->file('video'), $episode->id);
        
        return true;
    }

    public function update($data, $id)
    {
        $episode = $this->get($id);
        $inputs = [
            'show_id' => $data->show_id,
            'season'  => $data->season,
            'length'  => $data->length,
            // multi lang inputs
            'en' => [
                'title' => $data->title,
            ],
            'ar' => [
                'title' => $data->ar_title,
            ],
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];

        if ($data->hasFile('image') && $data->file('image')->isValid())
            $inputs['image_id'] = $this->images->set($data->image);

        $episode->update($inputs);

        if ($data->hasFile('video') && $data->file('video')->isValid())
            $this->videos->set($data->file('video'), $episode->id);

    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }

    public function paused($data, $user_id){
        if((int)$data->paused_at >= (int)$data->duration){
            $this->continue_watching->where("episode_id", $data->episode_id)->where("user_id", $user_id)->delete();
            return config("api.response_code.success");
        }else if($data->paused_at > 0 && $data->paused_at != $data->duration){
            $paused = $this->continue_watching
                ->where('user_id', $user_id)
                ->where('series_id', $data->id)
                ->where('episode_id', $data->episode_id)
                ->get()->toArray();

            $episode = $this->episode->find($data->episode_id);
            if(is_null($episode)){
                return config("api.response_code.not_found");
            }

            $this->continue_watching->user_id    = $user_id;
            $this->continue_watching->series_id  = $data->id;
            $this->continue_watching->episode_id = $data->episode_id;
            $this->continue_watching->paused_at  = $data->paused_at;
            $this->continue_watching->percent    = round(($data->paused_at / $data->duration) * 100, 1);

            if(count($paused) > 0){
                $res = $episode->pause()->update($this->continue_watching->toArray());
            } else{
                $res = $episode->pause()->save($this->continue_watching);
            }

            return ($res) ?
                config("api.response_code.success") :
                config("api.response_code.error");
        }
    }
    
    public function lastWatchedEpisode($slug, $user_id)
    {
        return $this->continue_watching
            ->where('user_id', $user_id)
            ->orderBy('episode_id')
            ->whereHas('shows', function($query) use ($slug){
                return $query->where('slug', $slug);
            })
            ->with('episodes')
            ->first();
    }

    public function countAll(){
        return $this->episode->count();
    }

    public function dateFilter($from_date, $to_date){
        $q = $this->episode;
        $q =  $q->whereDate('created_at', '<=', $from_date);
        $q =  $q->whereDate('created_at', '>=', $to_date);
        return (object) [
            'count' => $q->count()
        ];
    }

    public function userlog($data, $user_id){
        \DB::table('user_logs')->insert([
            "user_id" => $user_id, 
            "ip" => request()->session()->get('ip'), 
            "country" => request()->session()->get('country_name'), 
            "series_id" => $data->id,
            "episode_id" => $data->episode_id,
            "paused_at" =>   $data->paused_at,
            "percent" => round(($data->paused_at / $data->duration) * 100, 1),
            "created_at" => Carbon::now()->toDateTimeString()

        ]);

        return config("api.response_code.success");
    }

    public function getNameById($id){
        DB::table('episode_translations')->select('title')->where('id', $id)->where('locale', 'en')->pluck('title')->first();
    }
}
