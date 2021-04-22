<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/22/2018
 * Time: 3:11 PM
 */

namespace App\Http\Resources\Notifications;


use App\Http\Resources\Resource;

class TrackResource extends Resource
{

    public function get()
    {
        return [
//            "id"               => $this->model->id,
            "slug"             => $this->model->slug,
            "name"            => $this->model->title,
            "image_vertical"   => (isset($this->model->image_id)) ? thumb(\App\Image::find($this->model->image_id)) : "http://34.243.141.252/uploads/vertical.jpg",
//            "image_horizontal" => (isset($this->model->poster_id)) ? thumb(\App\Image::find($this->model->poster_id)) : "http://34.243.141.252/uploads/horizontal.jpg",
            "type"             => ( ! is_null($this->model->season))
                ? config('api.track_types.shortcut.show')
                : config('api.track_types.shortcut.movie'),
        ];
    }
}