<?php

namespace App\Http\Controllers\Api;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;

class JWTAuthController extends Controller
{
    public function registrar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:usuarios',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $usuario = Usuario::create([
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'rol' => 'demandante'
        ]);

        $token = JWTAuth::fromUser($usuario);

        return response()->json(compact('usuario', 'token'), 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales invalidas'], 401);
        }

        $usuarioAuth = auth()->user();

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
            $usuario['nombreCompleto'] = $usuarioAuth->empresa->nombre ?? 'Sin nombre';
        }
        else
        {
            $usuario['rol'] = 'sinrol';
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
}
