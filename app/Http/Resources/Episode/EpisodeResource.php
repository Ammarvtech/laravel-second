<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/14/2018
 * Time: 12:31 PM
 */

namespace App\Http\Resources\Series;


use App\Http\Resources\Resource;

class EpisodeResource extends Resource
{
    public function get()
    {
        return [
            "title"  => $this->model->title,
            "season" => $this->model->season
        ];
    }
}