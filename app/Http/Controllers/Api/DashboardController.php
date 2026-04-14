<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demandante;
use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
{
    public function invitado()
    {
        return response()->json([
            'ofertas_activas' => Oferta::where('abierta', true)->count(),
            'candidatos_registrados' => Demandante::count(),
            'empresas_colaboradoras' => Empresa::where('validado', true)->count(),
            'ultimas_ofertas' => config('app.ofertas_publicas')
                ? Oferta::with('empresa')->where('abierta', true)->latest('fecha_publicacion')->take(3)->get()
                : []
        ]);
    }

    public function demandante()
    {
        $usuario = JWTAuth::parseToken()->authenticate();
        $demandante = $usuario->demandante;

        if (!$demandante) {
            return response()->json(['error' => 'Demandante no encontrado'], 404);
        }

        $titulosIds = $demandante->titulos->pluck('id_titulo');
        $matches = Oferta::where('abierta', true)
            ->whereHas('titulos', function ($q) use ($titulosIds) {
                $q->whereIn('id_titulo', $titulosIds);
            })
            ->whereDoesntHave('demandantes', function ($q) use ($demandante) {
                $q->where('demandantes.id_demandante', $demandante->id_demandante);
            })
            ->with('empresa')
            ->latest('fecha_publicacion')
            ->take(5)
            ->get();


        $candidaturas = DB::table('demandantes_oferta')
            ->join('ofertas', 'demandantes_oferta.id_oferta', '=', 'ofertas.id')
            ->join('empresas', 'ofertas.id_empresa', '=', 'empresas.id_empresa')
            ->where('demandantes_oferta.id_demandante', $demandante->id_demandante)
            ->select(
                'ofertas.id',
                'ofertas.nombre as oferta',
                'empresas.id_empresa as id_empresa',
                'empresas.nombre as empresa',
                'demandantes_oferta.fecha as fecha_inscripcion',
                'demandantes_oferta.adjudicada'
            )
            ->orderByDesc('demandantes_oferta.fecha')
            ->take(5)
            ->get();


        $nuevasSemana = Oferta::where('abierta', true)
            ->where('fecha_publicacion', '>=', now()->subDays(7))
            ->count();

        return response()->json([
            'matches' => $matches,
            'mis_candidaturas' => $candidaturas,
            'nuevas_esta_semana' => $nuevasSemana
        ]);
    }


    public function empresa()
    {
        $usuario = JWTAuth::parseToken()->authenticate();
        $empresa = $usuario->empresa;

        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        $candidatosPendientes = DB::table('demandantes_oferta')
            ->join('ofertas', 'demandantes_oferta.id_oferta', '=', 'ofertas.id')
            ->where('ofertas.id_empresa', $empresa->id_empresa)
            ->where('ofertas.abierta', true)
            ->where('demandantes_oferta.adjudicada', false)
            ->count();

        $ofertasActivas = Oferta::where('id_empresa', $empresa->id_empresa)
            ->where('abierta', true)
            ->withCount('demandantes')
            ->get()
            ->map(function ($oferta) {
                return [
                    'id' => $oferta->id,
                    'nombre' => $oferta->nombre,
                    'inscritos' => $oferta->demandantes_count,
                    'fecha_publicacion' => $oferta->fecha_publicacion
                ];
            });

        return response()->json([
            'candidatos_pendientes' => $candidatosPendientes,
            'ofertas_activas' => $ofertasActivas
        ]);
    }


    public function admin()
    {
        $validacionesPendientes = Empresa::where('validado', false)->count();

        $totalOfertasActivas = Oferta::where('abierta', true)->count();
        $totalDemandantes = Demandante::count();
        $tasaAdjudicacion = 0;

        $totalOfertasCerradas = Oferta::where('abierta', false)->count();
        if ($totalOfertasCerradas > 0) {

            $ofertasConAdjudicado = Oferta::where('abierta', false)
                ->whereHas('demandantes', function ($q) {
                    $q->where('adjudicada', true);
                })->count();
            $tasaAdjudicacion = round(($ofertasConAdjudicado / $totalOfertasCerradas) * 100, 1);
        }

        $ultimasEmpresas = Empresa::latest()->take(3)->get(['nombre', 'created_at']);
        $ultimosDemandantes = Demandante::latest()->take(3)->get(['nombre', 'apellido1', 'created_at']);

        return response()->json([
            'validaciones_pendientes' => $validacionesPendientes,
            'kpis' => [
                'ofertas_activas' => $totalOfertasActivas,
                'total_demandantes' => $totalDemandantes,
                'tasa_exito_ofertas' => $tasaAdjudicacion . '%'
            ],
            'actividad_reciente' => [
                'empresas' => $ultimasEmpresas,
                'demandantes' => $ultimosDemandantes
            ]
        ]);
    }
}
