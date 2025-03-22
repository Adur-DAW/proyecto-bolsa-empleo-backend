<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TituloDemandante extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'titulos_demandante';

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
        'id_demandante',
        'id_titulo',
        'centro',
        'año',
        'cursando'
    ];

    public function titulo()
    {
        return $this->belongsTo(Titulo::class, 'id_titulo');
    }

    public function demandante()
    {
        return $this->belongsTo(Demandante::class, 'id_demandante');
    }
}
