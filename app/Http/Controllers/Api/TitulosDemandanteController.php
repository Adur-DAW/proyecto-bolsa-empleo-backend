<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TituloDemandante;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class TitulosDemandanteController extends Controller
{
    public function registrar(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'demandante') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'id_titulo' => 'required|int',
            'centro' => 'required|string|max:45',
            'año' => 'required|int',
            'cursando' => 'required|boolean'
        ]);

        $tituloExiste = TituloDemandante::where('id_demandante', $usuario->id)->where('id_titulo', $request->id_titulo)->exists();
        if ($tituloExiste) {
            return response()->json(['error' => 'Titulo ya registrado'], 400);
        }

        $titulo = TituloDemandante::create([
            'id_demandante' => $usuario->id,
            'id_titulo' => $request->id_titulo,
            'centro' => $request->centro,
            'año' => $request->año,
            'cursando' => $request->cursando
        ]);

        return response()->json([
            'message' => 'Titulo registrado con éxito',
            'titulo' => $titulo
        ], 201);
    }

    public function obtenerJWT()
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'demandante') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $titulosDemandante = TituloDemandante::where('id_demandante', $usuario->id)->with('titulo')->orderBy('año', 'desc')->get();

        return response()->json($titulosDemandante);
    }

    public function actualizar(Request $request, $id)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'demandante') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'centro' => 'required|string|max:45',
            'año' => 'required|int',
            'cursando' => 'required|boolean'
        ]);

        $tituloDemandante = TituloDemandante::where('id_demandante', $usuario->id)
            ->where('id_titulo', $id)
            ->first();

        if (!$tituloDemandante) {
            return response()->json(['error' => 'Titulo no encontrado'], 404);
        }

        $tituloDemandante->update([
            'centro' => $request->centro,
            'año' => $request->año,
            'cursando' => $request->cursando
        ]);

        return response()->json([
            'message' => 'Titulo actualizado con éxito',
            'tituloDemandante' => $tituloDemandante
        ]);
    }

    public function eliminar($id)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'demandante') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $tituloDemandante = TituloDemandante::where('id_demandante', $usuario->id)
            ->where('id_titulo', $id)
            ->first();

        if (!$tituloDemandante) {
            return response()->json(['error' => 'Titulo no encontrado'], 404);
        }

        $tituloDemandante->delete();

        return response()->json([
            'message' => 'Titulo eliminado con éxito'
        ]);
    }
}
