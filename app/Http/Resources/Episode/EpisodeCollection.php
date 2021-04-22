<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/14/2018
 * Time: 12:31 PM
 */

namespace App\Http\Resources\Series;


use App\Http\Resources\Collections;

class EpisodeCollection extends Collections
{

    public function toArray($row)
    {
        return [
            "id"               => $row->id,
            "image_vertical"   => (isset($row->image)) ? thumb($row->image) : "http://34.243.141.252/uploads/vertical.jpg",
            "image_horizontal" => (isset($row->poster)) ? thumb($row->poster) : "http://34.243.141.252/uploads/horizontal.jpg",
            "name"             => $row->title,
            "slug"             => $row->slug,
            "type"             => $this->type,
            "year"             => $row->production,
            "category"         => $row->genres->pluck('title')->toArray(),
            "rate"             => $row->rating_avg,
            "length"           => "120"
        ];
    }

}