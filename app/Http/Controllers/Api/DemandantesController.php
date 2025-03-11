<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demandante;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Validation\Rule;

class DemandantesController extends Controller
{
    public function registrar(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        // if ($usuario->tipo_usuario !== 'demandante') {
        //     return response()->json(['error' => 'No autorizado'], 403);
        // }

        $request->validate([
            'dni' => 'required|string|size:9|unique:demandantes,dni',
            'nombre' => 'required|string|max:45',
            'apellido1' => 'required|string|max:45',
            'apellido2' => 'nullable|string|max:45',
            'telefono_movil' => 'required|string|size:9',
            'email' => ['required', 'string', 'email', 'max:45', Rule::unique('demandantes')->ignore($usuario->id, 'id_usuario')],
            'situacion' => 'required|integer|min:0|max:1',
        ]);

        $demandante = Demandante::create([
            'id_usuario' => $usuario->id,
            'dni' => $request->dni,
            'nombre' => $request->nombre,
            'apellido1' => $request->apellido1,
            'apellido2' => $request->apellido2 ?? '',
            'telefono_movil' => $request->telefono_movil,
            'email' => $request->email,
            'situacion' => $request->situacion,
        ]);

        return response()->json([
            'message' => 'Demandante registrado con éxito',
            'demandante' => $demandante
        ], 201);
    }

    public function obtener()
    {
        $usuario = Usuario::with('demandante')->find(JWTAuth::parseToken()->authenticate()->id);

        return response()->json($usuario);
    }
}
