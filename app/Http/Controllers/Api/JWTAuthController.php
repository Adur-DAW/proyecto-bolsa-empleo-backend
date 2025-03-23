<?php

namespace App\Http\Controllers\Api;

use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Demandante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JWTAuthController extends Controller
{
    public function registrar(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email|max:255|unique:usuarios',
        'password' => 'required|string|min:6|confirmed',
        'password_confirmation' => 'required|string|min:6',
        'rol' => 'required|string|in:demandante,empresa'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
    }

    DB::beginTransaction();

    try
    {
        $usuario = Usuario::create([
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'rol' => $request->get('rol')
        ]);

        if ($usuario->rol == 'demandante') {
            $request->validate([
                'dni' => 'required|string|size:9|unique:demandantes,dni',
                'nombre' => 'required|string|max:45',
                'apellido1' => 'required|string|max:45',
                'apellido2' => 'nullable|string|max:45',
                'telefono_movil' => 'required|string|size:9',
                'email' => ['required', 'string', 'email', 'max:45', Rule::unique('demandantes')],
                'situacion' => 'required|integer|min:0|max:1',
            ]);

            $demandante = Demandante::create([
                'id_demandante' => $usuario->id,
                'dni' => $request->dni,
                'nombre' => $request->nombre,
                'apellido1' => $request->apellido1,
                'apellido2' => $request->apellido2 ?? '',
                'telefono_movil' => $request->telefono_movil,
                'email' => $request->email,
                'situacion' => $request->situacion,
            ]);

            $usuario->demandante = $demandante;
        }
        elseif ($usuario->rol == 'empresa') {
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

            $usuario->empresa = $empresa;
        }

        DB::commit();

        $token = JWTAuth::fromUser($usuario);

        return response()->json(compact('usuario', 'token'), 201);
    }
    catch (\Exception $e)
    {
        DB::rollBack();

        if (isset($usuario)) {
            $usuario->delete();
        }

        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales invalidas'], 401);
        }

        $usuarioAuth = JWTAuth::user();

        $usuario = [
            'id' => $usuarioAuth->id,
            'email' => $usuarioAuth->email,
            'rol' => $usuarioAuth->rol,
            'nombreCompleto' => 'Sin especificar'
        ];

        if ($usuarioAuth->rol == 'demandante')
        {
            $usuarioAuth->load('demandante');
            $usuario['nombreCompleto'] = $usuarioAuth->demandante->nombre ?? 'Sin nombre';
        }
        elseif ($usuarioAuth->rol == 'empresa')
        {
            $usuarioAuth->load('empresa');

            if ($usuarioAuth->empresa->validado == false) {
                return response()->json(['error' => 'La empresa no ha sido validada'], 403);
            }

            $usuario['nombreCompleto'] = $usuarioAuth->empresa->nombre ?? 'Sin nombre';
        }
        else
        {
            if ($usuarioAuth->rol == 'centro') {
                $usuario['nombreCompleto'] = 'Administrador';
            }
            else {
                return response()->json(['error' => 'El usuario no tiene ROL'], 403);
            }
        }

        return response()->json([
            'message' => 'Login realizado correctamente',
            'token' => $token,
            'usuario' => $usuario
        ]);
    }

    public function obtenerUsuarioJWT()
    {
        try {
            if (!$usuario = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token invalido'], 400);
        }

        return response()->json(compact('usuario'));
    }

    public function cerrarSesion()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Se ha cerrado sesión correctamente']);
    }

    public function refrescarToken()
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['error' => 'Token no proporcionado'], 401);
            }

            $newToken = JWTAuth::refresh($token);
            return response()->json(['token' => $newToken]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo refrescar el token'], 401);
        }
    }
}
