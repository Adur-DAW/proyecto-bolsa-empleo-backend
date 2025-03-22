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
            'fecha_cierre' => 'required|date',
            'id_empresa' => 'required|int'
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
            'id_empresa' => $request->id_empresa
        ]);

        return response()->json([
            'message' => 'Oferta registrada con éxito',
            'oferta' => $oferta
        ], 201);
    }

    public function obtener()
    {
        $ofertas = Oferta::all();

        return response()->json($ofertas);
    }
}
