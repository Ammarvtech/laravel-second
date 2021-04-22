<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Middleware\GetUserFromToken;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JWTCheck extends GetUserFromToken
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
        if( ! $token = $this->auth->setRequest($request)->getToken()){
            return $this->respond('tymon.jwt.absent', 'token_not_provided', 400);
        }

        try{
            $user = $this->auth->authenticate($token);
        } catch(TokenExpiredException $e){
            return response()->json([
                'errors' => 'token_expired',
            ], $e->getStatusCode());
            //return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
        } catch(JWTException $e){
            return response()->json([
                'errors' => 'token_invalid',

            ], $e->getStatusCode());
            //return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
        }

        if( ! $user){

            return response()->json([
                'errors' => 'user_not_found',

            ], 404);
//            return $this->respond('tymon.jwt.user_not_found', 'user_not_found', 404);
        }

        $this->events->fire('tymon.jwt.valid', $user);

        return $next($request);
    }
}
