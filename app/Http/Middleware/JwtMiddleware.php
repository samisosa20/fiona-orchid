<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 401);
            }
            $token = JWTAuth::parseToken();
            if (!$token->check()) {
                return response()->json(['message' => 'expired token'], 401);
            }
            $payload = $token->getPayload();
            $expirationDate = $payload->get('exp');
            
            if (time() > $expirationDate) {
                return response()->json(['message' => 'El token ha expirado'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
        return $next($request);
    }
}
