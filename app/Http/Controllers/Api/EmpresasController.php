<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'id_familia_profesional' => 'nullable|exists:familias_profesionales,id'
        ]);

        $empresa = Empresa::create([
            'id_empresa' => $usuario->id,
            'cif' => $request->cif,
            'nombre' => $request->nombre,
            'localidad' => $request->localidad,
            'telefono' => $request->telefono,
            'validado' => false,
            'id_familia_profesional' => $request->id_familia_profesional
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
            'id_familia_profesional' => 'nullable|exists:familias_profesionales,id'
        ]);

        $empresa = Empresa::where('id_empresa', $usuario->id)->first();
        
        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        $file = $request->file('imagen');
        if ($file && $file->isValid()) {
            $url = $file->store('empresas', 'public');
        }

        $updateData = [
            'cif' => $request->cif,
            'nombre' => $request->nombre,
            'localidad' => $request->localidad,
            'telefono' => $request->telefono,
            'id_familia_profesional' => $request->id_familia_profesional
        ];

        if (isset($url)) {
            $updateData['imagen_url'] = $url;
        }

        $empresa->update($updateData);

        return response()->json([
            'message' => 'Empresa actualizada con éxito',
            'empresa' => $empresa
        ]);
    }

    public function obtenerJWT()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            $usuario = Usuario::with('empresa')->find($user->id);

            if (!$usuario->empresa) {
                return response()->json(null);
            }

            return response()->json($usuario->empresa);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {

        $empresa = Empresa::with('familiaProfesional')->where('id_empresa', $id)->first();

        if (!$empresa) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        if (!$empresa->validado) {
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
            $query = Empresa::where('validado', true)->with('familiaProfesional');
            $this->aplicarFiltros($query, $request);
            return $query->get();
        }

        $usuario = JWTAuth::parseToken()->authenticate();
        $query = Empresa::with('familiaProfesional')
            ->withCount('ofertas')
            ->withSum('ofertas as vacantes', 'numero_puestos');

        if ($usuario->rol !== 'centro') {
            $query->where('validado', true);
        }

        $this->aplicarFiltros($query, $request);

        if ($request->has('ordenar_por')) {
            $orden = $request->input('ordenar_por');

            $partes = explode('.', $orden);
            $field = $partes[0];
            $direccion = $partes[1] ?? 'asc';

            if (in_array($field, ['nombre', 'localidad', 'ofertas_count', 'vacantes', 'validado'])) {
                $query->orderBy($field, $direccion);
            }
        } else {
            if ($usuario->rol === 'centro') {
                $query->orderBy('validado', 'asc');
            }
            $query->orderBy('nombre', 'asc');
        }

        $limite = $request->input('limite', 20);
        return response()->json($query->paginate($limite));
    }

    private function aplicarFiltros($query, Request $request)
    {
        if ($request->has('id_familia')) {
            $query->where('id_familia_profesional', (int)$request->input('id_familia'));
        }

        if ($request->has('validado')) {
            $validado = $request->input('validado');
            if ($validado === 'validadas') {
                $query->where('validado', true);
            } elseif ($validado === 'pendientes') {
                $query->where('validado', false);
            }
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhereHas('familiaProfesional', function ($qF) use ($search) {
                        $qF->where('nombre', 'like', "%{$search}%");
                    });
            });
        }
    }

    public function validar(Request $request, $id)
    {
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
