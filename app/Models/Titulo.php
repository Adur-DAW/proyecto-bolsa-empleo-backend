<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titulo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'titulos';

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
    ];
}
