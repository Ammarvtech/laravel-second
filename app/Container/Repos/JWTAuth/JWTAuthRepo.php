<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 5/7/2018
 * Time: 5:53 PM
 */

namespace Repos\JWTAuth;


use Contracts\JWTAuth\JWTAuthContract;
use Tymon\JWTAuth\JWTAuth;

class JWTAuthRepo implements JWTAuthContract
{

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    public function getUserWithToken($user)
    {
        $token = $this->auth->fromUser($user, ["exp" => strtotime("+1 year", time())]);

        //set old token as invalid token
        try{
            $previous_token = $user->token;
            if($previous_token && $this->auth->authenticate($previous_token)){
                $this->auth->invalidate($previous_token);
            }
        } catch(\Exception $exception){
            //do nothing
        }

        $user->token = $token;
        $user->save();

        return $user;
    }
}