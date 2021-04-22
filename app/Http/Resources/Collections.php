<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 3/14/2018
 * Time: 11:55 AM
 */

namespace App\Http\Resources;

use Doctrine\Common\Collections\Collection;
use Illuminate\Contracts\Pagination\Paginator;

class Collections
{
    public function __construct($collection)
    {
        $this->collection = $collection;
        $this->model      = $collection->first();
        $this->type       = $this->getClassName();
    }

    private function getClassName()
    {
        return ( ! is_null($this->model)) ? config('api.track_types.shortcut.' .
                                                   strtolower(substr(get_class($this->model),
                                                       strripos(get_class($this->model), "\\") + 1))) : "";
    }

    public function toArray($row)
    {
        return [];
    }

    protected function paginated()
    {
        $data['data']   = $this->collection->map(function($row){
            return $this->toArray($row);
        })->toArray();
        $data["paging"] = [
            "current_page" => $this->collection->currentPage(),
            "next_page"    => ($this->collection->hasMorePages()) ? $this->collection->currentPage() + 1 : 0,
            "total"        => $this->collection->total(),
        ];

        return $data;
    }

    public function get()
    {
        if($this->collection instanceof Paginator){
            return $this->paginated();
        } else{
            return $this->lists();
        }
    }

    protected function lists()
    {
        return $this->collection->map(function($row){
            return $this->toArray($row);
        })->toArray();
    }
}