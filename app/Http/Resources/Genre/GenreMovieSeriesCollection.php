<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/20/2018
 * Time: 10:40 AM
 */

namespace App\Http\Resources\Genre;


use App\Http\Resources\Collections;
use App\Image;

class GenreMovieSeriesCollection extends Collections
{
    public function __construct($collection)
    {
        parent::__construct($collection);
        $this->paginateList = true;
    }

    public function toArray($row)
    {
        return [
            "id"               => $row->id,
            "slug"             => $row->slug,
            "image_vertical"   => (isset($row->image_id)) ? thumb(Image::find($row->image_id)) : "http://34.243.141.252/uploads/vertical.jpg",
            "image_horizontal" => (isset($row->poster_id)) ? thumb(Image::find($row->poster_id)) : "http://34.243.141.252/uploads/horizontal.jpg",
            "type"             => (!is_null($row->season))
                ? config('api.track_types.shortcut.show')
                : config('api.track_types.shortcut.movie'),
        ];
    }

}