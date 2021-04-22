<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/14/2018
 * Time: 12:31 PM
 */

namespace App\Http\Resources\Series;


use App\Http\Resources\Resource;

class ShortSeriesResource extends Resource
{
    public function get()
    {
        return [
            "id"               => $this->model->id,
            "slug"             => $this->model->slug,
            "image_vertical"   => (isset($this->model->image)) ? thumb($this->model->image) : "http://34.243.141.252/uploads/vertical.jpg",
            "image_horizontal" => (isset($this->model->poster)) ? thumb($this->model->poster) : "http://34.243.141.252/uploads/horizontal.jpg",
            "type"             => config('api.track_types.shortcut.show'),
        ];
    }


}