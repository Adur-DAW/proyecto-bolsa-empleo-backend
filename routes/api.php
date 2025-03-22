<?php

use App\Http\Controllers\Api\JWTAuthController;
use App\Http\Controllers\Api\DemandantesController;
use App\Http\Controllers\Api\EmpresasController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OfertasController;
use App\Http\Controllers\Api\TitulosController;

Route::post('registrar', [JWTAuthController::class, 'registrar']);
Route::post('login', [JWTAuthController::class, 'login']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('usuarios/jwt', [JWTAuthController::class, 'obtenerUsuarioJWT']);
    Route::post('cerrar-sesion', [JWTAuthController::class, 'cerrarSesion']);

    Route::get('demandantes/jwt', [DemandantesController::class, 'obtenerJWT']);
    Route::get('demandantes', [DemandantesController::class, 'obtener']);
    Route::post('demandantes', [DemandantesController::class, 'registrar']);

    Route::get('empresas/jwt', [EmpresasController::class, 'obtenerJWT']);
    Route::get('empresas', [EmpresasController::class, 'obtener']);
    Route::post('empresas', [EmpresasController::class, 'registrar']);

    Route::get('ofertas', [OfertasController::class, 'obtener']);
    Route::post('ofertas', [OfertasController::class, 'registrar']);

    Route::get('titulos', [TitulosController::class, 'obtener']);
    Route::post('titulos', [TitulosController::class, 'registrar']);
});
