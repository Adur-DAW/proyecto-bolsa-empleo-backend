<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamiliaProfesional extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'familias_profesionales';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre'
    ];
}
