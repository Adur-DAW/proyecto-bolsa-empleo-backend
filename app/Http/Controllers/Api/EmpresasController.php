<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class EmpresasController extends Controller
{
    public function registrar(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'cif' => 'required|string|size:9|unique:empresas,cif',
            'nombre' => 'required|string|max:45',
            'localidad' => 'required|string|max:45',
            'telefono' => 'required|string|size:9'
        ]);

        $empresa = Empresa::create([
            'id_usuario' => $usuario->id,
            'cif' => $request->cif,
            'nombre' => $request->nombre,
            'localidad' => $request->localidad,
            'telefono' => $request->telefono,
            'validado' => false
        ]);

        return response()->json([
            'message' => 'Empresa registrada con éxito',
            'empresa' => $empresa
        ], 201);
    }

    public function obtenerJWT()
    {
        $usuario = Usuario::with('empresa')->find(JWTAuth::parseToken()->authenticate()->id);

        return response()->json($usuario->empresa);
    }

    public function obtener()
    {
        $empresas = Empresa::all();

        return response()->json($empresas);
    }
}
