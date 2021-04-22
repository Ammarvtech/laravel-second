<?php

namespace App\Http\Middleware;

use Closure;
use App\Country;
use App\CountryException;
use Stevebauman\Location\Facades\Location;
use Route;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ip= \Request::ip();
        $position = Location::get('39.52.42.77'); //'39.52.42.77' 185.35.50.4
        $exp =  CountryException::where('ip', $ip)->first();
        $code = $position->countryCode;
        $country = Country::where('code', '=', $code)->where('status', '=', 1)->first();
        
        if($exp != null && $country === null){
            if(\Session::has('locale'))
            {
                \App::setlocale(\Session::get('locale'));
            }
            \Session::put('amount',1);
            \Session::put('code','GBP');
            \Session::put('is_left',1);
            \Session::put('ip',$ip);
            \Session::put('country_name','United Kingdom');
            return $next($request);
        }
        if($country === null) {
            //abort(403, 'Your Country is Blocked.');
            return response(view('errors.country_restriction'));
        }else{
            if(\Session::has('locale'))
            {
                \App::setlocale(\Session::get('locale'));
            }
            \Session::put('amount',$country->amount);
            \Session::put('code',$country->curriency_code);
            \Session::put('is_left',$country->is_left);
            \Session::put('ip',$ip);
            \Session::put('country_name',$country->name);
        }
        return $next($request);
    }
}
