<?php

namespace Repos\API\Users;

use App\Config;
use App\Option;
use Contracts\API\Users\IMDBConstract;
use Illuminate\Database\QueryException;
use App\User;
use \Tymon\JWTAuth\JWTAuth;

class IMDBRepos implements IMDBConstract
{


    public function __construct()
    {
    }

    public function getRate($imdeb_id)
    {
        // TODO: Implement getRate() method.
    }

    public function getRateByUrl($url)
    {
        //read file as a string
        $handle    = fopen($url, 'r');
        $file_text = stream_get_contents($handle);
        fclose($handle);

        // get index for rate field
        $index  = strpos($file_text, 'itemprop="ratingValue"');
        $length = strlen('itemprop="ratingValue"');

        // get file start from rating value
        $new_sub_file = substr($file_text, $index + $length + 1);

        // get index of slash /10
        $slash_index = strpos($new_sub_file, "/");

        //get exact rating
        return substr($new_sub_file, 0, $slash_index - 1);
    }

    public function set($data)
    {

    }
}