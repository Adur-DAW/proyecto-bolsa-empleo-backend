<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ofertas';

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
        'nombre',
        'fecha_publicacion',
        'numero_puestos',
        'tipo_contrato',
        'horario',
        'obs',
        'abierta',
        'fecha_cierre',
        'id_empresa'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function titulos() {
        return $this->belongsToMany(Titulo::class, 'titulos_oferta', 'id_oferta', 'id_titulo');
    }

    public function demandantes()
    {
        return $this->belongsToMany(Demandante::class, 'demandantes_oferta', 'id_oferta', 'id_demandante')
                    ->withPivot('adjudicada', 'fecha')
                    ->withTimestamps();
    }
}
