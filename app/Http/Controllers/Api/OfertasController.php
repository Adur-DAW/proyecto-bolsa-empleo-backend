<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oferta;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\Demandante;
use App\Mail\NuevaOfertaMail;
use App\Mail\OfertaCerradaMail;
use Illuminate\Support\Facades\Mail;

class OfertasController extends Controller
{
    public function registrar(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:45',
            'fecha_publicacion' => 'required|date',
            'numero_puestos' => 'required|int',
            'tipo_contrato_id' => 'required|exists:tipos_contrato,id',
            'horario' => 'required|string|max:45',
            'dias_descanso' => 'nullable|string|max:100',
            'obs' => 'nullable|string|max:255',
            'abierta' => 'required|boolean',
            'fecha_cierre' => 'required|date'
        ]);

        $oferta = Oferta::create([
            'nombre' => $request->nombre,
            'fecha_publicacion' => $request->fecha_publicacion,
            'numero_puestos' => $request->numero_puestos,
            'tipo_contrato_id' => $request->tipo_contrato_id,
            'horario' => $request->horario,
            'dias_descanso' => $request->dias_descanso,
            'obs' => $request->obs ?? '',
            'abierta' => $request->abierta,
            'fecha_cierre' => $request->fecha_cierre,
            'id_empresa' => $usuario->id
        ]);
        
        // Enviar email a demandantes de la misma familia profesional
        try {
            $empresa = $usuario->empresa;
            if ($empresa && $empresa->familia_profesional) {
                $demandantes = Demandante::where('familia_profesional', $empresa->familia_profesional)->get();
                foreach ($demandantes as $demandante) {
                    if ($demandante->email) {
                        Mail::to($demandante->email)->send(new NuevaOfertaMail($oferta));
                    }
                }
            }
        } catch (\Exception $e) {
            // No bloquear la respuesta si falla el mail
        }

        return response()->json([
            'message' => 'Oferta registrada con éxito',
            'oferta' => $oferta
        ], 201);
    }

    public function obtener(Request $request)
    {
        try {
            $usuario = JWTAuth::parseToken()->authenticate();
        } catch (\Exception) {
            // Permitido por middleware si es público, o ya autenticado
        }

        $query = Oferta::with('empresa', 'tipoContrato');

        if ($request->has('empresa_id')) {
            $query->where('id_empresa', $request->input('empresa_id'));
        }

        if ($request->has('estado')) {
            $estado = $request->input('estado');
            if ($estado === 'activas') {
                $query->where('abierta', true);
            } elseif ($estado === 'cerradas') {
                $query->where('abierta', false);
            }
            // 'todas' no añade filtro (pero respeta el filtro por empresa si existe)
        } else {
             // Comportamiento por defecto: si no especifica estado ni empresa, ¿mostramos solo abiertas en general?
             // En la lista general (sin empresa_id), solemos querer solo las abiertas, 
             // pero si pedimos historial de empresa, quizás queramos todas.
             
             // MANTENER COMPORTAMIENTO ORIGINAL: Si es lista general pública, solo abiertas.
             // Pero el código original traía TODAS en obtener()... espera, obtener() NO filtraba por abierta=true antes.
             // Revisando el código anterior: $ofertas = Oferta::with(...)->get(); -> Traía todas.
             // DashboardController traía solo abiertas.
             // OfertasLista.tsx mostraba "Activa: Si/No".
             // Así que por defecto TRAE TODAS. El frontend filtra o muestra el estado.
             // Vamos a mantener eso, salvo que se pida 'activas'.
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('obs', 'like', "%{$search}%")
                  ->orWhereHas('empresa', function($qEmpresa) use ($search) {
                      $qEmpresa->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        if ($request->has('sort_by')) {
             $sort = $request->input('sort_by');
             $parts = explode('.', $sort);
             $field = $parts[0];
             $direction = $parts[1] ?? 'desc'; // Default desc for dates

             if (in_array($field, ['fecha_publicacion', 'fecha_cierre', 'numero_puestos', 'nombre'])) {
                 $query->orderBy($field, $direction);
             }
        } else {
             $query->orderBy('fecha_publicacion', 'desc');
        }

        $limit = $request->input('limit', 20);
        $ofertas = $query->paginate($limit);

        $ofertas->getCollection()->transform(function ($oferta) use ($usuario) {
            $oferta->demandantes_inscritos = $oferta->demandantes->count();
            
            if (isset($usuario) && $usuario->rol === 'demandante') {
                $oferta->inscrito = $oferta->demandantes->contains($usuario->demandante->id_demandante);
            }
            
            return $oferta;
        });

        return response()->json($ofertas);
    }

    public function obtenerPorId($id)
    {
        $oferta = Oferta::with('empresa', 'tipoContrato')->find($id);
        
        if (!$oferta) {
            return response()->json(['error' => 'Oferta no encontrada'], 404);
        }
        
        $oferta->demandantes_inscritos = $oferta->demandantes->count();

        try {
            $usuario = JWTAuth::parseToken()->authenticate();

            if ($usuario->rol === 'demandante') {
                $oferta->inscrito = $oferta->demandantes->contains($usuario->demandante->id_demandante);
            }
        } catch (\Exception) {
            // Permitido por middleware
        }

        return response()->json($oferta);
    }

    public function obtenerPorEmpresaJWT()
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $ofertas = Oferta::with('empresa')->where('id_empresa', $usuario->id)->get();

        $ofertas->each(function ($oferta) {
            $oferta->demandantes_inscritos = $oferta->demandantes->count();
        });

        return response()->json($ofertas);
    }

    public function obtenerPorTitulosDemandanteJWT()
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'demandante') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Obtener títulos del demandante
        $titulosDemandante = $usuario->demandante->titulos->pluck('id_titulo');

        // Obtener ofertas que tengan alguno de los títulos del demandante
        $ofertas = Oferta::whereHas('titulos', function ($query) use ($titulosDemandante) {
            $query->whereIn('id_titulo', $titulosDemandante);
        })->with('empresa', 'titulos')->get();

        $ofertas->each(function ($oferta) use ($usuario) {
            $oferta->inscrito = $oferta->demandantes->contains($usuario->demandante->id_demandante);
        });

        $ofertas->each(function ($oferta) {
            $oferta->demandantes_inscritos = $oferta->demandantes->count();
        });

        return response()->json($ofertas);
    }


    public function actualizar(Request $request, int $id)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $oferta = Oferta::find($id);
        if (!$oferta) {
            return response()->json(['error' => 'Oferta no encontrada'], 404);
        }
        if ($usuario->id !== $oferta->id_empresa) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:45',
            'fecha_publicacion' => 'required|date',
            'numero_puestos' => 'required|int',
            'tipo_contrato_id' => 'required|exists:tipos_contrato,id',
            'horario' => 'required|string|max:45',
            'dias_descanso' => 'nullable|string|max:100',
            'obs' => 'nullable|string|max:255',
            'abierta' => 'required|boolean',
            'fecha_cierre' => 'required|date'
        ]);
        
        $estabaAbierta = $oferta->abierta;

        $oferta->update([
            'nombre' => $request->nombre,
            'fecha_publicacion' => $request->fecha_publicacion,
            'numero_puestos' => $request->numero_puestos,
            'tipo_contrato_id' => $request->tipo_contrato_id,
            'horario' => $request->horario,
            'dias_descanso' => $request->dias_descanso,
            'obs' => $request->obs ?? '',
            'abierta' => $request->abierta,
            'fecha_cierre' => $request->fecha_cierre
        ]);
        
        // Si se cierra la oferta, notificar a los inscritos
        try {
            if ($estabaAbierta && !$oferta->abierta) {
                foreach ($oferta->demandantes as $inscrito) {
                    if ($inscrito->email) {
                         Mail::to($inscrito->email)->send(new OfertaCerradaMail($oferta, 'cerrada'));
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignorar error de mail
        }

        return response()->json([
            'message' => 'Oferta actualizada con éxito',
            'oferta' => $oferta
        ]);
    }

    public function eliminar(int $id)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $oferta = Oferta::find($id);
        if (!$oferta) {
            return response()->json(['error' => 'Oferta no encontrada'], 404);
        }
        if ($usuario->id !== $oferta->id_empresa) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $oferta->delete();

        return response()->json(['message' => 'Oferta eliminada con éxito']);
    }
}
