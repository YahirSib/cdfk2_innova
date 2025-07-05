<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Trabajadores;
use Illuminate\Support\Facades\DB;
use App\Models\Pieza;
use App\Models\Detalle;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Salas;

class AgrupacionSalaController extends Controller
{
    public function index()
    {
        $menu = (new MenuController)->obtenerMenu();
        return view('movimientos.agrupacion-sala.mvCargarSalas', ['menu' => $menu]);
    }

    public function create()
    {
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $maximo = Movimiento::where('tipo_doc', 'AS')->max('correlativo');
        if($maximo === null) {
            $numero = 1;
        }else{
            $numero = $maximo + 1;
        }
        $data['correlativo'] = 'AS-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        $data['action'] = 'crear';
        $menu = (new MenuController)->obtenerMenu();
        return view('movimientos.agrupacion-sala.mvAgrupacionSalas', ['data' => $data, 'menu' => $menu]);
    }

}
