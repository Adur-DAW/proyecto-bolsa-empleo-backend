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

        $demandante = $usuario->load('demandante.titulos.titulo');

        if (!$demandante->demandante) {
            return response()->json(['error' => 'El usuario no tiene un demandante asociado'], 404);
        }

        return response()->json($demandante->demandante->titulos);
    }
}
