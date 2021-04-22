<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/19/2018
 * Time: 7:11 PM
 */

namespace App\Http\Controllers\Api\FormattingData;


class Genre
{

    public static function index($data)
    {
        $id  = array();
        $arr = array_merge($data, [
            [
                "id"   => -2,
                "name" => __("api.movie"),
                "slug" => "movie",
            ],
            [
                "id"   => -1,
                "name" => __("api.series"),
                "slug" => "series",
            ]
        ]);
        foreach ($arr as $key => $row) {
            $id[$key] = $row['id'];
        }
        array_multisort($id, SORT_ASC, $arr);

        return $arr;
    }
}