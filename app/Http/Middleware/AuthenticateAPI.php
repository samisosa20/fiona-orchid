<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class AuthenticateAPI
{
    public function handle($request, Closure $next)
    {
        try {
            // Intenta autenticar al usuario utilizando el token JWT
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Autenticar al usuario en Laravel si es necesario
            Auth::login($user);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
