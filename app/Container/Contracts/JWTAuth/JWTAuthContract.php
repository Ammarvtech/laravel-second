<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 5/7/2018
 * Time: 5:51 PM
 */

namespace Contracts\JWTAuth;



use Tymon\JWTAuth\JWTAuth;

interface JWTAuthContract
{
    public function __construct(JWTAuth  $auth);

    public function getUserWithToken($user);
}