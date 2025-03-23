<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Thiagoprz\CompositeKey\HasCompositeKey;
class TituloDemandante extends Model
{
    use HasCompositeKey;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'titulos_demandante';


    protected $primaryKey = ['id_demandante', 'id_titulo'];

    public $incrementing = false;

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

    public function getCursandoAttribute($value)
    {
        return (bool) $value;
    }
}
