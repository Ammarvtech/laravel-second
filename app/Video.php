<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function show()
    {
        return $this->belongsTo(Show::class);
    }
}
