<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/22/2018
 * Time: 11:18 AM
 */

namespace App\Http\Resources\Page;


use App\Http\Resources\Resource;

class PageResource extends Resource
{

    public function get()
    {
        return [
            "title"   => $this->model->title,
            "content" => $this->model->content
        ];
    }
}