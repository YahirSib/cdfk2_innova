<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajadores extends Model
{
    protected $fillable = [
        'nombre1',
        'nombre2',
        'apellido1',
        'apellido2',
        'edad',
        'tipo',
        'dui',
        'telefono'
    ];

    protected $table = 'trabajadores';
    public $timestamps = false;
    protected $primaryKey = 'id_trabajador';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $casts = [
        'id_trabajador' => 'int',
        'nombre1' => 'string',
        'nombre2' => 'string',
        'apellido1' => 'string',
        'apellido2' => 'string',
        'edad' => 'int',
        'tipo' => 'string',
        'dui' => 'string',
        'telefono' => 'string'
    ];

}
