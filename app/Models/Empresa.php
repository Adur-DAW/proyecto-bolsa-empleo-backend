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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_usuario',
        'validado',
        'cif',
        'nombre',
        'telefono',
        'localidad'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_empresa', 'id');
    }
}
