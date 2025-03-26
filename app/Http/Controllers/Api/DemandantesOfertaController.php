<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demandante;
use App\Models\Titulo;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class DemandantesOfertaController extends Controller
{
    public function registrarJWT(Request $request, $id) {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'demandante') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $demandante = Demandante::find($usuario->id);
        if (!$demandante) {
            return response()->json(['error' => 'No se ha encontrado el demandante'], 404);
        }

        $inscrito = $demandante->ofertas()->where('id_oferta', $id)->first();
        if ($inscrito) {
            return response()->json(['error' => 'Ya está inscrito en la oferta'], 400);
        }

        $demandante->ofertas()->attach($id, [
            'adjudicada' => false,
            'fecha' => now()
        ]);

        return response()->json([
            'message' => 'Se ha inscrito en la oferta correctamente'
        ], 201);
    }

    public function obtenerOfertasJWT()
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'demandante') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $ofertas = $usuario->demandante->ofertas ?? [];

        return response()->json($ofertas);
    }

    public function eliminarJWT($id_oferta)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'demandante') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $usuario->demandante->ofertas()->detach($id_oferta);

        return response()->json([
            'message' => 'Se ha desinscrito de la oferta correctamente'
        ]);
    }

    public function registrarDemandanteYAdjudicar(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'id_demandante' => 'required|int',
            'id_oferta' => 'required|int',
        ]);

        $demandante = Demandante::find($request->id_demandante);
        if (!$demandante) {
            return response()->json(['error' => 'No se ha encontrado el demandante'], 404);
        }

        $demandante->ofertas()->attach($request->id_oferta, [
            'adjudicada' => true,
            'fecha' => $request->fecha,
        ]);

        return response()->json([
            'message' => 'Se ha adjudicado a la oferta correctamente'
        ], 201);
    }

    public function obtenerDemandantesPorIdOferta($id_oferta)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $demandantes = Demandante::whereHas('ofertas', function ($query) use ($id_oferta) {
            $query->where('id_oferta', $id_oferta);
        })->get();

        return response()->json($demandantes);
    }

    public function obtenerDemandantesContieneTitulacionPorIdOferta($id_oferta)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $titulos = Titulo::whereHas('ofertas', function ($query) use ($id_oferta) {
            $query->where('id_oferta', $id_oferta);
        })->get();

        $demandantes = Demandante::whereHas('titulos', function ($query) use ($titulos) {
            $query->whereIn('id_titulo', $titulos->pluck('id'));
        })->get();

        $demandantes = $demandantes->filter(function ($demandante) use ($id_oferta) {
            return !$demandante->ofertas->contains('id', $id_oferta);
        });

        return response()->json($demandantes);
    }

    public function adjudicarOferta($id_oferta, $id_demandante)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $demandante = Demandante::find($id_demandante);

        $demandante->ofertas()->updateExistingPivot($id_oferta, [
            'adjudicada' => true
        ]);

        return response()->json([
            'message' => 'Se ha adjudicado la oferta al demandante'
        ]);
    }

    public function eliminarDemandante($id_oferta, $id_demandante)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $demandante = Demandante::find($id_demandante);

        if (!$demandante) {
            return response()->json(['error' => 'No se ha encontrado el demandante'], 404);
        }

        $demandante->ofertas()->detach($id_oferta);

        return response()->json([
            'message' => 'Se ha desinscrito de la oferta correctamente'
        ]);
    }
}
