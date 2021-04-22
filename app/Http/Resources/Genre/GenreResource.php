<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/14/2018
 * Time: 12:31 PM
 */

namespace App\Http\Resources\Genre;


use App\Http\Resources\Movie\MovieCollection;
use App\Http\Resources\Series\SeriesCollection;
use App\Http\Resources\Resource;

class GenreResource extends Resource
{
    public function get()
    {
        return [
            "id"   => $this->model->id,
            "name" => $this->model->title,
            "slug" => $this->model->slug
        ];
    }

}