<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotaPiezaController extends Controller
{
    public function index()
    {
        return view('movimientos.nota-piezas.mvNotaPieza');
    }
    
    public function create()
    {
        return view('movimientos.nota-piezas.mvCargarPieza');
    }
}
