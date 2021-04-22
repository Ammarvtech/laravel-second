<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Contracts\Episodes\EpisodesContract;
use App\Http\Requests\Api\Shows\Shows as ShowValidation;

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

    public function paused(ShowValidation $request)
    {
        $code = $this->episodes->paused($request, \JWTAuth::toUser()->id);

        if($code != \config("api.response_code.success")){
            return response()->json(["errors" => __('api.response_code.' . $code)], 401);
        }

        return response()->json(["message" => __('api.response_code.' . $code)], 200);
    }
}
