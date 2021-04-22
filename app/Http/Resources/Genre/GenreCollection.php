<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/14/2018
 * Time: 12:31 PM
 */

namespace App\Http\Resources\Genre;


use App\Http\Resources\Collections;

class GenreCollection extends Collections
{

    public function toArray($row)
    {
        return [
            "id"   => $row->id,
            "name" => $row->title,
            "slug" => $row->slug
        ];
    }


}