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

        $familia = $peticion->query('familia'); // Nuevo filtro

        // --- Estructura Base de Queries con Filtros Globales ---
        $baseOfertas = Oferta::query();
        $baseDemandantes = Demandante::query();
        $baseEmpresas = Empresa::query();

        // Filtro Fechas
        if ($fechaInicio && $fechaFin) {
            $baseOfertas->whereBetween('fecha_publicacion', [$fechaInicio, $fechaFin]);
            $baseDemandantes->whereBetween('created_at', [$fechaInicio, $fechaFin]);
            $baseEmpresas->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }

        // Filtro Familia (Solo afecta a Ofertas y Demandantes, Empresas no suelen tener familia directa asociada en este contexto global a menos que se fuerce, aplicamos a ofertas y demandantes)
        if ($familia) {
             // Ofertas: filtrar por relación con títulos requeridos o campo si lo hubiera.
             // Asumimos que la oferta pertenece a familias de sus títulos requeridos.
             $baseOfertas->whereHas('titulos', function ($q) use ($familia) {
                 $q->where('familia_profesional', $familia);
             });
             $baseDemandantes->where('familia_profesional', $familia);
        }

        // --- 1. Totales ---
        $ofertasAdjudicadasQuery = clone $baseOfertas;
        $countOfertasAdjudicadas = $ofertasAdjudicadasQuery->whereHas('demandantes', function($q) {
             $q->where('adjudicada', 1);
        })->count();

        $datosTotales = [
            'ofertas' => (clone $baseOfertas)->count(),
            'ofertas_adjudicadas' => $countOfertasAdjudicadas,
            'demandantes' => (clone $baseDemandantes)->count(),
            'empresas' => (clone $baseEmpresas)->count(), // Empresas generalmente no se filtran por familia
        ];

        // --- 2. Evolución Registros ---
        $registros = $this->obtenerEvolucionRegistros($fechaInicio, $fechaFin, $familia, $peticion->query('agrupacion', 'diario'));

        // --- 3. Evolución Ofertas ---
        $ofertasEvolucion = $this->obtenerEvolucionOfertas($fechaInicio, $fechaFin, $familia, $peticion->query('agrupacion', 'diario'));

        // --- 4. Top Familias ---
        $topFamilias = $this->obtenerTopFamilias($fechaInicio, $fechaFin);

        // --- 5. Distribución Geográfica ---
        $localidades = $this->obtenerDistribucionGeografica($fechaInicio, $fechaFin, $familia);

        // --- METRICAS NUEVAS ---

        // 6. Tiempo Medio de Resolución (Días)
        // Avg(Fecha Adjudicación - Fecha Publicación) de ofertas adjudicadas
        $tiempoResolucion = $this->obtenerTiempoMedioResolucion($fechaInicio, $fechaFin, $familia);

        // 7. Top Empresas (Más activas publicando ofertas)
        $topEmpresasList = $this->obtenerTopEmpresas($fechaInicio, $fechaFin, $familia);

        // 8. Funnel (Candidaturas vs Adjudicadas)
        $funnel = $this->obtenerFunnel($fechaInicio, $fechaFin, $familia);

        // 9. Estado Ofertas (Abiertas vs Cerradas vs Adjudicadas)
        $estadoOfertas = $this->obtenerEstadoOfertas($fechaInicio, $fechaFin, $familia);

        // 10. Top Títulos (Más solicitados en ofertas)
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
            'top_titulos' => $topTitulos // Nueva métrica
        ]);
    }

    // --- Helpers de Métricas ---

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
            // Filtrar lógica familia
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
        // Cerradas son Total - Abiertas (simplificación, o fecha_cierre < now)
        
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
            'cerradas_sin_adjudicar' => ($res->total - $res->abiertas - $res->adjudicadas)
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

    // --- Métodos Existentes Adaptados (Evolución, etc) ---

    private function obtenerEvolucionRegistros($inicio, $fin, $familia = null, $agrupacion = 'diario')
    {
        // Determinar campo de agrupación
        $groupByD = "DATE_FORMAT(created_at, '%Y-%m-%d')"; // Default diario
        $groupByE = "DATE_FORMAT(created_at, '%Y-%m-%d')";
        $label = "periodo";

        if ($agrupacion === 'mensual') {
            $groupByD = "DATE_FORMAT(created_at, '%Y-%m')";
            $groupByE = "DATE_FORMAT(created_at, '%Y-%m')";
        } elseif ($agrupacion === 'familia') {
            $groupByD = "familia_profesional";
            $groupByE = "'Sin Familia'"; // Empresas no tienen familia directa en este contexto simple
        } elseif ($agrupacion === 'localidad') {
            $groupByD = "'Desconocido'"; // Demandantes no tienen localidad simple
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

        // Merge keys
        $allKeys = $demandantes->keys()->merge($empresas->keys())->unique()->sort()->values();

        $resultado = [];
        foreach ($allKeys as $key) {
            if ($key === 'Sin Familia' || $key === 'Desconocido') continue; // Limpiar datos dummy
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
             // Necesitamos join para agrupar ofertas por familia
             // Esto es complejo porque una oferta tiene titulos y titulos tienen familia.
             // Asumimos titulos.familia_profesional.
             // Si agrupamos por familia, necesitamos el join en la base.
             // Como este helper usa Oferta::select, hacemos el join abajo.
             $groupBy = "t.familia_profesional";
        } elseif ($agrupacion === 'localidad') {
             $groupBy = "e.localidad";
        }

        // Base Query
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
             // Si ya hicimos join arriba, no repetirlo o controlar alias
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
        // Top familias ignora el filtro de familia (para ver el contexto global o podríamos filtrar, pero mejor ver el global)
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
