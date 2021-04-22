<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/14/2018
 * Time: 11:55 AM
 */

namespace App\Http\Resources;


use Illuminate\Database\Eloquent\Model;

class Resource
{
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->type = $this->getClassName();
    }

    private function getClassName()
    {
        return config('api.track_types.shortcut.' .
            strtolower(substr(get_class($this->model),
                strripos(get_class($this->model), "\\") + 1)));
    }
}