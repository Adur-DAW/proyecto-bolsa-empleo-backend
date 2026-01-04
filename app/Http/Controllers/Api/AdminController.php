<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demandante;
use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    public function getStats()
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'centro') { // Asumiendo 'centro' es el rol de admin
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $totalOfertas = Oferta::count();
        $totalDemandantes = Demandante::count();
        $totalEmpresas = Empresa::count();
        
        // Ofertas adjudicadas: aquellas que tienen algún demandante con adjudicada=1
        $ofertasAdjudicadas = Oferta::whereHas('demandantes', function($q) {
            $q->where('adjudicada', 1);
        })->count();

        // Gráfico: Ofertas por mes (últimos 12 meses)
        $ofertasPorMes = Oferta::select(
            DB::raw('count(id) as total'),
            DB::raw("DATE_FORMAT(fecha_publicacion, '%Y-%m') as mes")
        )
        ->groupBy('mes')
        ->orderBy('mes', 'desc')
        ->limit(12)
        ->get();

        return response()->json([
            'total_ofertas' => $totalOfertas,
            'ofertas_adjudicadas' => $ofertasAdjudicadas,
            'total_demandantes' => $totalDemandantes,
            'total_empresas' => $totalEmpresas,
            'ofertas_por_mes' => $ofertasPorMes
        ]);
    }
}
