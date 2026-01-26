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
    protected $primaryKey = 'id_demandante';

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
        'id_demandante',
        'dni',
        'nombre',
        'apellido1',
        'apellido2',
        'telefono_movil',
        'email',
        'id_familia_profesional',
        'cv_path',
        'situacion'
    ];

    public function familiaProfesional()
    {
        return $this->belongsTo(FamiliaProfesional::class, 'id_familia_profesional');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_demandante', 'id');
    }

    public function titulos()
    {
        return $this->hasMany(TituloDemandante::class, 'id_demandante');
    }


    public function ofertas()
    {
        return $this->belongsToMany(Oferta::class, 'demandantes_oferta', 'id_demandante', 'id_oferta')
                    ->withPivot('adjudicada', 'fecha')
                    ->withTimestamps();
    }
}
