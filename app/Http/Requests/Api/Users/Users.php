<?php

namespace App\Http\Requests\Api\Users;

use App\Http\Requests\Api\Base;
use Illuminate\Foundation\Http\FormRequest;

class Users extends Base
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(\Request::is(parent::$scope . 'login')){
            return self::login();
        } elseif(\Request::is(parent::$scope . 'register')){
            return self::register();
        } elseif(\Request::is(parent::$scope . 'payment')){
            return self::payment();
        } elseif(\Request::is(parent::$scope . 'forget-password')){
            return self::forgetPassword();
        } elseif(\Request::is(parent::$scope . 'follow')){
            return self::follow();
        } elseif(\Request::is(parent::$scope . 'edit-profile')){
            return self::editProfile();
        } elseif(\Request::is(parent::$scope . 'upload-avatar')){
            return self::uploadAvatar();
        } else{
            return [
                //
            ];
        }
    }


    public static function login()
    {
        return [
            "email"        => "required|email|max:255",
            "password"     => "required_without:fb_id|min:6",
            "device_token" => "required",
            "fb_id"        => "required_without_all:password",
            "avatar"       => "required_if:fb_id,email|url",
            "name"         => "required_without_all:password"
        ];
    }

    public static function register()
    {
        return [
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required_without:fb_id|min:6|confirmed',
            "fb_id"    => "required_without_all:password",
            'name'     => 'required',
        ];
    }

    public static function payment()
    {
        return [
            'card_number'   => 'required|min:14|max:19',
            'exp_date'      => 'required',
            'security_code' => 'required|min:3|max:3',
            'name_on_card'  => 'required',
        ];
    }

    public static function forgetPassword()
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }

    public static function follow()
    {
        return [
            'type' => 'required',
            'id'   => 'required',
        ];
    }

    public static function editProfile()
    {
        return [
            "name"         => "min:3",
            'password'     => 'min:6',
            'old_password' => 'min:6',
        ];
    }

    public static function uploadAvatar()
    {
        return [
            "image" => "required|image"
        ];
    }
}
