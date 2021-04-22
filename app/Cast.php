<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cast extends Model
{
    protected $guarded              = ['id'];
    protected $with                 = ['translations'];
    public    $translatedAttributes = ['name'];
    // translation class
    use \Dimsav\Translatable\Translatable;

    public function movies()
    {
        return $this->belongsToMany(Movie::class);
    }

    public function show()
    {
        return $this->belongsToMany(Show::class);
    }

    public function poster()
    {
        return $this->belongsTo(Image::class, 'image_id');
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id');
    }
}
