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
            'telefono' => 'required|string|size:9',
            'familia_profesional_id' => 'nullable|exists:familias_profesionales,id'
        ]);

        $empresa = Empresa::create([
            'id_empresa' => $usuario->id,
            'cif' => $request->cif,
            'nombre' => $request->nombre,
            'localidad' => $request->localidad,
            'telefono' => $request->telefono,
            'validado' => false,
            'familia_profesional_id' => $request->familia_profesional_id
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
            'telefono' => 'required|string|size:9',
            'familia_profesional_id' => 'nullable|exists:familias_profesionales,id'
        ]);

        $empresa = Empresa::where('id_empresa', $usuario->id)->first();

        $empresa->update([
            'cif' => $request->cif,
            'nombre' => $request->nombre,
            'localidad' => $request->localidad,
            'telefono' => $request->telefono,
            'familia_profesional_id' => $request->familia_profesional_id
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

    public function show($id)
    {
        // Método público para ver detalles básicos
        $empresa = Empresa::with('familiaProfesional')->where('id_empresa', $id)->first();
        
        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        // Si la empresa no está validada, quizás no deberíamos mostrarla, a menos que sea admin
        if (!$empresa->validado) {
             // Chequear si es admin (centro) quien pide, o la propia empresa
             try {
                $user = JWTAuth::parseToken()->authenticate();
                if ($user->rol !== 'centro' && $user->id !== $empresa->id_empresa) {
                     return response()->json(['error' => 'Empresa no disponible'], 403);
                }
             } catch (\Exception) {
                 return response()->json(['error' => 'Empresa no disponible'], 403);
             }
        }

        return response()->json($empresa);
    }

    public function obtener(Request $request)
    {
        try {
            $usuario = JWTAuth::parseToken()->authenticate();
        } catch (\Exception) {
             // Si no hay token, solo mostrar validadas
             $query = Empresa::where('validado', true)->with('familiaProfesional');
             $this->aplicarFiltros($query, $request);
             return $query->get();
        }

        $usuario = JWTAuth::parseToken()->authenticate();
        $query = Empresa::with('familiaProfesional');

        if ($usuario->rol !== 'centro') {
            $query->where('validado', true);
        } else {
             $query->orderBy('validado', 'asc'); // Pendientes primero para admin
        }

        $this->aplicarFiltros($query, $request);

        return response()->json($query->get());
    }

    private function aplicarFiltros($query, Request $request) {
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhereHas('familiaProfesional', function($qF) use ($search) {
                      $qF->where('nombre', 'like', "%{$search}%");
                  });
            });
        }
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
