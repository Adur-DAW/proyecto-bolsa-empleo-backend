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

        $request->validate([
            'dni' => 'required|string|size:9|unique:demandantes,dni',
            'nombre' => 'required|string|max:45',
            'apellido1' => 'required|string|max:45',
            'apellido2' => 'nullable|string|max:45',
            'telefono_movil' => 'required|string|size:9',
            'email' => ['required', 'string', 'email', 'max:45', Rule::unique('demandantes')],
            'situacion' => 'required|integer|min:1|max:3',
            'id_familia_profesional' => 'nullable|exists:familias_profesionales,id'
        ]);

        Demandante::create([
            'id_demandante' => $usuario->id,
            'dni' => $request->dni,
            'nombre' => $request->nombre,
            'apellido1' => $request->apellido1,
            'apellido2' => $request->apellido2 ?? '',
            'telefono_movil' => $request->telefono_movil,
            'email' => $request->email,
            'situacion' => $request->situacion,
            'id_familia_profesional' => $request->id_familia_profesional
        ]);
    }

    public function actualizar(Request $request) {
        $usuario = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'dni' => 'required|string|size:9',
            'nombre' => 'required|string|max:45',
            'apellido1' => 'required|string|max:45',
            'apellido2' => 'nullable|string|max:45',
            'telefono_movil' => 'required|string|size:9',
            'email' => ['required', 'string', 'email', 'max:45', Rule::unique('demandantes')->ignore($usuario->id, 'id_demandante')],
            'situacion' => 'required|integer|min:1|max:3',
            'id_familia_profesional' => 'nullable|exists:familias_profesionales,id'
        ]);

        $demandante = Demandante::where('id_demandante', $usuario->id)->first();

        if ($request->hasFile('cv')) {
            $path = $request->file('cv')->store('curriculums', 'public');
            $cvPath = $path;
        }

        $updateData = [
            'dni' => $request->dni,
            'nombre' => $request->nombre,
            'apellido1' => $request->apellido1,
            'apellido2' => $request->apellido2 ?? '',
            'telefono_movil' => $request->telefono_movil,
            'email' => $request->email,
            'situacion' => $request->situacion,
            'id_familia_profesional' => $request->id_familia_profesional
        ];

        if (isset($cvPath)) {
            $updateData['cv_path'] = $cvPath;
        }

        $demandante->update($updateData);

        return response()->json([
            'message' => 'Demandante actualizado con éxito',
            'demandante' => $demandante
        ]);
    }

    public function obtenerJWT()
    {
        $usuario = Usuario::with('demandante')->find(JWTAuth::parseToken()->authenticate()->id);

        return response()->json($usuario->demandante);
    }

    public function obtener()
    {
        $demandantes = Demandante::all();

        return response()->json($demandantes);
    }
}
