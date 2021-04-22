<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Contracts\Pages\PageContract;

class PageController extends Controller
{
    public function __construct(PageContract $pages)
    {
        $this->pages = $pages;
    }

    public function show(Request $request, $slug)
    {
        $pages = $this->pages->getBySlug($slug);

        $data['page'] = $pages;

        return view('frontend.single-page', $data);
    }
}