<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Contracts\Episodes\EpisodesContract;

class EpisodesController extends Controller
{
    public function __construct(EpisodesContract $episodes)
    {
        $this->episodes = $episodes;
    }

    public function show(Request $request, $id)
    {
        $episode = $this->episodes->get($id);

        $data['episode'] = $episode;

        return view('frontend.single-episode', $data);
    }
}
