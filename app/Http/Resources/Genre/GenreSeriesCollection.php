<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/19/2018
 * Time: 4:12 PM
 */

namespace App\Http\Resources\Genre;


use App\Http\Resources\Collections;
use App\Http\Resources\Series\SeriesCollection;

class GenreSeriesCollection extends Collections
{

    public function toArray($row)
    {
        $series = $row->shows()->paginate(21);
        $rows   = $series->all();

        return [
            "sectionTitle" => $row->title,
            "sectionItems" => (count($rows) > 0) ? (new SeriesCollection(collect($rows)))->get() : [],
            "loadMore"     => ($series->total() >= 22)
        ];
    }
}