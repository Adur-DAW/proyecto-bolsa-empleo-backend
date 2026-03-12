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
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Middleware\VerificarOfertasPublicas;
use App\Http\Controllers\Api\MaestrosController;

Route::post('registrar', [JWTAuthController::class, 'registrar']);
Route::post('login', [JWTAuthController::class, 'login']);
Route::get('config', [App\Http\Controllers\Api\ConfigController::class, 'obtenerConfiguracion']);

Route::get('maestros/familias-profesionales', [MaestrosController::class, 'getFamiliasProfesionales']);
Route::get('maestros/tipos-contrato', [MaestrosController::class, 'getTiposContrato']);

Route::middleware([VerificarOfertasPublicas::class])->group(function () {
    Route::get('ofertas', [OfertasController::class, 'obtener']);
    Route::get('ofertas/{id}', [OfertasController::class, 'obtenerPorId']);
});

Route::get('ofertas/{id}/titulos', [TitulosOfertaController::class, 'obtenerTitulosPorIdOferta']);
Route::get('titulos', [TitulosController::class, 'obtener']);
Route::get('titulos/extra', [TitulosController::class, 'obtenerExtra']);
Route::get('dashboard/invitado', [DashboardController::class, 'invitado']);
Route::get('refrescar', [JWTAuthController::class, 'refrescarToken']);

Route::middleware([JwtMiddleware::class])->group(function () {

    // Obtener usuario autenticado
    Route::get('usuarios/jwt', [JWTAuthController::class, 'obtenerUsuarioJWT']);

    // Dashboards por Rol
    Route::get('dashboard/demandante', [DashboardController::class, 'demandante']);
    Route::get('dashboard/empresa', [DashboardController::class, 'empresa']);
    Route::get('dashboard/admin', [DashboardController::class, 'admin']);

    // Admin stats
    Route::get('admin/stats', [AdminController::class, 'obtenerEstadisticas']);

    // Cerrar sesión
    Route::post('cerrar-sesion', [JWTAuthController::class, 'cerrarSesion']);

    // Obtener demandante autenticado
    Route::get('demandantes/jwt', [DemandantesController::class, 'obtenerJWT']);
    // Actualizar demandante autenticado
    Route::match(['post', 'put'], 'demandantes', [DemandantesController::class, 'actualizar']);
    // Obtener demandante por ID
    Route::get('demandantes/{id}', [DemandantesController::class, 'obtenerPorId']);
    // Descargar CV
    Route::get('demandantes/{id}/cv', [DemandantesController::class, 'descargarCv']);

    // Registrar títulos
    Route::post('titulos', [TitulosController::class, 'registrar']);
    // Actualizar titulo
    Route::put('titulos/{id}', [TitulosController::class, 'actualizar']);
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
    Route::match(['post', 'put'], 'empresas', [EmpresasController::class, 'actualizar']);
    // Validar empresa
    Route::put('empresas/{id}/validar', [EmpresasController::class, 'validar']);
    // Eliminar empresa
    Route::delete('empresas/{id}', [EmpresasController::class, 'eliminar']);

    // Registrar oferta
    Route::post('ofertas', [OfertasController::class, 'registrar']);
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


    // Registrar títulos de una oferta
    Route::post('ofertas/titulos', [TitulosOfertaController::class, 'registrar']);
    // Eliminar títulos de una oferta
    Route::delete('ofertas/{id_oferta}/titulos/{id_titulo}', [TitulosOfertaController::class, 'eliminar']);

    // Obtener ofertas por demandante autenticado
    Route::get('demandantes/jwt/ofertas', [DemandantesOfertaController::class, 'obtenerOfertasJWT']);
    // Inscribirse a una oferta por demandante autenticado
    Route::post('demandantes/jwt/ofertas', [DemandantesOfertaController::class, 'registrarJWT']);
    // Eliminar inscripción a una oferta por demandante autenticado
    Route::delete('demandantes/jwt/ofertas/{id_oferta}', [DemandantesOfertaController::class, 'eliminarJWT']);

    // Obtener demandantes por ID de oferta
    Route::get('ofertas/{id}/demandantes', [DemandantesOfertaController::class, 'obtenerDemandantesPorIdOferta']);
    // Registrar demandante y adjudicar oferta
    Route::post('ofertas/{id}/demandantes', [DemandantesOfertaController::class, 'registrarDemandanteYAdjudicar']);
    // Obtener demandantes por oferta que contengan los títulos de la oferta
    Route::get('ofertas/{id}/demandantes/posibles', [DemandantesOfertaController::class, 'obtenerDemandantesContieneTitulacionPorIdOferta']);
    // Adjudicar oferta a demandante
    Route::put('ofertas/{id_oferta}/demandantes/{id_demandante}/adjudicar', [DemandantesOfertaController::class, 'adjudicarOferta']);
    // Rechazar demandante de una oferta
    Route::put('ofertas/{id_oferta}/demandantes/{id_demandante}/rechazar', [DemandantesOfertaController::class, 'rechazarDemandante']);
    // Inscribir y adjudicar oferta a demandante
    Route::post('ofertas/demandantes', [DemandantesOfertaController::class, 'registrarDemandanteYAdjudicar']);
    // Eliminar demandante de una oferta
    Route::delete('ofertas/{id_oferta}/demandantes/{id_demandante}', [DemandantesOfertaController::class, 'eliminarDemandante']);
});

Route::get('empresas', [EmpresasController::class, 'obtener']);
Route::get('empresas/{id}', [EmpresasController::class, 'show']);
Route::get('demandantes', [DemandantesController::class, 'obtener']);
