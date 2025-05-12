<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pieza extends Model
{
    protected $table = 'piezas';
    protected $primaryKey = 'id_pieza';
    public $timestamps = false;
    protected $keyType = 'int';
    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'estado',
        'costo_cacastero',
        'costo_tapicero'
    ];

    protected $casts = [
        'id_pieza' => 'int',
        'codigo' => 'string',
        'nombre' => 'string',
        'descripcion' => 'string',
        'estado' => 'int',
        'costo_cacastero' => 'double',
        'costo_tapicero' => 'double',
    ];

}
