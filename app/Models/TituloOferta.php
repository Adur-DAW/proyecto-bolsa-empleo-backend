<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Thiagoprz\CompositeKey\HasCompositeKey;
class TituloOferta extends Model
{
    use HasCompositeKey;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'titulos_oferta';


    protected $primaryKey = ['id_oferta', 'id_titulo'];

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_oferta',
        'id_titulo'
    ];

    public function titulo()
    {
        return $this->belongsTo(Titulo::class, 'id_titulo');
    }

    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta');
    }
}
