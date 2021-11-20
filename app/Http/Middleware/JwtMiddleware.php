<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role = '')
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            switch ($role) {
                case 'admin':
                    if($user->is_admin == 0){
                        return response()->json(['status' => 'Unauthorized entry']);
                    }
                    break;
                case 'user':
                    if($user->is_admin == 1){
                        return response()->json(['status' => 'Unauthorized entry']);
                    }
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => 'Token is Invalid']);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => 'Token is Expired']);
            }else{
                return response()->json(['status' => 'Authorization Token not found']);
            }
        }
        return $next($request);
    }
}