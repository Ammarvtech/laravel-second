<?php

namespace Contracts\API\Users;

use App\Config;
use App\Option;
use Illuminate\Http\Request;
use App\User;
use \Tymon\JWTAuth\JWTAuth;

interface IMDBConstract
{

    public function __construct();

    public function getRateByUrl($url);

    public function getRate($imdb_id);

    public function set($data);
}
