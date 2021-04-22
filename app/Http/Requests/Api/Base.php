<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class Base extends FormRequest
{
    protected static $scope = "api/";

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    public function response(array $errors)
    {
        $data = [];

        foreach ($errors as $key => $value) {
            $data[] = [
                "field"   => $key,
                "message" => implode(",", $value)
            ];
        }

        return response()->json(["code" => 422, "error" => $data], 422);
    }
}
