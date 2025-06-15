<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Detalle;

class Movimiento extends Model
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

    public function detalles()
    {
        return $this->hasMany(Detalle::class, 'fk_movimiento', 'id_movimiento');
    }

    public function totalizar()
    {
        return $this->detalles()->sum('costo_total');
    }

    public function totalizarUnidades(){
        return $this->detalles()->sum('unidades');
    }

}
