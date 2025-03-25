<?php

use App\Http\Controllers\Api\JWTAuthController;
use App\Http\Controllers\Api\DemandantesController;
use App\Http\Controllers\Api\EmpresasController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OfertasController;
use App\Http\Controllers\Api\TitulosController;
use App\Http\Controllers\Api\TitulosDemandanteController;
use App\Http\Controllers\Api\TitulosOfertaController;
use App\Http\Controllers\Api\DemandantesOfertaController;

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
    Route::put('demandantes', [DemandantesController::class, 'actualizar']);
    Route::get('demandante/titulos', [TitulosDemandanteController::class, 'obtenerJWT']);
    Route::post('demandante/titulos', [TitulosDemandanteController::class, 'registrar']);
    Route::put('demandante/titulos/{id}', [TitulosDemandanteController::class, 'actualizar']);
    Route::delete('demandante/titulos/{id}', [TitulosDemandanteController::class, 'eliminar']);

    Route::get('empresas/jwt', [EmpresasController::class, 'obtenerJWT']);
    Route::put('empresas', [EmpresasController::class, 'actualizar']);

    Route::post('ofertas', [OfertasController::class, 'registrar']);
    Route::get('ofertas/{id}', [OfertasController::class, 'obtenerPorId']);
    Route::put('ofertas/{id}', [OfertasController::class, 'actualizar']);
    Route::delete('ofertas/{id}', [OfertasController::class, 'eliminar']);
    Route::delete('ofertas/{id_oferta}/titulos/{id_titulo}', [TitulosOfertaController::class, 'eliminar']);
    Route::get('ofertas/{id}/demandantes', [OfertasController::class, 'obtenerDemandantesPorIdOferta']);
    Route::post('ofertas/{id}/demandantes', [OfertasController::class, 'registrarDemandante']);
    Route::delete('ofertas/{id_oferta}/demandantes/{id_demandante}', [OfertasController::class, 'eliminarDemandante']);
    Route::get('demandantes/jwt/ofertas-por-titulos', [OfertasController::class, 'obtenerPorTitulosDemandanteJWT']);
    Route::get('empresas/jwt/ofertas', [OfertasController::class, 'obtenerPorEmpresaJWT']);

    Route::get('ofertas/{id}/titulos', [TitulosOfertaController::class, 'obtenerTitulosPorIdOferta']);
    Route::post('ofertas/titulos', [TitulosOfertaController::class, 'registrar']);

    Route::post('titulos', [TitulosController::class, 'registrar']);
    Route::get('titulos/{id}/ofertas', [TitulosOfertaController::class, 'obtenerOfertasPorIdTitulo']);

    Route::get('demandantes/jwt/ofertas', [DemandantesOfertaController::class, 'obtenerOfertasJWT']);
    Route::get('ofertas/{id}/demandantes', [DemandantesOfertaController::class, 'obtenerDemandantesPorIdOferta']);
    Route::post('ofertas/{id}/demandantes/jwt', [DemandantesOfertaController::class, 'registrarJWT']);
    Route::post('ofertas/{id}/demandantes', [DemandantesOfertaController::class, 'registrarDemandante']);
    Route::delete('ofertas/{id_oferta}/demandantes/jwt', [DemandantesOfertaController::class, 'eliminarJWT']);
    Route::delete('ofertas/{id_oferta}/demandantes/{id_demandante}', [DemandantesOfertaController::class, 'eliminarDemandante']);
});
