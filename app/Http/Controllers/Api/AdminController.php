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
    public function obtenerEstadisticas(Request $peticion)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'centro') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $fechaInicio = $peticion->query('fechaInicio');
        $fechaFin = $peticion->query('fechaFin');

        if ($fechaFin) {
            $fechaFin = $fechaFin . ' 23:59:59';
        }

        $familia = $peticion->query('familia');


        $datosTotales = $this->obtenerTotalesYComparativa($fechaInicio, $fechaFin, $familia);


        $registros = $this->obtenerEvolucionRegistros($fechaInicio, $fechaFin, $familia, $peticion->query('agrupacion', 'diario'));


        $ofertasEvolucion = $this->obtenerEvolucionOfertas($fechaInicio, $fechaFin, $familia, $peticion->query('agrupacion', 'diario'));


        $topFamilias = $this->obtenerTopFamilias($fechaInicio, $fechaFin);


        $localidades = $this->obtenerDistribucionGeografica($fechaInicio, $fechaFin, $familia);


        $tiempoResolucion = $this->obtenerTiempoMedioResolucion($fechaInicio, $fechaFin, $familia);
        $topEmpresasList = $this->obtenerTopEmpresas($fechaInicio, $fechaFin, $familia);
        $funnel = $this->obtenerFunnel($fechaInicio, $fechaFin, $familia);
        $estadoOfertas = $this->obtenerEstadoOfertas($fechaInicio, $fechaFin, $familia);
        $topTitulos = $this->obtenerTopTitulos($fechaInicio, $fechaFin, $familia);

        return response()->json([
            'totales' => $datosTotales,
            'registros' => $registros,
            'ofertas' => $ofertasEvolucion,
            'top_familias' => $topFamilias,
            'localidades' => $localidades,
            'tiempo_resolucion' => $tiempoResolucion,
            'top_empresas' => $topEmpresasList,
            'funnel' => $funnel,
            'estado_ofertas' => $estadoOfertas,
            'top_titulos' => $topTitulos
        ]);
    }



    private function obtenerTotalesYComparativa($inicio, $fin, $familia)
    {

        $actual = $this->calcularTotalesPeriodo($inicio, $fin, $familia);


        if ($inicio && $fin) {
            $start = \Carbon\Carbon::parse($inicio);
            $end = \Carbon\Carbon::parse($fin);
            $dias = $start->diffInDays($end) + 1;

            $prevInicio = $start->copy()->subDays($dias);
            $prevFin = $end->copy()->subDays($dias);

            $anterior = $this->calcularTotalesPeriodo($prevInicio, $prevFin, $familia);
        } else {
            $anterior = $actual;
        }


        $variacion = [
            'ofertas' => $this->calcPct($actual['ofertas'], $anterior['ofertas']),
            'ofertas_adjudicadas' => $this->calcPct($actual['ofertas_adjudicadas'], $anterior['ofertas_adjudicadas']),
            'demandantes' => $this->calcPct($actual['demandantes'], $anterior['demandantes']),
            'empresas' => $this->calcPct($actual['empresas'], $anterior['empresas']),
        ];

        return array_merge($actual, ['variacion' => $variacion]);
    }

    private function calcularTotalesPeriodo($inicio, $fin, $familia) {
        $baseOfertas = Oferta::query();
        $baseDemandantes = Demandante::query();
        $baseEmpresas = Empresa::query();

        if ($inicio && $fin) {
            $baseOfertas->whereBetween('fecha_publicacion', [$inicio, $fin]);
            $baseDemandantes->whereBetween('created_at', [$inicio, $fin]);
            $baseEmpresas->whereBetween('created_at', [$inicio, $fin]);
        }

        if ($familia) {
            $baseOfertas->whereHas('titulos', function ($q) use ($familia) {
                $q->where('familia_profesional', $familia);
            });
            $baseDemandantes->where('familia_profesional', $familia);
        }

        $adjudicadas = (clone $baseOfertas)->whereHas('demandantes', function($q) {
             $q->where('adjudicada', 1);
        })->count();

        return [
            'ofertas' => $baseOfertas->count(),
            'ofertas_adjudicadas' => $adjudicadas,
            'demandantes' => $baseDemandantes->count(),
            'empresas' => $baseEmpresas->count(),
        ];
    }

    private function calcPct($nuevo, $viejo) {
        if ($viejo == 0) return $nuevo > 0 ? 100 : 0;
        return round((($nuevo - $viejo) / $viejo) * 100, 1);
    }



    private function obtenerTiempoMedioResolucion($inicio, $fin, $familia) {
        $query = DB::table('ofertas')
            ->join('demandantes_oferta', 'ofertas.id', '=', 'demandantes_oferta.id_oferta')
            ->where('demandantes_oferta.adjudicada', 1)
            ->select(DB::raw('AVG(DATEDIFF(demandantes_oferta.fecha, ofertas.fecha_publicacion)) as dias_medio'));

        if ($inicio && $fin) $query->whereBetween('ofertas.fecha_publicacion', [$inicio, $fin]);

        if ($familia) {
             $query->join('titulos_oferta', 'ofertas.id', '=', 'titulos_oferta.id_oferta')
                   ->join('titulos', 'titulos_oferta.id_titulo', '=', 'titulos.id')
                   ->where('titulos.familia_profesional', $familia);
        }

        $res = $query->first();
        return $res ? round($res->dias_medio, 1) : 0;
    }

    private function obtenerTopEmpresas($inicio, $fin, $familia) {
        $query = Oferta::join('empresas', 'ofertas.id_empresa', '=', 'empresas.id_empresa')
            ->select('empresas.nombre', DB::raw('count(ofertas.id) as total_ofertas'))
            ->groupBy('empresas.id_empresa', 'empresas.nombre')
            ->orderByDesc('total_ofertas')
            ->limit(5);

        if ($inicio && $fin) $query->whereBetween('ofertas.fecha_publicacion', [$inicio, $fin]);

        if ($familia) {
            $query->join('titulos_oferta', 'ofertas.id', '=', 'titulos_oferta.id_oferta')
                  ->join('titulos', 'titulos_oferta.id_titulo', '=', 'titulos.id')
                  ->where('titulos.familia_profesional', $familia);
        }

        return $query->get();
    }

    private function obtenerFunnel($inicio, $fin, $familia) {
        $queryInscripciones = DB::table('demandantes_oferta')
            ->join('ofertas', 'demandantes_oferta.id_oferta', '=', 'ofertas.id');

        $queryAdjudicadas = DB::table('demandantes_oferta')
            ->join('ofertas', 'demandantes_oferta.id_oferta', '=', 'ofertas.id')
            ->where('demandantes_oferta.adjudicada', 1);

        if ($inicio && $fin) {
            $queryInscripciones->whereBetween('ofertas.fecha_publicacion', [$inicio, $fin]);
            $queryAdjudicadas->whereBetween('ofertas.fecha_publicacion', [$inicio, $fin]);
        }

        if ($familia) {
             $filter = function($q) use ($familia) {
                $q->join('titulos_oferta', 'ofertas.id', '=', 'titulos_oferta.id_oferta')
                  ->join('titulos', 'titulos_oferta.id_titulo', '=', 'titulos.id')
                  ->where('titulos.familia_profesional', $familia);
             };
             $filter($queryInscripciones);
             $filter($queryAdjudicadas);
        }

        return [
            'inscritos' => $queryInscripciones->count(),
            'adjudicados' => $queryAdjudicadas->count()
        ];
    }

    private function obtenerEstadoOfertas($inicio, $fin, $familia) {
        $query = Oferta::select(
            DB::raw('count(*) as total'),
            DB::raw('SUM(CASE WHEN abierta = 1 THEN 1 ELSE 0 END) as abiertas'),
            DB::raw("SUM(CASE WHEN (select count(*) from demandantes_oferta d_o where d_o.id_oferta = ofertas.id and d_o.adjudicada = 1) > 0 THEN 1 ELSE 0 END) as adjudicadas")
        );

        if ($inicio && $fin) $query->whereBetween('fecha_publicacion', [$inicio, $fin]);

        if ($familia) {
             $query->join('titulos_oferta', 'ofertas.id', '=', 'titulos_oferta.id_oferta')
                   ->join('titulos', 'titulos_oferta.id_titulo', '=', 'titulos.id')
                   ->where('titulos.familia_profesional', $familia);
        }

        $res = $query->first();
        return [
            'abiertas' => $res->abiertas ?? 0,
            'adjudicadas' => $res->adjudicadas ?? 0,
            'cerradas_sin_adjudicar' => ($res->total - ($res->abiertas ?? 0) - ($res->adjudicadas ?? 0))
        ];
    }

    private function obtenerTopTitulos($inicio, $fin, $familia) {
        $query = DB::table('titulos_oferta')
            ->join('titulos', 'titulos_oferta.id_titulo', '=', 'titulos.id')
            ->join('ofertas', 'titulos_oferta.id_oferta', '=', 'ofertas.id')
            ->select('titulos.nombre', 'titulos.familia_profesional', DB::raw('count(titulos_oferta.id_oferta) as total_ofertas'))
            ->groupBy('titulos.id', 'titulos.nombre', 'titulos.familia_profesional')
            ->orderByDesc('total_ofertas')
            ->limit(8);

        if ($inicio && $fin) $query->whereBetween('ofertas.fecha_publicacion', [$inicio, $fin]);

        if ($familia) {
            $query->where('titulos.familia_profesional', $familia);
        }

        return $query->get();
    }



    private function obtenerEvolucionRegistros($inicio, $fin, $familia = null, $agrupacion = 'diario')
    {
        $groupByD = "DATE_FORMAT(created_at, '%Y-%m-%d')";
        $groupByE = "DATE_FORMAT(created_at, '%Y-%m-%d')";

        if ($agrupacion === 'mensual') {
            $groupByD = "DATE_FORMAT(created_at, '%Y-%m')";
            $groupByE = "DATE_FORMAT(created_at, '%Y-%m')";
        } elseif ($agrupacion === 'familia') {
            $groupByD = "familia_profesional";
            $groupByE = "'Sin Familia'";
        } elseif ($agrupacion === 'localidad') {
            $groupByD = "'Desconocido'";
            $groupByE = "localidad";
        }

        $queryD = Demandante::select(DB::raw("$groupByD as periodo"), DB::raw('count(*) as total'))
                    ->groupBy('periodo');

        $queryE = Empresa::select(DB::raw("$groupByE as periodo"), DB::raw('count(*) as total'))
                    ->groupBy('periodo');

        if ($inicio && $fin) {
            $queryD->whereBetween('created_at', [$inicio, $fin]);
            $queryE->whereBetween('created_at', [$inicio, $fin]);
        }

        if ($familia) {
            $queryD->where('familia_profesional', $familia);
        }

        $demandantes = $queryD->get()->keyBy('periodo');
        $empresas = ($agrupacion === 'familia' || $agrupacion === 'localidad' && $agrupacion !== 'localidad') ? collect([]) : $queryE->get()->keyBy('periodo');

        $allKeys = $demandantes->keys()->merge($empresas->keys())->unique()->sort()->values();

        $resultado = [];
        foreach ($allKeys as $key) {
            if (empty($key) || $key === 'Sin Familia' || $key === 'Desconocido' || $key === 'null') continue;
            $resultado[] = [
                'periodo' => $key,
                'demandantes' => $demandantes[$key]->total ?? 0,
                'empresas' => $empresas[$key]->total ?? 0,
            ];
        }

        return $resultado;
    }

    private function obtenerEvolucionOfertas($inicio, $fin, $familia = null, $agrupacion = 'diario')
    {
        $groupBy = "DATE_FORMAT(fecha_publicacion, '%Y-%m-%d')";

        if ($agrupacion === 'mensual') {
            $groupBy = "DATE_FORMAT(fecha_publicacion, '%Y-%m')";
        } elseif ($agrupacion === 'familia') {
             $groupBy = "t.familia_profesional";
        } elseif ($agrupacion === 'localidad') {
             $groupBy = "e.localidad";
        }

        $query = DB::table('ofertas');

        if ($agrupacion === 'familia') {
            $query->join('titulos_oferta as to', 'ofertas.id', '=', 'to.id_oferta')
                  ->join('titulos as t', 'to.id_titulo', '=', 't.id');
        } elseif ($agrupacion === 'localidad') {
            $query->join('empresas as e', 'ofertas.id_empresa', '=', 'e.id_empresa');
        }

        $query->select(
            DB::raw("$groupBy as periodo"),
            DB::raw('count(DISTINCT ofertas.id) as total_publicadas'),
             DB::raw('sum(case when (select count(*) from demandantes_oferta d_o where d_o.id_oferta = ofertas.id and d_o.adjudicada = 1) > 0 then 1 else 0 end) as total_adjudicadas')
        )->groupBy('periodo');

        if ($inicio && $fin) {
            $query->whereBetween('ofertas.fecha_publicacion', [$inicio, $fin]);
        }

        if ($familia) {
             if ($agrupacion !== 'familia') {
                $query->join('titulos_oferta as to2', 'ofertas.id', '=', 'to2.id_oferta')
                      ->join('titulos as t2', 'to2.id_titulo', '=', 't2.id')
                      ->where('t2.familia_profesional', $familia);
             } else {
                 $query->where('t.familia_profesional', $familia);
             }
        }

        return $query->get();
    }

    private function obtenerTopFamilias($inicio, $fin)
    {
        $query = Demandante::select('familia_profesional', DB::raw('count(*) as total'))
            ->whereNotNull('familia_profesional')
            ->groupBy('familia_profesional')
            ->orderByDesc('total')
            ->limit(10);

        if ($inicio && $fin) {
            $query->whereBetween('created_at', [$inicio, $fin]);
        }

        return $query->get();
    }

    private function obtenerDistribucionGeografica($inicio, $fin, $familia = null)
    {
        $query = DB::table('ofertas')
            ->join('empresas', 'ofertas.id_empresa', '=', 'empresas.id_empresa')
            ->select('empresas.localidad', DB::raw('count(ofertas.id) as total'))
            ->groupBy('empresas.localidad')
            ->orderByDesc('total')
            ->limit(10);

        if ($inicio && $fin) {
            $query->whereBetween('ofertas.fecha_publicacion', [$inicio, $fin]);
        }

        if ($familia) {
             $query->join('titulos_oferta', 'ofertas.id', '=', 'titulos_oferta.id_oferta')
                   ->join('titulos', 'titulos_oferta.id_titulo', '=', 'titulos.id')
                   ->where('titulos.familia_profesional', $familia);
        }

        return $query->get();
    }
}
