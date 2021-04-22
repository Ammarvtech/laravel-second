<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/8/2018
 * Time: 3:29 PM
 */

namespace App\Http\Controllers\Api\FormattingData;


use App\ContinueWatching;
use App\Http\Resources\ContinueCollection;
use App\Http\Resources\Genre\GenreMovieSeriesCollection;
use App\Http\Resources\Genre\GenreSeriesCollection;
use App\Http\Resources\Movie\MovieCollection;
use App\Http\Resources\Movie\ShortMovieResource;
use App\Http\Resources\Series\SeriesCollection;
use App\Http\Resources\Series\SeriesResource;
use App\Http\Resources\Series\ShortSeriesResource;

class Home
{

    private static $page = 4;

    public static function index($rows)
    {
        $data = [];

        if((count($rows['continueWatching']) > 0)){
            $data[] = [
                "sectionTitle" => __("api.home.continueWatching"),
                "sectionItems" => (new ContinueCollection($rows['continueWatching']))->get(),
                "url"          => [
                    "api"   => "continue-watching",
                    "query" => ""
                ],
                "loadMore"     => (count($rows['continueWatching']) > self::$page)
            ];
        }

        foreach($rows['series'] as $key => $value){
            if(count($value) > 0){
                $data[] = [
                    "sectionTitle" => __("api.home." . $key),
                    "sectionItems" => (new SeriesCollection($value))->get(),
                    "url"          => [
                        "api"   => "search",
                        "query" => "type=s"
                    ],
                    "loadMore"     => (count($value) > self::$page)
                ];
            }
        }

        foreach($rows['movies'] as $key => $value){
            if(count($value) > 0){
                $data[] = [
                    "sectionTitle" => __("api.home." . $key),
                    "sectionItems" => (new MovieCollection($value))->get(),
                    "url"          => [
                        "api"   => "search",
                        "query" => "type=m" . config("api.home.sort." . $key)
                    ],
                    "loadMore"     => (count($value) > self::$page)
                ];
            }
        }

        if((count($rows['picked']) > 0)){
            $data[] = [
                "sectionTitle" => __("api.home.picked"),
                "sectionItems" => (new GenreMovieSeriesCollection($rows['picked']))->get(),
                "url"          => [
                    "api"   => "picked-list",
                    "query" => ""
                ],
                "loadMore"     => (count($rows['picked']) > self::$page)
            ];
        }

        return $data;
    }

    public static function continueWatching($data)
    {
        $arr = [];
        foreach($data["tracks_info"] as $key => $value){
            if($value['episode_id']){
                $arr[] = array_merge((new ShortSeriesResource($data['episode'][$value['episode_id']]->show))->get()
                    , [
                        "continue_watching" => [
                            'title'      => "S" . $data['episode'][$value['episode_id']]->season . " " . $data['episode'][$value['episode_id']]->title,
                            "percentage" => $value['percent']
                        ]
                    ]);
            } else{

                $arr[] = array_merge((new ShortMovieResource($data['movie'][$value['movie_id']]))->get()
                    , [
                        "continue_watching" => [
                            'title'      => "",
                            "percentage" => $value['percent']
                        ]
                    ]);
            }
        }


        return $arr;
    }
}