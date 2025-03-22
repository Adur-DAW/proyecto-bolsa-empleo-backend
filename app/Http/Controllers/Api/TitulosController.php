<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Titulo;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class TitulosController extends Controller
{
    public function registrar(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'centro') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:45|unique:titulos,nombre'
        ]);

        $titulo = Titulo::create([
            'nombre' => $request->nombre
        ]);

        return response()->json([
            'message' => 'Titulo registrado con éxito',
            'titulo' => $titulo
        ], 201);
    }

    public function obtener()
    {
        $titulos = Titulo::all();

        return response()->json($titulos);
    }
}
