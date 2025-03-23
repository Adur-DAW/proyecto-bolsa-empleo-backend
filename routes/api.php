<?php

use App\Http\Controllers\Api\JWTAuthController;
use App\Http\Controllers\Api\DemandantesController;
use App\Http\Controllers\Api\EmpresasController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OfertasController;
use App\Http\Controllers\Api\TitulosController;
use App\Http\Controllers\Api\TitulosDemandanteController;

Route::post('registrar', [JWTAuthController::class, 'registrar']);
Route::post('login', [JWTAuthController::class, 'login']);

Route::get('empresas', [EmpresasController::class, 'obtener']);
Route::get('demandantes', [DemandantesController::class, 'obtener']);
Route::get('ofertas', [OfertasController::class, 'obtener']);
Route::get('titulos', [TitulosController::class, 'obtener']);
Route::get('refrescar', [JWTAuthController::class, 'refrescarToken']);

Route::middleware([JwtMiddleware::class])->group(function () {

    Route::get('usuarios/jwt', [JWTAuthController::class, 'obtenerUsuarioJWT']);
    Route::post('cerrar-sesion', [JWTAuthController::class, 'cerrarSesion']);

    Route::get('demandantes/jwt', [DemandantesController::class, 'obtenerJWT']);
    // Route::post('demandantes', [DemandantesController::class, 'registrar']);

    Route::get('empresas/jwt', [EmpresasController::class, 'obtenerJWT']);
    // Route::post('empresas', [EmpresasController::class, 'registrar']);

    Route::post('ofertas', [OfertasController::class, 'registrar']);
    Route::post('titulos', [TitulosController::class, 'registrar']);

    Route::get('titulos/demandante/jwt', [TitulosDemandanteController::class, 'obtenerJWT']);
    Route::post('titulos/demandante', [TitulosDemandanteController::class, 'registrar']);
});
