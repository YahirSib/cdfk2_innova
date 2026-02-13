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
        'costo_tapicero',
        'individual',
        'precio_venta'
    ];

    protected $casts = [
        'id_pieza' => 'int',
        'codigo' => 'string',
        'nombre' => 'string',
        'descripcion' => 'string',
        'estado' => 'int',
        'costo_cacastero' => 'double',
        'costo_tapicero' => 'double',
        'individual' => 'int',
        'precio_venta' => 'double'
    ];

    public function totalizarExistencias()
    {
        $total = \DB::table('inv_detalles as d')
            ->join('inv_movimiento as m', 'd.fk_movimiento', '=', 'm.id_movimiento')
            ->where('d.fk_pieza', $this->id_pieza)
            ->where('m.estado', 'I')
            ->whereNotIn('m.tipo_doc', ['TT', 'VS'])
            ->selectRaw("
                SUM(CASE 
                    WHEN m.tipo_mov = 'E' THEN d.unidades
                    WHEN m.tipo_mov = 'S' THEN -d.unidades
                    ELSE 0
                END) as total")
            ->value('total') ?? 0;

        return $total;

    }

    public function totalizarExistenciasTraslado()
    {
        $total = \DB::table('inv_detalles as d')
            ->join('inv_movimiento as m', 'd.fk_movimiento', '=', 'm.id_movimiento')
            ->where('d.fk_pieza', $this->id_pieza)
            ->where('m.estado', 'I')
            ->whereIn('m.tipo_doc', ['TP', 'TT'])
            ->selectRaw("
                SUM(CASE 
                    WHEN m.tipo_mov = 'S' THEN d.unidades
                    WHEN m.tipo_mov = 'E' THEN -d.unidades
                    ELSE 0
                END) as total")
            ->value('total') ?? 0;

        return $total;

    }

    public function totalizarExistenciasTapizado()
    {
        $total = \DB::table('inv_detalles as d')
            ->join('inv_movimiento as m', 'd.fk_movimiento', '=', 'm.id_movimiento')
            ->where('d.fk_pieza', $this->id_pieza)
            ->where('m.estado', 'I')
            ->whereIn('m.tipo_doc', ['TT', 'VS'])
            ->selectRaw("
                SUM(CASE 
                    WHEN m.tipo_mov = 'E' THEN d.unidades
                    WHEN m.tipo_mov = 'S' THEN -d.unidades
                    ELSE 0
                END) as total")
            ->value('total') ?? 0;

        return $total;

    }


}
