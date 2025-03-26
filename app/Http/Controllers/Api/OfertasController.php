<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oferta;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

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
            'tipo_contrato' => 'required|string|max:45',
            'horario' => 'required|string|max:45',
            'obs' => 'nullable|string|max:255',
            'abierta' => 'required|boolean',
            'fecha_cierre' => 'required|date'
        ]);

        $oferta = Oferta::create([
            'nombre' => $request->nombre,
            'fecha_publicacion' => $request->fecha_publicacion,
            'numero_puestos' => $request->numero_puestos,
            'tipo_contrato' => $request->tipo_contrato,
            'horario' => $request->horario,
            'obs' => $request->obs ?? '',
            'abierta' => $request->abierta,
            'fecha_cierre' => $request->fecha_cierre,
            'id_empresa' => $usuario->id
        ]);

        return response()->json([
            'message' => 'Oferta registrada con éxito',
            'oferta' => $oferta
        ], 201);
    }

    public function obtener()
    {
        try {
            $usuario = JWTAuth::parseToken()->authenticate();
        } catch (\Exception) {
            return response()->json(Oferta::with('empresa')->get());
        }

        $ofertas = Oferta::with('empresa')->get();

        $ofertas->each(function ($oferta) {
            $oferta->demandantes_inscritos = $oferta->demandantes->count();
        });

        if ($usuario->rol === 'demandante') {
            $ofertas->each(function ($oferta) use ($usuario) {
                $oferta->inscrito = $oferta->demandantes->contains($usuario->demandante->id_demandante);
            });
        }

        return response()->json($ofertas);
    }

    public function obtenerPorId($id)
    {
        $oferta = Oferta::with('empresa')->find($id);
        $oferta->demandantes_inscritos = $oferta->demandantes->count();

        if (!$oferta) {
            return response()->json(['error' => 'Oferta no encontrada'], 404);
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
            'tipo_contrato' => 'required|string|max:45',
            'horario' => 'required|string|max:45',
            'obs' => 'nullable|string|max:255',
            'abierta' => 'required|boolean',
            'fecha_cierre' => 'required|date'
        ]);

        $oferta->update([
            'nombre' => $request->nombre,
            'fecha_publicacion' => $request->fecha_publicacion,
            'numero_puestos' => $request->numero_puestos,
            'tipo_contrato' => $request->tipo_contrato,
            'horario' => $request->horario,
            'obs' => $request->obs ?? '',
            'abierta' => $request->abierta,
            'fecha_cierre' => $request->fecha_cierre
        ]);

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
