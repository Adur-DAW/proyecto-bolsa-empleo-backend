<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TituloOferta;
use App\Models\Oferta;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class TitulosOfertaController extends Controller
{
    public function registrar(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $oferta = Oferta::find($request->id_oferta);
        if (!$oferta) {
            return response()->json(['error' => 'Oferta no encontrada'], 404);
        }
        if ($oferta->id_empresa !== $usuario->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'id_titulo' => 'required|int',
            'id_oferta' => 'required|int',
        ]);

        $tituloExiste = TituloOferta::where('id_oferta', $request->id_oferta)->where('id_titulo', $request->id_titulo)->exists();
        if ($tituloExiste) {
            return response()->json(['error' => 'Titulo ya registrado'], 400);
        }

        $titulo = TituloOferta::create([
            'id_oferta' => $request->id_oferta,
            'id_titulo' => $request->id_titulo
        ]);

        return response()->json([
            'message' => 'Titulo registrado con éxito',
            'titulo' => $titulo
        ], 201);
    }

    public function obtenerTitulosPorIdOferta($id)
    {
        $titulosOferta = TituloOferta::where('id_oferta', $id)
            ->with('titulo')
            ->get();

        return response()->json($titulosOferta);
    }

    public function obtenerOfertasPorIdTitulo($id)
    {
        $titulosOferta = TituloOferta::where('id_titulo', $id)
            ->with('oferta')
            ->get();

        return response()->json($titulosOferta);
    }

    public function eliminar($id_oferta, $id_titulo)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'empresa') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $oferta = Oferta::find($id_oferta);
        if (!$oferta) {
            return response()->json(['error' => 'Oferta no encontrada'], 404);
        }
        if ($oferta->id_empresa !== $usuario->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $tituloOferta = TituloOferta::where('id_oferta', $id_oferta)
            ->where('id_titulo', $id_titulo)
            ->first();

        if (!$tituloOferta) {
            return response()->json(['error' => 'Titulo no encontrado'], 404);
        }

        $tituloOferta->delete();

        return response()->json([
            'message' => 'Titulo eliminado con éxito'
        ]);
    }
}
