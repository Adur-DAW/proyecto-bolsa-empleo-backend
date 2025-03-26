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
Route::get('titulos/extra', [TitulosController::class, 'obtenerExtra']);
Route::get('refrescar', [JWTAuthController::class, 'refrescarToken']);

Route::middleware([JwtMiddleware::class])->group(function () {

    // Obtener usuario autenticado
    Route::get('usuarios/jwt', [JWTAuthController::class, 'obtenerUsuarioJWT']);
    // Cerrar sesión
    Route::post('cerrar-sesion', [JWTAuthController::class, 'cerrarSesion']);

    // Obtener demandante autenticado
    Route::get('demandantes/jwt', [DemandantesController::class, 'obtenerJWT']);
    // Actualizar demandante autenticado
    Route::put('demandantes', [DemandantesController::class, 'actualizar']);

    // Registrar títulos
    Route::post('titulos', [TitulosController::class, 'registrar']);
    // Eliminar títulos
    Route::delete('titulos/{id}', [TitulosController::class, 'eliminar']);

    // Obtener títulos del demandante autenticado
    Route::get('demandante/titulos', [TitulosDemandanteController::class, 'obtenerJWT']);
    // Registrar títulos del demandante autenticado
    Route::post('demandante/titulos', [TitulosDemandanteController::class, 'registrar']);
    // Actualizar títulos del demandante autenticado
    Route::put('demandante/titulos/{id}', [TitulosDemandanteController::class, 'actualizar']);
    // Eliminar títulos del demandante autenticado
    Route::delete('demandante/titulos/{id}', [TitulosDemandanteController::class, 'eliminar']);

    // Obtener empresa autenticada
    Route::get('empresas/jwt', [EmpresasController::class, 'obtenerJWT']);
    // Actualizar empresa autenticada
    Route::put('empresas', [EmpresasController::class, 'actualizar']);

    // Registrar oferta
    Route::post('ofertas', [OfertasController::class, 'registrar']);
    // Obtener oferta por ID
    Route::get('ofertas/{id}', [OfertasController::class, 'obtenerPorId']);
    // Actualizar oferta
    Route::put('ofertas/{id}', [OfertasController::class, 'actualizar']);
    // Eliminar oferta
    Route::delete('ofertas/{id}', [OfertasController::class, 'eliminar']);

    // Obtener ofertas por empresa autenticada
    Route::get('empresas/jwt/ofertas', [OfertasController::class, 'obtenerPorEmpresaJWT']);

    // Eliminar demandante de una oferta
    Route::delete('ofertas/{id_oferta}/demandantes/{id_demandante}', [OfertasController::class, 'eliminarDemandante']);
    // Obtener demandantes por ID de oferta que contengan los títulos de la oferta
    Route::get('demandantes/jwt/ofertas-por-titulos', [OfertasController::class, 'obtenerPorTitulosDemandanteJWT']);


    // Obtener títulos de una oferta
    Route::get('ofertas/{id}/titulos', [TitulosOfertaController::class, 'obtenerTitulosPorIdOferta']);
    // Registrar títulos de una oferta
    Route::post('ofertas/titulos', [TitulosOfertaController::class, 'registrar']);
    // Eliminar títulos de una oferta
    Route::delete('ofertas/{id_oferta}/titulos/{id_titulo}', [TitulosOfertaController::class, 'eliminar']);

    // Obtener ofertas por demandante autenticado
    Route::get('demandantes/jwt/ofertas', [DemandantesOfertaController::class, 'obtenerOfertasJWT']);
    // Inscribirse a una oferta por demandante autenticado
    Route::post('ofertas/{id}/demandantes/jwt', [DemandantesOfertaController::class, 'registrarJWT']);
    // Eliminar inscripción a una oferta por demandante autenticado
    Route::delete('ofertas/{id}/demandantes/jwt', [DemandantesOfertaController::class, 'eliminarJWT']);

    // Obtener demandantes por ID de oferta
    Route::get('ofertas/{id}/demandantes', [DemandantesOfertaController::class, 'obtenerDemandantesPorIdOferta']);
    // Registrar demandante y adjudicar oferta
    Route::post('ofertas/{id}/demandantes', [DemandantesOfertaController::class, 'registrarDemandanteYAdjudicar']);
    // Obtener demandantes por oferta que contengan los títulos de la oferta
    Route::get('ofertas/{id}/demandantes/posibles', [DemandantesOfertaController::class, 'obtenerDemandantesContieneTitulacionPorIdOferta']);
    // Adjudicar oferta a demandante
    Route::put('ofertas/{id_oferta}/demandantes/{id_demandante}/adjudicar', [DemandantesOfertaController::class, 'adjudicar']);
    // Eliminar demandante de una oferta
    Route::delete('ofertas/{id_oferta}/demandantes/{id_demandante}', [DemandantesOfertaController::class, 'eliminarDemandante']);
});
