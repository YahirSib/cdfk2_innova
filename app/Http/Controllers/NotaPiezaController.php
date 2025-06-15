<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Trabajadores;
use Illuminate\Support\Facades\DB;
use App\Models\Pieza;
use App\Models\Detalle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

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
                    $html .= '<a data-id="'.$notaPieza->id_movimiento.'" href ="'. route('nota-pieza.edit', ['id' => $notaPieza->id_movimiento]) .'" class="btn_editar btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></a>';
                    $html .= '<button data-id="'.$notaPieza->id_movimiento.'" class="btn_eliminar btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>';
                }else if($notaPieza->estado == "I"){
                    $html .= '<a data-id="'.$notaPieza->id_movimiento.'" data-estado="'.$notaPieza->estado.'" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
                    $html .= '<button data-id="'.$notaPieza->id_movimiento.'" class="btnAnular btn btn-sm btn-danger cursor-pointer"> <i class="bx bx-revision text-2xl text-red-600 hover:text-red-400"></i> </button>';
                }else{
                    $html .= '<a data-id="'.$notaPieza->id_movimiento.'" data-estado="'.$notaPieza->estado.'" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
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

    public function borrarDetalle($id)
    {
        $detalle = Detalle::find($id);
        if (!$detalle) {
            return response()->json(['success' => false, 'message' => 'Detalle no encontrado.']);
        }

        try {
            DB::beginTransaction();
            $movimiento = Movimiento::find($detalle->fk_movimiento);
            $detalle->delete();
            $total = $movimiento->totalizar();
            Movimiento::where('id_movimiento', $movimiento->id_movimiento)->update(['total' => $total]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Detalle eliminado con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el detalle, contacte con Soporte Técnico.']);
        }
    }

    public function actualizarDetalle($id, $cant)
    {
        $cant = (int) $cant; // Asegurarse de que la cantidad sea un entero
        $detalle = Detalle::find($id);
        if (!$detalle) {
            return response()->json(['success' => false, 'message' => 'Detalle no encontrado.']);
        }

        $cantidad = $cant;
        $precioUnitario = $detalle->costo_unitario;
        $precioTotal = round($cantidad * $precioUnitario, 2);

        try {
            DB::beginTransaction();
            $detalle->unidades = $cantidad;
            $detalle->costo_total = $precioTotal;
            $detalle->save();

            $movimiento = Movimiento::find($detalle->fk_movimiento);
            $total = $movimiento->totalizar();
            Movimiento::where('id_movimiento', $movimiento->id_movimiento)->update(['total' => $total]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Detalle actualizado con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar el detalle, contacte con Soporte Técnico.']);
        }
    }

    public function destroy($id)
    {
        $notaPieza = Movimiento::find($id);
        if (!$notaPieza) {
            return response()->json(['success' => false, 'message' => 'Nota de Pieza no encontrada.']);
        }

        if ($notaPieza->estado !== 'A') {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una Nota de Pieza que no está activa.']);
        }

        // Borrar los detalles asociados a la Nota de Pieza
        $detalles = Detalle::where('fk_movimiento', $notaPieza->id_movimiento)->get();
        foreach ($detalles as $detalle) {
            $detalle->delete();
        }

        try {
            DB::beginTransaction();
            $notaPieza->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Nota de Pieza eliminada con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar Nota de Pieza, contacte con Soporte Técnico.']);
        }
    }

    public function renderPDF($notaPieza, $detalles, $total, $totalUnidades, $data){
        ini_set('memory_limit', '512M');
        $pdf = Pdf::loadView('movimientos.nota-piezas.pdfNP', compact('notaPieza', 'detalles', 'total', 'totalUnidades', 'data'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('nota_pieza' . $notaPieza->correlativo . '.pdf');
    }

    public function imprimirPreliminar($id)
    {
        
        $data = [];
        $data['title'] = 'NOTA DE PIEZA PRELIMINAR';
        $notaPieza = Movimiento::find($id);
        $data['notaPieza'] = $notaPieza;
        $data['correlativo'] = $notaPieza->correlativo_formateado;
        $data['fecha_ingreso'] = $notaPieza->fecha_ingreso_formateada;
        $data['cacastero'] = $notaPieza->nombre_cacastero;
        $detalles = $notaPieza->detalles()->with('pieza')->get();
        $total = $notaPieza->totalizar();
        $totalUnidades = $notaPieza->totalizarUnidades();
        return $this->renderPDF($notaPieza, $detalles, $total, $totalUnidades, $data);
    }

    public function imprimirFinal($id)
    {
        try{
            DB::beginTransaction();
            $data = [];
            $data['title'] = 'NOTA DE PIEZA';
            $notaPieza = Movimiento::find($id);
            $data['notaPieza'] = $notaPieza;
            $data['correlativo'] = $notaPieza->correlativo_formateado;
            $data['fecha_ingreso'] = $notaPieza->fecha_ingreso_formateada;
            $data['cacastero'] = $notaPieza->nombre_cacastero;
            $detalles = $notaPieza->detalles()->with('pieza')->get();
            $total = $notaPieza->totalizar();
            $totalUnidades = $notaPieza->totalizarUnidades();

            $notaPieza->estado = 'I';
            $notaPieza->fecha_impresion = now(); 
            $notaPieza->save();

            foreach ($detalles as $detalle) {
                $id_pieza = $detalle->fk_pieza;
                $pieza = Pieza::find($id_pieza);
                $pieza->existencia = $pieza->totalizarExistencias();
                $pieza->save();
            }

            DB::commit();
            return $this->renderPDF($notaPieza, $detalles, $total, $totalUnidades, $data);
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error('Error al generar PDF de Nota de Pieza: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al generar el PDF, contacte con Soporte Técnico.']);
        }

        
    }

    public function imprimirHistorico($id)
    {
        $data = [];
        
        $notaPieza = Movimiento::find($id);

        if($notaPieza->estado == "I"){
            $data['title'] = 'NOTA DE PIEZA HISTORICA';
        }else{
            $data['title'] = 'NOTA DE PIEZA ANULADA';
        }

        $data['notaPieza'] = $notaPieza;
        $data['correlativo'] = $notaPieza->correlativo_formateado;
        $data['fecha_ingreso'] = $notaPieza->fecha_ingreso_formateada;
        $data['cacastero'] = $notaPieza->nombre_cacastero;
        $detalles = $notaPieza->detalles()->with('pieza')->get();
        $total = $notaPieza->totalizar();
        $totalUnidades = $notaPieza->totalizarUnidades();
        return $this->renderPDF($notaPieza, $detalles, $total, $totalUnidades, $data);
    }

    public function imprimirAnular($id)
    {
        try{
            DB::beginTransaction();
            $data = [];
            $data['title'] = 'NOTA DE PIEZA ANULADA';
            $notaPieza = Movimiento::find($id);
            $data['notaPieza'] = $notaPieza;
            $data['correlativo'] = $notaPieza->correlativo_formateado;
            $data['fecha_ingreso'] = $notaPieza->fecha_ingreso_formateada;
            $data['cacastero'] = $notaPieza->nombre_cacastero;
            $detalles = $notaPieza->detalles()->with('pieza')->get();
            $total = $notaPieza->totalizar();
            $totalUnidades = $notaPieza->totalizarUnidades();

            $notaPieza->estado = 'Z';
            $notaPieza->fecha_anulacion = now(); 
            $notaPieza->save();

            foreach ($detalles as $detalle) {
                $id_pieza = $detalle->fk_pieza;
                $pieza = Pieza::find($id_pieza);
                $pieza->existencia = $pieza->totalizarExistencias();
                $pieza->save();
            }

            DB::commit();
            return $this->renderPDF($notaPieza, $detalles, $total, $totalUnidades, $data);
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error('Error al generar PDF de Nota de Pieza: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al generar el PDF, contacte con Soporte Técnico.']);
        }
    }


}
