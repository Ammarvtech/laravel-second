<?php

function thumb($image, $size = null)
{
    if (empty($image))
        return null;
        
    if (empty($size)) {
        $url = $image->title . '.' . $image->ext;
    } else{
        $url = $image->title . '_' . $size . '.' . $image->ext;
    }
    
    return env('AWS_CLOUD_DOMAIN') . $url;
}

function messages($message, $type = 'success')
{
    if($type == 'error'){
        return ['msg-type' => 'danger', 'msg' => 'backend.' . $type . '_' . $message];
    }

    return ['msg-type' => $type, 'msg' => 'backend.' . $type . '_' . $message];
}

function make_slug($title, $separator = '-')
{
    $flip = $separator == '-' ? '_' : '-';

    $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

    // Remove all characters that are not the separator, letters, numbers, or whitespace.
    $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));

    // Replace all separator characters and whitespace by a single separator
    $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

    return trim($title, $separator);
}

function option($name)
{
    $option = resolve('Contracts\Options\OptionsContract');

    return $option->getByName($name)->value;
}

function country_code($name)
{
    $countries = resolve('Contracts\Countries\CountriesContract');
    $code = $countries->getCode($name);
    return $code;
}

function currency_code($name)
{
    $countries = resolve('Contracts\Countries\CountriesContract');
    $code = $countries->getCurrencyCode($name);
    return $code;
}

function slider($type = 'show')
{
    $slider = option('slider');
    $ids    = [];

    foreach(json_decode($slider) as $slide){
        if($slide->type == $type){
            $ids[] = $slide->id;
        }
    }

    return $ids;
}

function get_slider()
{
    $shows_slider  = slider();
    $movies_slider = slider('movie');
    $objects       = collect();

    $shows  = resolve('Contracts\Shows\ShowsContract');
    $movies = resolve('Contracts\Movies\MoviesContract');

    if(count($movies_slider) > 0){
        $movies_obj = $movies->getIn($movies_slider);
        foreach($movies_obj as $movie){
            $objects->push($movie);
        }
    }

    if(count($shows_slider) > 0){
        $shows_obj = $shows->getIn($shows_slider);
        foreach($shows_obj as $show){
            $objects->push($show);
        }
    }


    return $objects;
}

function generateToken()
{
    return str_random(60);
}

function videoUrl($video)
{
    if (!isset($video->title))
        return null;

    $arr = explode('/', $video->title);

    $end  = end($arr);
    $url  = env('AWS_CLOUD_DOMAIN');
    $url .= 'videos/'.$video->title. '/' . $end . '.m3u8';

    return $url;
}

function imdb_rate($url)
{
    try {
        $handle    = fopen($url, 'r');
    } catch (Exception $e) {
        return 0;
    }

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
    $number = substr($new_sub_file, 0, $slash_index - 1);
    if(is_numeric($number)){
        return $number;
    }else{
        return 0;
    }

}

function websiteTitle() {
    return trans('frontend.website_title');
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function video_type($movie){
    if($movie!=""){
        $array['movie'] = "Movie";
        $array['trailer_movie'] = "Movie Trailer"; 
        $array['show'] = "Show"; 
        $array['trailer_show'] = "Show Trailer"; 
        return $array[$movie];
    }
    return "";
}

function convertToReadableSize($size){
  $base = log($size) / log(1024);
  $suffix = array("", "KB", "MB", "GB", "TB");
  $f_base = floor($base);
  return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
}

function getMovieById($id){
    $movies = resolve('Contracts\Movies\MoviesContract');
    return $movies->getNameById($id);
}

function getShowById($id){
    $episode = resolve('Contracts\Episodes\EpisodesContract');
    return $episode->getNameById($id);
}

function removeSpecialChar($title){
   return preg_replace('/[^A-Za-z0-9\-]/', '', $title);
}
