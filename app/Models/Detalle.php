<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle extends Model
{
    protected $table = 'inv_detalles';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;
    protected $keyType = 'int';
    protected $fillable = [
        'fk_movimiento',
        'fk_pieza',
        'fk_sala',
        'unidades',
        'costo_unitario',
        'costo_total'
    ];

    public function pieza()
    {
        return $this->belongsTo(Pieza::class, 'fk_pieza', 'id_pieza');
    }

}
