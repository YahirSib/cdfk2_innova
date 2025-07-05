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
use App\Services\MenuService;

class AgrupacionSalaController extends Controller
{

    private $tipo_doc = 'AS'; // Agrupación de Salas

    public function getTipoDoc()
    {
        return $this->tipo_doc;
    }

    public function index()
    {
        return view('movimientos.agrupacion-sala.mvCargarSalas', ['menu' => $this->getMenu()]);
    }

    public function create()
    {
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $maximo = Movimiento::where('tipo_doc', $this->getTipoDoc())->max('correlativo');
        if($maximo === null) {
            $numero = 1;
        }else{
            $numero = $maximo + 1;
        }
        $data['correlativo'] = $this->getTipoDoc() . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        $data['action'] = 'crear';
        return view('movimientos.agrupacion-sala.mvAgrupacionSalas', ['data' => $data, 'menu' => $this->getMenu()]);
    }

}
