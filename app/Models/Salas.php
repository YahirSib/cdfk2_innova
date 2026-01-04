<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

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

    public function totalizarExistencias()
    {
        $total = \DB::table('inv_detalles as d')
            ->join('inv_movimiento as m', 'd.fk_movimiento', '=', 'm.id_movimiento')
            ->where('d.fk_sala', $this->id_salas)
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
            ->where('d.fk_sala', $this->id_salas)
            ->where('m.estado', 'I')
            ->whereIn('m.tipo_doc', ['TP', 'TT'])
            ->selectRaw("
                SUM(CASE 
                    WHEN m.tipo_mov = 'E' THEN -d.unidades
                    WHEN m.tipo_mov = 'S' THEN d.unidades
                    ELSE 0
                END) as total")
            ->value('total') ?? 0;

        return $total;

    }

    public function totalizarExistenciasTapizado()
    {
        $total = \DB::table('inv_detalles as d')
            ->join('inv_movimiento as m', 'd.fk_movimiento', '=', 'm.id_movimiento')
            ->where('d.fk_sala', $this->id_salas)
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
