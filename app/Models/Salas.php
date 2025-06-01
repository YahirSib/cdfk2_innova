<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salas extends Model
{
    protected $table = 'salas';
    protected $primaryKey = 'id_salas';
    public $timestamps = false;
    protected $keyType = 'int';
    protected $fillable = [
        'nombre',
        'codigo',
        'nombre',
        'descripcion',
        'costo_cacastero',
        'costo_tapicero',
        'estado'
    ];

    protected $casts = [
        'id_sala' => 'int',
        'codigo' => 'string',
        'nombre' => 'string',
        'descripcion' => 'string',
        'costo_cacastero' => 'double',
        'costo_tapicero' => 'double',
        'estado' => 'int'
    ];
}
