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
            'id_empresa' => $usuario->id,
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

    public function actualizar(Request $request)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'cif' => 'required|string|size:9',
            'nombre' => 'required|string|max:45',
            'localidad' => 'required|string|max:45',
            'telefono' => 'required|string|size:9'
        ]);

        $empresa = Empresa::where('id_empresa', $usuario->id)->first();

        $empresa->update([
            'cif' => $request->cif,
            'nombre' => $request->nombre,
            'localidad' => $request->localidad,
            'telefono' => $request->telefono
        ]);

        return response()->json([
            'message' => 'Empresa actualizada con éxito',
            'empresa' => $empresa
        ]);
    }

    public function obtenerJWT()
    {
        $usuario = Usuario::with('empresa')->find(JWTAuth::parseToken()->authenticate()->id);

        return response()->json($usuario->empresa);
    }

    public function obtener()
    {
        try {
            $usuario = JWTAuth::parseToken()->authenticate();
        } catch (\Exception) {
            return Empresa::where('validado', true)->get();
        }

        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol === 'centro') {
            $empresas = Empresa::orderBy('validado', 'asc')->get();
        } else {
            $empresas = Empresa::where('validado', true)->get();
        }

        return response()->json($empresas);
    }

    public function validar(Request $request, $id) {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'centro') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = Empresa::where('id_empresa', $id)->first();
        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        $empresa->validado = true;
        $empresa->save();

        return response()->json([
            'message' => 'Empresa validada con éxito',
            'empresa' => $empresa
        ]);
    }

    public function eliminar($id)
    {
        $usuario = JWTAuth::parseToken()->authenticate();

        if ($usuario->rol !== 'centro') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $empresa = Empresa::where('id_empresa', $id)->first();
        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        $empresa->delete();

        return response()->json([
            'message' => 'Empresa eliminada con éxito'
        ]);
    }
}
