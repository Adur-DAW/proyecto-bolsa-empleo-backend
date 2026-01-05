<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\FamiliaProfesional;
use App\Models\TipoContrato;

class MaestrosController extends Controller
{
    public function getFamiliasProfesionales()
    {
        return response()->json(FamiliaProfesional::all());
    }

    public function getTiposContrato()
    {
        return response()->json(TipoContrato::all());
    }
}
