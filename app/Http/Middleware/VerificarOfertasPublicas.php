<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerificarOfertasPublicas
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si las ofertas no son públicas, verificar autenticación
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
