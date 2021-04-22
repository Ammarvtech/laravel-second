<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/19/2018
 * Time: 4:12 PM
 */

namespace App\Http\Resources\Genre;


use App\Http\Resources\Collections;
use App\Http\Resources\Movie\MovieCollection;

class GenreMovieCollection extends Collections
{
    public function toArray($row)
    {
        $movies = $row->movies()->paginate(21);
        $rows   = $movies->all();

        return [
            "sectionTitle" => $row->title,
            "sectionItems" => (count($rows) > 0) ? (new MovieCollection(collect($rows)))->get() : [],
            "loadMore"     => ($movies->total() >= 22)
        ];
    }

}