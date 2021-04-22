<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/20/2018
 * Time: 10:40 AM
 */

namespace App\Http\Resources;


use App\Http\Resources\Collections;
use App\Image;

class ContinueCollection extends Collections
{
    public function toArray($row)
    {
        return [
            "id"               => $row->movies->id ?? $row->shows->id,
            "slug"             => $row->movies->slug ?? $row->shows->slug,
            "title"      => ( ! is_null($row->episode_id)) ? "S" . $row->shows->season . " " . $row->shows->title : $row->movies->title,
            "season"           => $row->shows->season??"",
            "production"       => $row->movies->production ?? $row->shows->production,
            "image_vertical"   => (isset($row->movies->image_id)) ? thumb(($row->movies->image)) : "",
            "image_horizontal" => (isset($row->poster_id)) ? thumb(($row->poster)) : "",
            "image" => (isset($row->shows->poster_id)) ? thumb(($row->shows->poster)) : thumb(($row->movies->poster)),
            //"age" => $row->movies->age ?? $row->shows->age,
            //"publish_date" => $row->movies->publish_date ?? $row->shows->publish_date,
            "is_kid" => $row->movies->is_kid ?? $row->shows->is_kid,
            "status" => $row->movies->status ?? $row->shows->status,
            "type"             => ( ! is_null($row->episode_id)) ? config('api.track_types.shortcut.show') : config('api.track_types.shortcut.movie'),
            "continue_watching"    => [
                "title"      => ( ! is_null($row->episode_id)) ? "S" . $row->episodes->season . " " . $row->episodes->title : $row->movies->title,
                "percentage" => $row->percent
            ]
        ];
    }

}