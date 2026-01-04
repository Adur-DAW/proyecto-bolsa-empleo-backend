<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'empresas';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_empresa';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_empresa',
        'validado',
        'nombre',
        'familia_profesional',
        'localidad',
        'telefono',
        'localidad'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_empresa', 'id');
    }
}
