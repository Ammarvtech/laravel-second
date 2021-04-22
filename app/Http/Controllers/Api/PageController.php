<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Page\PageResource;
use Contracts\Pages\PageContract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    //
    public function __construct(
        PageContract $page
    ){
        $this->page = $page;
    }

    public function show($slug = "", Request $request)
    {
        $slug = $request->type ?? $slug;
        $row  = $this->page->getBySlug($slug);
        if(is_null($row)){
            return response()->json(['errors' => 'Page not found'], 400);
        }

        return response()->json(
            (new PageResource($row))->get(),
            200
        );
    }
}
