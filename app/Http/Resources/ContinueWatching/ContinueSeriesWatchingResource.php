<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/29/2018
 * Time: 3:25 PM
 */

namespace App\Http\Resources\ContinueWatching;

use App\Http\Resources\Resource;

class ContinueSeriesWatchingResource extends Resource
{
    public function get()
    {
        $episode = $this->model->episodes;
        $show = $episode->show;

        return [
            "id"                => $show->id,
            "image_vertical"    => (isset($show->image)) ? thumb($show->image) : "http://34.243.141.252/uploads/vertical.jpg",
            "image_horizontal"  => (isset($show->poster)) ? thumb($show->poster) : "http://34.243.141.252/uploads/horizontal.jpg",
            "name"              => $show->title,
            "slug"              => $show->slug,
            "type"              => config('api.track_types.shortcut.show'),
            "year"              => $show->production,
            "category"          => $show->genres->pluck('title'),
            "rate"              => $show->rating_avg,
            "length"            => "85",
            "media_url"         => ( ! is_null($show->video))?videoUrl($show->video):"http://content.jwplatform.com/manifests/vM7nH0Kl.m3u8",
            "description"       => $show->desc,
            "rate_imdb"         => $show->imdb->rate ?? 0,
            "over18"            => "false",
            "sharing_url"       => "http://www.google/com",
            "seasons"           => $this->seasons($show->episodes),
            "continue_watching" => [
                "title"      => (!is_null($this->model->episode_id)) ? "S" . $this->model->episodes->season . " " . $this->model->episodes->title : "",
                "episode_id" => (isset($this->model->episode_id)) ?
                    $this->model->episode_id : 0,
                "percentage" => (isset($this->model->percent)) ?
                    $this->model->percent : 0,
                "paused_at"  => $this->model->paused_at ?? 0,
                "media"      => ( ! is_null($this->model->episodes->video)) ? videoUrl($this->model->episodes->video) : " http://content.jwplatform.com/manifests/vM7nH0Kl.m3u8"
            ],
            "is_added_to_list " => in_array($show->id, \JWTAuth::toUser()->followingSeries->pluck('id')->toArray())
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
                    "media"      => (!is_null($row->video)) ? videoUrl($row->video) : ""
                ];
            } else {
                $data[$row->season - 1]["season"] = $row->season;
                $data[$row->season - 1]["episodes"][] = [
                    "episode_id" => $row->id,
                    "episode"    => $row->title,
                    "media"      => (!is_null($row->video)) ? videoUrl($row->video) : ""
                ];
            }
        }

        return $data;
    }

}