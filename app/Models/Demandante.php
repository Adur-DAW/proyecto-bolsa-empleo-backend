<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demandante extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'demandantes';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_usuario',
        'email',
        'dni',
        'nombre',
        'apellido1',
        'apellido2',
        'telefono_movil',
        'situacion'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function titulos()
    {
        return $this->hasMany(TituloDemandante::class, 'id_demandante');
    }
}
