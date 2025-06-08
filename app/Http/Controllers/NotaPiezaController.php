<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotaPieza;
use App\Models\Trabajadores;
use Illuminate\Support\Facades\DB;

class NotaPiezaController extends Controller
{
    public function index()
    {
        return view('movimientos.nota-piezas.mvNotaPieza');
    }
    
    public function create()
    {
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $maximo = NotaPieza::max('correlativo');
        if($maximo === null) {
            $numero = 1;
        }else{
            $numero = $maximo + 1;
        }
        $data['correlativo'] = 'NP-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        $data['action'] = 'crear';
        return view('movimientos.nota-piezas.mvCargarPieza', ['data' => $data]);
    }

    public function edit($id)
    {
        $notaPieza = NotaPieza::findOrFail($id);
        $data['action'] = 'editar';
        $notaPieza->correlativo = 'NP-' . str_pad($notaPieza->correlativo, 5, '0', STR_PAD_LEFT);
        $notaPieza->nombre_cacastero = Trabajadores::where('id_trabajador', $notaPieza->cacastero)->value(DB::raw("CONCAT(nombre1, ' ', apellido1)"));
        $data['notaPieza'] = $notaPieza;
        return view('movimientos.nota-piezas.mvCargarPieza', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cacastero' => 'required|min:1',
            'comentario' => 'nullable|string|max:255',
        ]);

        $notaPieza = new NotaPieza();

        $notaPieza->tipo_mov = 'E'; // Asignar tipo de movimiento
        $notaPieza->tipo_doc = 'NP'; // Asignar tipo de documento
        $notaPieza->fecha_ingreso = now(); // Fecha actual
        $notaPieza->cacastero = $request->cacastero; // Asignar el ID del carpintero
        $notaPieza->total = 0; // Inicializar total en 0
        $notaPieza->comentario = $request->comentario; // Asignar comentario
        $notaPieza->estado = 'A'; // Asignar estado activo
        $numero_cor = (int) preg_replace('/[^0-9]/', '', $request->correlativo);
        $notaPieza->correlativo = $numero_cor; // Asignar correlativo
    
        try {
            DB::beginTransaction();
            $notaPieza->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Nota de Pieza registrada con exito.', 'redirect' => route('nota-pieza.edit', ['id' => $notaPieza->id_movimiento])]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar Nota de Pieza, contacte con Soporte Técnico.']);
        }  
        
        
    }
}
