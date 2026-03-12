<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function obtenerConfiguracion()
    {
        return response()->json([
            'ofertas_publicas' => config('app.ofertas_publicas'),
        ]);
    }
}
