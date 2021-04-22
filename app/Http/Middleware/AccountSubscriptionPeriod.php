<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class AccountSubscriptionPeriod
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
        $user         = \JWTAuth::toUser();
        $free_sign_up = \App\Option::where("name", "freeSignUp")->first()['value'];
        if($free_sign_up === "false"){
            if($user->premium_end_date < Carbon::now()){
                return response()->json([
                    "code"  => config("api.response_code.expired_account"),
                    "error" => __("api.response_code." . config("api.response_code.expired_account"))
                ], 400);
            }
        }

        return $next($request);
    }
}
