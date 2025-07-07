<?php

namespace App\Services;

use App\Models\Detalle;
use Illuminate\Support\Facades\Auth;
use App\Models\Piezas;
use App\Models\Salas;
use Illuminate\Support\Facades\DB;

class PiezasServices
{

    public function disPiezaByTrabajador($id_pieza, $id_trabajador)
    {

        // Entradas
        $entradas = Detalle::query()
            ->select(DB::raw('IFNULL(SUM(unidades), 0) as total'))
            ->join('inv_movimiento', 'inv_movimiento.id_movimiento', '=', 'inv_detalles.fk_movimiento')
            ->where('inv_movimiento.tipo_mov', 'E')
            ->where('inv_movimiento.estado', 'I')
            ->where('inv_movimiento.cacastero', $id_trabajador)
            ->whereNotNull('inv_detalles.fk_pieza')
            ->where('inv_detalles.fk_pieza', $id_pieza)
            ->value('total');

        // Salidas
        $salidas = Detalle::query()
            ->select(DB::raw('IFNULL(SUM(unidades), 0) as total'))
            ->join('inv_movimiento', 'inv_movimiento.id_movimiento', '=', 'inv_detalles.fk_movimiento')
            ->where('inv_movimiento.tipo_mov', 'S')
            ->where('inv_movimiento.estado', 'I')
            ->where('inv_movimiento.cacastero', $id_trabajador)
            ->whereNotNull('inv_detalles.fk_pieza')
            ->where('inv_detalles.fk_pieza', $id_pieza)
            ->value('total');

        return $entradas - $salidas; // O manejar el caso donde la pieza no existe
    }

}