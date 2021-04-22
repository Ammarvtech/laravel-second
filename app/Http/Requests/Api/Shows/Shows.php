<?php

namespace App\Http\Requests\Api\Shows;

use App\Http\Requests\Api\Base;
use Illuminate\Foundation\Http\FormRequest;

class Shows extends Base
{
    private $subScope = "shows/";

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(\Request::is(parent::$scope . $this->subScope . 'paused')){
            return self::paused();
        } elseif(\Request::is(parent::$scope . $this->subScope . 'review')){
            return self::review();
        } else{
            return [
                //
            ];
        }
    }


    public static function paused()
    {
        return [
            "id"         => "required",
            "episode_id" => "required",
            "paused_at"  => "required|min_or_equal:duration",
            "duration"   => 'required'
        ];
    }

    public static function review()
    {
        return [
            "id"   => "required",
            "rate" => "required|numeric|between:1,5"
        ];
    }
}
