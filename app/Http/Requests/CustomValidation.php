<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/18/2018
 * Time: 2:27 PM
 */

namespace App\Http\Requests;


use Illuminate\Support\Facades\Validator;

class CustomValidation
{
    public function __construct()
    {
        $this->init();
    }

    public static function init()
    {
        self::minOrEqual();
    }

    public static function minOrEqual()
    {
        Validator::extend("min_or_equal", function($attribute, $value, $parameters, $validator){
            return $value <= request()->get($parameters[0]);
        });

        Validator::replacer('min_or_equal', function($message, $attribute, $rule, $parameters){

            return trans("api.validation.min_or_equal", [
                "attribute"  => __("api.variables." . $attribute),
                "parameters" => __("api.variables." . $parameters[0]),
            ]);
        });
    }
}