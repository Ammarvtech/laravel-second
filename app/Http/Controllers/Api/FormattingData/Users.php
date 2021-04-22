<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/21/2018
 * Time: 4:19 PM
 */

namespace App\Http\Controllers\Api\FormattingData;


use App\Http\Resources\Movie\MovieCollection;
use App\Http\Resources\Series\SeriesCollection;

class Users
{

    public static function followList($data)
    {
        return [
            [
                "sectionTitle" => __("api.movie"),
                "sectionItems" => (count($data["movie"]) > 0) ? (new  MovieCollection(($data["movie"])))->get() : []
            ],
            [
                "sectionTitle" => __("api.series"),
                "sectionItems" => (count($data["series"]) > 0) ? (new SeriesCollection($data["series"]))->get() : [],
            ]
        ];
    }
}