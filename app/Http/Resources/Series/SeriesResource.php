<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/14/2018
 * Time: 12:31 PM
 */

namespace App\Http\Resources\Series;


use App\Http\Resources\Resource;

class SeriesResource extends Resource
{
    public function get()
    {
//        $continueWatching = $this->model->pause()->where("user_id", 1)->orderBy('episode_id', "DESC")->first();

        return [
            "id"                => $this->model->id,
            "image_vertical"   => (isset($this->model->image)) ? thumb($this->model->image) : "http://34.243.141.252/uploads/vertical.jpg",
            "image_horizontal" => (isset($this->model->poster)) ? thumb($this->model->poster) : "http://34.243.141.252/uploads/horizontal.jpg",
            "name"              => $this->model->title,
            "slug"              => $this->model->slug,
            "type"              => $this->type,
            "year"              => $this->model->production,
            "category"          => $this->model->genres->pluck('title'),
            "rate"              => $this->model->rating_avg,
            "length"            => "85",
            "media_url"         =>  ( ! is_null($this->model->video))? videoUrl($this->model->video):"http://content.jwplatform.com/manifests/vM7nH0Kl.m3u8",
            "description"       => $this->model->desc,
            "rate_imdb"         => $this->model->imdb->rate ?? 0,
            "over18"            => "false",
            "sharing_url"       => "http://www.google/com",
            "seasons"           => $this->seasons($this->model->episodes),
            "continue_watching" => [
                "title"      => "",
                "episode_id" => 0,
                "percentage" => 0,
                "paused_at"  => 0,
                "media"      => ""
            ],
            "is_added_to_list " => in_array($this->model->id, \JWTAuth::toUser()->followingSeries->pluck('id')->toArray())
        ];
    }

    private function seasons($rows)
    {
        $data = [];
        foreach ($rows as $row) {
            if (isset($data[$row->season - 1])) {
                $data[$row->season - 1]["episodes"][] = [
                    "episode_id" => $row->id,
                    "episode"    => $row->title,
                    "media"      => ( ! is_null($row->video))?videoUrl($row->video):" http://content.jwplatform.com/manifests/vM7nH0Kl.m3u8"
                ];
            } else {
                $data[$row->season - 1]["season"] = $row->season;
                $data[$row->season - 1]["episodes"][] = [
                    "episode_id" => $row->id,
                    "episode"    => $row->title,
                    "media"      => ( ! is_null($row->video))?videoUrl($row->video):" http://content.jwplatform.com/manifests/vM7nH0Kl.m3u8"
                ];
            }
        }

        return $data;
    }

    public function collection()
    {

    }
}