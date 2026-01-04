<?php

namespace App\Services;

use App\Models\Detalle;
use Illuminate\Support\Facades\Auth;
use App\Models\Piezas;
use App\Models\Salas;
use Illuminate\Support\Facades\DB;

class SalasServices
{

    public function disSalasByTrabajador($id_sala, $id_trabajador)
    {

        // Entradas
        $entradas = Detalle::query()
            ->select(DB::raw('IFNULL(SUM(unidades), 0) as total'))
            ->join('inv_movimiento', 'inv_movimiento.id_movimiento', '=', 'inv_detalles.fk_movimiento')
            ->where('inv_movimiento.tipo_mov', 'E')
            ->whereIn('inv_movimiento.estado', ['I', 'A'])
            ->where('inv_movimiento.cacastero', $id_trabajador)
            ->whereNotNull('inv_detalles.fk_sala')
            ->where('inv_detalles.fk_sala', $id_sala)
            ->value('total');

        // Salidas
        $salidas = Detalle::query()
            ->select(DB::raw('IFNULL(SUM(unidades), 0) as total'))
            ->join('inv_movimiento', 'inv_movimiento.id_movimiento', '=', 'inv_detalles.fk_movimiento')
            ->where('inv_movimiento.tipo_mov', 'S')
            ->whereIn('inv_movimiento.estado', ['I', 'A'])
            ->where('inv_movimiento.cacastero', $id_trabajador)
            ->whereNotNull('inv_detalles.fk_sala')
            ->where('inv_detalles.fk_sala', $id_sala)
            ->value('total');

        return $entradas - $salidas; // O manejar el caso donde la pieza no existe
    }

    public function disSalasTraslado($id_sala)
    {

        // Entradas
        $entradas = Detalle::query()
            ->select(DB::raw('IFNULL(SUM(unidades), 0) as total'))
            ->join('inv_movimiento', 'inv_movimiento.id_movimiento', '=', 'inv_detalles.fk_movimiento')
            ->where('inv_movimiento.tipo_mov', 'S')
            ->where('inv_movimiento.tipo_doc', 'TP')
            ->whereIn('inv_movimiento.estado', ['I', 'A'])
            ->whereNotNull('inv_detalles.fk_sala')
            ->where('inv_detalles.fk_sala', $id_sala)
            ->value('total');

        // Salidas
        $salidas = Detalle::query()
            ->select(DB::raw('IFNULL(SUM(unidades), 0) as total'))
            ->join('inv_movimiento', 'inv_movimiento.id_movimiento', '=', 'inv_detalles.fk_movimiento')
            ->where('inv_movimiento.tipo_mov', 'E')
            ->where('inv_movimiento.tipo_doc', 'TT')
            ->whereIn('inv_movimiento.estado', ['I', 'A'])
            ->whereNotNull('inv_detalles.fk_sala')
            ->where('inv_detalles.fk_sala', $id_sala)
            ->value('total');

        return $entradas - $salidas; // O manejar el caso donde la pieza no existe
    }

}