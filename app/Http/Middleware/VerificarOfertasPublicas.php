<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerificarOfertasPublicas
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('app.ofertas_publicas')) {
            try {
                JWTAuth::parseToken()->authenticate();
            } catch (\Exception $e) {
                return response()->json(['error' => 'No autorizado'], 401);
            }
        }

        return $next($request);
    }
}
