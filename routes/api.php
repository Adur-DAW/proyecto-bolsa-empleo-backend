<?php

use App\Http\Controllers\Api\JWTAuthController;
use App\Http\Controllers\Api\DemandantesController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('registrar', [JWTAuthController::class, 'registrar']);
Route::post('login', [JWTAuthController::class, 'login']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('usuarios/jwt', [JWTAuthController::class, 'obtenerUsuarioJWT']);
    Route::post('cerrar-sesion', [JWTAuthController::class, 'cerrarSesion']);

    Route::get('demandantes', [DemandantesController::class, 'obtener']);
    Route::post('demandantes', [DemandantesController::class, 'registrar']);
});
