<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IMDB extends Model
{
    //
    protected $guarded = ['id'];
    protected $table = "imdb";

    public function movies()
    {
        return $this->belongsTo(Movie::class, "movie_id");
    }

    public function shows()
    {
        return $this->belongsTo(Show::class, "show_id");
    }
}
