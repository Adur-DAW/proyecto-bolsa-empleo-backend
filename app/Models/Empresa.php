<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

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
        'id_familia_profesional',
        'localidad',
        'telefono',
        'localidad',
        'imagen_url'
    ];

    public function familiaProfesional()
    {
        return $this->belongsTo(FamiliaProfesional::class, 'id_familia_profesional');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_empresa', 'id');
    }

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_empresa');
    }

    protected function imagenUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? asset("storage/{$value}") : null,
        );
    }
}
