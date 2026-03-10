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

use Illuminate\Support\Facades\Log;

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
            'id_tipo_contrato' => 'required|exists:tipos_contrato,id',
            'horario' => 'required|string|max:45',
            'dias_descanso' => 'nullable|string|max:100',
            'obs' => 'nullable|string|max:255',
            'abierta' => 'required|boolean',
            'fecha_cierre' => 'required|date',
            'readme' => 'nullable|string'
        ]);

        $oferta = Oferta::create([
            'nombre' => $request->nombre,
            'fecha_publicacion' => $request->fecha_publicacion,
            'numero_puestos' => $request->numero_puestos,
            'id_tipo_contrato' => $request->id_tipo_contrato,
            'horario' => $request->horario,
            'dias_descanso' => $request->dias_descanso,
            'obs' => $request->obs ?? '',
            'abierta' => $request->abierta,
            'fecha_cierre' => $request->fecha_cierre,
            'id_empresa' => $usuario->id,
            'readme' => $request->readme
        ]);


        try {
            $empresa = $usuario->empresa;
            if ($empresa && $empresa->id_familia_profesional) {
                $demandantes = Demandante::where('id_familia_profesional', $empresa->id_familia_profesional)->get();
                foreach ($demandantes as $demandante) {
                    if ($demandante->email) {
                        Mail::to($demandante->email)->send(new NuevaOfertaMail($oferta));
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error enviando email a demandantes: ' . $e->getMessage());
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
        }

        $query = Oferta::with('empresa', 'tipoContrato');

        $this->aplicarFiltros($query, $request);

        $limite = $request->input('limite', 20);
        $ofertas = $query->paginate($limite);

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
        }

        return response()->json($oferta);
    }

    public function obtenerPorEmpresaJWT(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $query = Oferta::with('empresa')->where('id_empresa', $usuario->id);

        $this->aplicarFiltros($query, $request);

        $limite = $request->input('limite', 20);
        $ofertas = $query->paginate($limite);

        $ofertas->getCollection()->transform(function ($oferta) {
            $oferta->demandantes_inscritos = $oferta->demandantes->count();
            return $oferta;
        });

        return response()->json($ofertas);
    }

    public function obtenerPorTitulosDemandanteJWT(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'demandante') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $titulosDemandante = $usuario->demandante->titulos->pluck('id_titulo');

        $query = Oferta::whereHas('titulos', function ($query) use ($titulosDemandante) {
            $query->whereIn('id_titulo', $titulosDemandante);
        })->with('empresa', 'titulos');

        if ($request->has('inscrito')) {
            $inscrito = $request->input('inscrito');
            if ($inscrito === 'inscritas') {
                $query->whereHas('demandantes', function ($q) use ($usuario) {
                    $q->where('demandantes.id_demandante', $usuario->demandante->id_demandante);
                });
            } elseif ($inscrito === 'no_inscritas') {
                $query->whereDoesntHave('demandantes', function ($q) use ($usuario) {
                    $q->where('demandantes.id_demandante', $usuario->demandante->id_demandante);
                });
            }
        }

        $this->aplicarFiltros($query, $request);

        $limite = $request->input('limite', 20);
        $ofertas = $query->paginate($limite);

        $ofertas->getCollection()->transform(function ($oferta) use ($usuario) {
            $oferta->inscrito = $oferta->demandantes->contains($usuario->demandante->id_demandante);
            $oferta->demandantes_inscritos = $oferta->demandantes->count();
            return $oferta;
        });

        return response()->json($ofertas);
    }

    private function aplicarFiltros($query, Request $request)
    {
        if ($request->has('id_empresa')) {
            $query->where('id_empresa', $request->input('id_empresa'));
        }

        if ($request->has('estado')) {
            $estado = $request->input('estado');
            if ($estado === 'activas') {
                $query->where('abierta', true);
            } elseif ($estado === 'finalizadas') {
                $query->where('abierta', false);
            }
        }

        if ($request->has('id_familia')) {
            $id_familia = (int)$request->input('id_familia');
            $query->whereHas('empresa', function ($q) use ($id_familia) {
                $q->where('id_familia_profesional', $id_familia);
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('obs', 'like', "%{$search}%")
                    ->orWhereHas('empresa', function ($qEmpresa) use ($search) {
                        $qEmpresa->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('ordenar_por')) {
            $orden = $request->input('ordenar_por');
            $partes_orden = explode('.', $orden);
            $field = $partes_orden[0];
            $direction = $partes_orden[1] ?? 'desc';

            if (in_array($field, ['fecha_publicacion', 'fecha_cierre', 'numero_puestos', 'nombre'])) {
                $query->orderBy($field, $direction);
            }
        } else {
            $query->orderBy('fecha_publicacion', 'desc');
        }
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
            'id_tipo_contrato' => 'required|exists:tipos_contrato,id',
            'horario' => 'required|string|max:45',
            'dias_descanso' => 'nullable|string|max:100',
            'obs' => 'nullable|string|max:255',
            'abierta' => 'required|boolean',
            'fecha_cierre' => 'required|date',
            'readme' => 'nullable|string'
        ]);

        $estabaAbierta = $oferta->abierta;

        $oferta->update([
            'nombre' => $request->nombre,
            'fecha_publicacion' => $request->fecha_publicacion,
            'numero_puestos' => $request->numero_puestos,
            'id_tipo_contrato' => $request->id_tipo_contrato,
            'horario' => $request->horario,
            'dias_descanso' => $request->dias_descanso,
            'obs' => $request->obs ?? '',
            'abierta' => $request->abierta,
            'fecha_cierre' => $request->fecha_cierre,
            'readme' => $request->readme
        ]);

        try {
            if ($estabaAbierta && !$oferta->abierta) {
                foreach ($oferta->demandantes as $inscrito) {
                    if ($inscrito->email) {
                        Mail::to($inscrito->email)->send(new OfertaCerradaMail($oferta, 'cerrada'));
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error enviando email de cierre de oferta: ' . $e->getMessage());
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
