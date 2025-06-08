<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaPieza extends Model
{
    protected $table = 'inv_movimiento';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;
    protected $keyType = 'int';
    protected $fillable = [
        'tipo_mov',
        'tipo_doc',
        'fecha_ingreso',
        'cacastero',
        'total',
        'comentario',
        'estado',
        'correlativo'
    ];
}
