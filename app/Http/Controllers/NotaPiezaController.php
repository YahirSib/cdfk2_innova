<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Trabajadores;
use Illuminate\Support\Facades\DB;
use App\Models\Pieza;
use App\Models\Detalle;

class NotaPiezaController extends Controller
{
    public function index()
    {
        return view('movimientos.nota-piezas.mvNotaPieza');
    }
    
    public function create()
    {
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $maximo = Movimiento::where('tipo_doc', 'NP')->max('correlativo');
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
        $notaPieza = Movimiento::findOrFail($id);
        $data['action'] = 'editar';
        $notaPieza->correlativo = 'NP-' . str_pad($notaPieza->correlativo, 5, '0', STR_PAD_LEFT);
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $data['notaPieza'] = $notaPieza;
        return view('movimientos.nota-piezas.mvCargarPieza', ['data' => $data]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'cacastero' => 'required|min:1',
            'comentario' => 'nullable|string|max:255',
        ]);

        $notaPieza = Movimiento::findOrFail($request->id_movimiento);

        $notaPieza->cacastero = $request->cacastero; // Asignar el ID del carpintero
        $notaPieza->comentario = $request->comentario; // Asignar comentario

        try {
            DB::beginTransaction();
            $notaPieza->update();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Nota de Pieza actualizada con exito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar Nota de Pieza, contacte con Soporte Técnico.']);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'cacastero' => 'required|min:1',
            'comentario' => 'nullable|string|max:255',
        ]);

        $notaPieza = new Movimiento();

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

    public function datatable(Request $request)
    {
        $notaPiezas = Movimiento::query()
            ->select('id_movimiento', 
                DB::raw('CONCAT("NP-", LPAD(correlativo, 5, "0")) AS correlativo'),
                DB::raw('DATE_FORMAT(fecha_ingreso, "%d/%m/%Y") AS fecha_ingreso'), 
                'cacastero', 
                'comentario', 
                'estado', 
                DB::raw('CONCAT(trabajadores.nombre1, " ", trabajadores.nombre2, " ", trabajadores.apellido1, " ", trabajadores.apellido2) AS nombre_cacastero'))
            ->join('trabajadores', 'inv_movimiento.cacastero', '=', 'trabajadores.id_trabajador')
            ->where('tipo_doc', 'NP')
            ->orderBy('id_movimiento', 'desc');

        return datatables()->of($notaPiezas)
            ->addColumn('acciones', function($notaPieza) {

                $html = '<div class="flex justify-evenly items-center">';

                if($notaPieza->estado == 'A') {
                    $html .= '<a id="btn_editar" data_id="'.$notaPieza->id_movimiento.'" href ="'. route('nota-pieza.edit', ['id' => $notaPieza->id_movimiento]) .'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></a>';
                    $html .= '<button id="btn_eliminar" data_id="'.$notaPieza->id_movimiento.'" class="btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>';
                }else{

                }

                $html .= '</div>';

                return $html;
            })->rawColumns(['acciones'])
            ->toJson();


    }

    public function guardarDetalle(Request $request){
        $request->validate([
            'id_pieza' => 'required|integer',
            'cantidad' => 'required|integer|min:1'
        ]);

        $id_enca = $request->id_enca;
        $id_pieza = $request->id_pieza;
        $cantidad = $request->cantidad;

        $validarPieza = Pieza::find($id_pieza);

        if (!$validarPieza) {
            return response()->json(['success' => false, 'message' => 'Pieza no encontrada.']);
        }

        $precio = DB::table('piezas')->where('id_pieza', $id_pieza)->value('costo_cacastero');
        $precioTotal = round($cantidad * $precio, 2);

        try {
            DB::beginTransaction();

            $detalle = new Detalle();
            $detalle->fk_movimiento = $id_enca; // ID del movimiento
            $detalle->fk_pieza = $id_pieza; // ID de la pieza
            $detalle->fk_sala = 0; // Asignar sala como 0 (no se usa en este caso)
            $detalle->unidades = $cantidad; // Cantidad de piezas
            $detalle->costo_unitario = $precio; // Costo unitario de la pieza
            $detalle->costo_total = $precioTotal; // Costo total de la pieza
            $detalle->save();

            $movimiento = Movimiento::find($id_enca);
            $total = $movimiento->totalizar();

            Movimiento::where('id_movimiento', $id_enca)->update(['total' => $total]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Detalle guardado con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Error al guardar el detalle, contacte con Soporte Técnico.']);
        }
    }

    public function cargarDetalles($id){

        $movimiento = Movimiento::find($id);
        $detalles = $movimiento->detalles()->with('pieza')->get();
        $total = $movimiento->totalizarUnidades();
        
        return response()->json(['success' => true, 'detalles' => $detalles, 'total' => $total, 'id_movimiento' => $id]);
    }


}
