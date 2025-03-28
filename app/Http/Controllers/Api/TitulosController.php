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

    public function obtenerExtra()
    {
        $titulos = Titulo::withCount('demandantes', 'ofertas')->get();

        return response()->json($titulos);
    }

    public function actualizar(Request $request, $id)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'centro') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $titulo = Titulo::find($id);
        if (!$titulo) {
            return response()->json(['error' => 'Titulo no encontrado'], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:45|unique:titulos,nombre,' . $titulo->id
        ]);

        $titulo->update([
            'nombre' => $request->nombre
        ]);

        return response()->json([
            'message' => 'Titulo actualizado con éxito',
            'titulo' => $titulo
        ]);
    }


    public function eliminar($id)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'centro') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $titulo = Titulo::find($id);
        if (!$titulo) {
            return response()->json(['error' => 'Titulo no encontrado'], 404);
        }

        if ($titulo->demandantes()->exists() || $titulo->ofertas()->exists()) {
            return response()->json(['error' => 'No se puede eliminar el título porque está asociado a demandantes u ofertas'], 400);
        }

        $titulo->delete();

        return response()->json([
            'message' => 'Titulo eliminado con éxito'
        ]);
    }
}
