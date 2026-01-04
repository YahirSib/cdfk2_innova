<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Trabajadores;
use Illuminate\Support\Facades\DB;
use App\Models\Pieza;
use App\Models\Detalle;
use App\Models\Salas;
use App\Services\MenuService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\PiezasServices;
use App\Services\SalasServices;

class TapizadoTerminadoController extends Controller
{

    private $tipo_doc = 'TT'; // Agrupación de Salas
    private $tipo_mov = 'E'; // Tipo de movimiento para Nota de Pieza
    private $nombre_doc = 'Tapizado Terminado'; // Nombre del documento

    public function getTipoDoc()
    {
        return $this->tipo_doc;
    }

    public function getTipoMov()
    {
        return $this->tipo_mov;
    }

    public function getNombreDoc()
    {
        return $this->nombre_doc;
    }

    public function index()
    {
        return view('movimientos.tapizado-terminado.mvTapizadoTerminado', ['menu' => $this->getMenu()]);
    }

    public function datatable(Request $request)
    {
        $tapizadoTerminado = Movimiento::query()
            ->select(
                'id_movimiento',
                DB::raw('CONCAT("' . $this->getTipoDoc() . '-", LPAD(correlativo, 5, "0")) AS correlativo'),
                DB::raw('DATE_FORMAT(fecha_ingreso, "%d/%m/%Y") AS fecha_ingreso'),
                'tapicero',
                'comentario',
                'estado',
                DB::raw('CONCAT(trabajadores.nombre1, " ", trabajadores.nombre2, " ", trabajadores.apellido1, " ", trabajadores.apellido2) AS nombre_tapicero')
            )
            ->join('trabajadores', 'inv_movimiento.tapicero', '=', 'trabajadores.id_trabajador')
            ->where('tipo_doc', $this->getTipoDoc())
            ->orderBy('id_movimiento', 'desc');

        if ($request->filled('mes')) {
            $tapizadoTerminado->whereMonth('fecha_ingreso', $request->mes);
        }

        return datatables()->of($tapizadoTerminado)
            ->filterColumn('nombre_tapicero', function ($query, $keyword) {
                $sql = 'LOWER(CONCAT(trabajadores.nombre1, " ", trabajadores.nombre2, " ", trabajadores.apellido1, " ", trabajadores.apellido2)) LIKE ?';
                $query->whereRaw($sql, ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('correlativo', function ($query, $keyword) {
                $sql = 'LOWER(CONCAT("' . $this->getTipoDoc() . '-", LPAD(correlativo, 5, "0"))) LIKE ?';
                $query->whereRaw($sql, ["%" . strtolower($keyword) . "%"]);
            })
            ->addColumn('acciones', function ($tapizadoTerminado) {

                $html = '<div class="flex justify-evenly items-center">';

                if ($tapizadoTerminado->estado == 'A') {
                    $html .= '<a data-id="' . $tapizadoTerminado->id_movimiento . '" href ="' . route('tapizado-terminado.edit', ['id' => $tapizadoTerminado->id_movimiento]) . '" class="btn_editar btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></a>';
                    $html .= '<button data-id="' . $tapizadoTerminado->id_movimiento . '" class="btn_eliminar btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>';
                } else if ($tapizadoTerminado->estado == "I") {
                    $html .= '<a data-id="' . $tapizadoTerminado->id_movimiento . '" data-estado="' . $tapizadoTerminado->estado . '" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
                    $html .= '<button data-id="' . $tapizadoTerminado->id_movimiento . '" class="btnAnular btn btn-sm btn-danger cursor-pointer"> <i class="bx bx-revision text-2xl text-red-600 hover:text-red-400"></i> </button>';
                } else {
                    $html .= '<a data-id="' . $tapizadoTerminado->id_movimiento . '" data-estado="' . $tapizadoTerminado->estado . '" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
                }

                $html .= '</div>';

                return $html;
            })->rawColumns(['acciones'])
            ->toJson();
    }

    public function obtenerMeses()
    {
        return $this->obtenerMesesTipoDoc($this->getTipoDoc());
    }

    public function edit($id)
    {
        $tapizadoTerminado = Movimiento::findOrFail($id);
        $data['action'] = 'editar';
        $tapizadoTerminado->correlativo = $this->getTipoDoc() . '-' . str_pad($tapizadoTerminado->correlativo, 5, '0', STR_PAD_LEFT);
        $data['tapicero'] = Trabajadores::all()->where('tipo', 1);
        $data['tapizadoTerminado'] = $tapizadoTerminado;

        if ($tapizadoTerminado->estado != 'A') {
            return redirect()->route('tapizado-terminado.index')->with('error', 'No se puede editar una ' . $this->getNombreDoc() . ' que no está activa.');
        }

        return view('movimientos.tapizado-terminado.mvCargarTapizado', ['data' => $data, 'menu' => $this->getMenu()]);
    }

    public function destroy($id)
    {
        $tapizadoTerminado = Movimiento::find($id);
        if (!$tapizadoTerminado) {
            return response()->json(['success' => false, 'message' => $this->getNombreDoc() . ' no encontrada.']);
        }

        if ($tapizadoTerminado->estado !== 'A') {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una ' . $this->getNombreDoc() . ' que no está activa.']);
        }

        // Borrar los detalles asociados al Traslado de Tapicería
        $detalles = Detalle::where('fk_movimiento', $tapizadoTerminado->id_movimiento)->get();
        foreach ($detalles as $detalle) {
            $detalle->delete();
        }

        try {
            DB::beginTransaction();
            $tapizadoTerminado->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => $this->getNombreDoc() . ' eliminada con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar ' . $this->getNombreDoc() . ', contacte con Soporte Técnico.']);
        }
    }

    public function imprimirHistorico($id)
    {
        $data = [];

        $tapizadoTerminado = Movimiento::find($id);

        if ($tapizadoTerminado->estado == "I") {
            $data['title'] = strtoupper($this->nombre_doc . ' HISTORICA');
        } else {
            $data['title'] = strtoupper($this->nombre_doc . ' ANULADA');
        }

        $data['trasladoTapiceria'] = $tapizadoTerminado;
        $data['correlativo'] = $tapizadoTerminado->correlativo_formateado;
        $data['fecha_ingreso'] = $tapizadoTerminado->fecha_ingreso_formateada;
        $data['tapicero'] = $tapizadoTerminado->nombre_tapicero;
        $detalles_pieza = $tapizadoTerminado->detalles()->where('fk_pieza', '!=', null)->with('pieza')->get();
        $detalles_sala = $tapizadoTerminado->detalles()->where('fk_sala', '!=', null)->with('sala')->get();
        $total = $tapizadoTerminado->totalizar();
        $totalUnidades = $tapizadoTerminado->totalizarUnidades();

        return $this->renderPDF($tapizadoTerminado, $detalles_pieza, $detalles_sala, $total, $totalUnidades, $data);
    }

    public function imprimirAnular($id)
    {
        try {
            DB::beginTransaction();
            $data = [];
            $data['title'] = strtoupper($this->nombre_doc . ' ANULADA');
            $tapizadoTerminado = Movimiento::find($id);
            $data['trasladoTapiceria'] = $tapizadoTerminado;
            $data['correlativo'] = $tapizadoTerminado->correlativo_formateado;
            $data['fecha_ingreso'] = $tapizadoTerminado->fecha_ingreso_formateada;
            $data['tapicero'] = $tapizadoTerminado->nombre_tapicero;
            $detalles_piezas = $tapizadoTerminado->detalles()->where('fk_pieza', '!=', null)->with('pieza')->get();
            $detalles_sala = $tapizadoTerminado->detalles()->where('fk_sala', '!=', null)->with('sala')->get();
            $total = $tapizadoTerminado->totalizar();
            $totalUnidades = $tapizadoTerminado->totalizarUnidades();

            $tapizadoTerminado->estado = 'Z';
            $tapizadoTerminado->fecha_anulacion = now();
            $tapizadoTerminado->save();

            foreach ($detalles_piezas as $detalle) {
                $id_pieza = $detalle->fk_pieza;
                $pieza = Pieza::find($id_pieza);
                $pieza->existencia_traslado = $pieza->totalizarExistenciasTraslado();
                $pieza->existencia_tapizado = $pieza->totalizarExistenciasTapizado();
                $pieza->save();
            }

            foreach ($detalles_sala as $detalle) {
                $id_sala = $detalle->fk_sala;
                $sala = Salas::find($id_sala);
                $sala->existencia_traslado = $sala->totalizarExistenciasTraslado();
                $sala->existencia_tapizado = $sala->totalizarExistenciasTapizado();
                $sala->save();
            }

            DB::commit();
            return $this->renderPDF($tapizadoTerminado, $detalles_piezas, $detalles_sala, $total, $totalUnidades, $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function renderPDF($entrada, $detalles_pieza, $detalles_sala, $total, $totalUnidades, $data)
    {
        ini_set('memory_limit', '512M');
        $pdf = Pdf::loadView('movimientos.tapizado-terminado.pdfTT', compact('entrada', 'detalles_pieza', 'detalles_sala', 'total', 'totalUnidades', 'data'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('tapizado-terminado' . $entrada->correlativo . '.pdf');
    }

    public function create()
    {
        $data['tapicero'] = Trabajadores::all()->where('tipo', 1);
        $maximo = Movimiento::where('tipo_doc', $this->getTipoDoc())->max('correlativo');
        if ($maximo === null) {
            $numero = 1;
        } else {
            $numero = $maximo + 1;
        }
        $data['correlativo'] = $this->getTipoDoc() . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        $data['action'] = 'crear';
        return view('movimientos.tapizado-terminado.mvCargarTapizado', ['data' => $data, 'menu' => $this->getMenu()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tapicero' => 'required|min:1',
            'comentario' => 'nullable|string|max:255',
        ]);

        $tapizadoTerminado = new Movimiento();

        $tapizadoTerminado->tipo_mov = $this->getTipoMov(); // Asignar tipo de movimiento
        $tapizadoTerminado->tipo_doc = $this->getTipoDoc(); // Asignar tipo de documento
        $tapizadoTerminado->fecha_ingreso = now(); // Fecha actual
        $tapizadoTerminado->tapicero = $request->tapicero; // Asignar el ID del carpintero
        $tapizadoTerminado->total = 0; // Inicializar total en 0
        $tapizadoTerminado->comentario = $request->comentario; // Asignar comentario
        $tapizadoTerminado->estado = 'A'; // Asignar estado activo
        $numero_cor = (int) preg_replace('/[^0-9]/', '', $request->correlativo);
        $tapizadoTerminado->correlativo = $numero_cor; // Asignar correlativo

        try {
            DB::beginTransaction();
            $tapizadoTerminado->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => $this->getNombreDoc() . ' registrada con exito.', 'redirect' => route('tapizado-terminado.edit', ['id' => $tapizadoTerminado->id_movimiento])]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar ' . $this->getNombreDoc() . ', contacte con Soporte Técnico.']);
        }


    }

    public function update(Request $request)
    {
        $request->validate([
            'tapicero' => 'required|min:1',
            'comentario' => 'nullable|string|max:255',
        ]);

        $tapizadoTerminado = Movimiento::findOrFail($request->id_movimiento);
        $tapizadoTerminado->tapicero = $request->tapicero; // Asignar el ID del carpintero
        $tapizadoTerminado->comentario = $request->comentario; // Asignar comentario

        try {
            DB::beginTransaction();
            $tapizadoTerminado->update();
            DB::commit();
            return response()->json(['success' => true, 'message' => $this->getNombreDoc() . ' actualizada con exito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar ' . $this->getNombreDoc() . ', contacte con Soporte Técnico.']);
        }
    }

    public function guardarDetalle(Request $request){

        try{
            DB::beginTransaction();
            $id_enca = $request->id_enca;
            $cantidad = $request->cantidad;

            $enca = Movimiento::find($id_enca);
            $tapicero = $enca->tapicero;
            
            if($request->has('id_pieza')){
                $request->validate([
                    'id_pieza' => 'required|integer|exists:piezas,id_pieza',
                    'cantidad' => 'required|integer|min:1',
                ],
                [
                    'id_pieza.required' => 'La pieza es obligatoria',
                    'id_pieza.exists' => 'La pieza seleccionada no existe',
                    'cantidad.required' => 'La cantidad es obligatoria',
                    'cantidad.integer' => 'La cantidad debe ser un número entero',
                    'cantidad.min' => 'La cantidad debe ser mayor o igual a 1',
                ]);

                $id_pieza = $request->id_pieza;
                $precio = DB::table('piezas')->where('id_pieza', $id_pieza)->value('costo_tapicero');
                
                $piezaService = new PiezasServices();
                $valor = $piezaService->disPiezaTraslado($id_pieza);

                if($valor <= 0){
                    return response()->json(['success' => false, 'message' => 'No hay piezas disponibles para ' . $this->nombre_doc . '.']);
                }

                if($cantidad > $valor){
                    return response()->json(['success' => false, 'message' => 'La cantidad solicitada supera la disponibilidad de la pieza seleccionada.']);
                }

            }

            if($request->has('id_salas')){
                $request->validate([
                    'id_salas' => 'required|integer|exists:salas,id_salas',
                    'cantidad' => 'required|integer|min:1',
                ],
                [
                    'id_salas.required' => 'La sala es obligatoria',
                    'id_salas.exists' => 'La sala seleccionada no existe',
                    'cantidad.required' => 'La cantidad es obligatoria',
                    'cantidad.integer' => 'La cantidad debe ser un número entero',
                    'cantidad.min' => 'La cantidad debe ser mayor o igual a 1',
                ]);

                $id_salas = $request->id_salas;
                $precio = DB::table('salas')->where('id_salas', $id_salas)->value('costo_tapicero');

                $salasService = new SalasServices();
                $valor = $salasService->disSalasTraslado($id_salas);
                if($valor <= 0){
                    return response()->json(['success' => false, 'message' => 'No hay salas disponibles para ' . $this->nombre_doc . '.']);
                }

                if($cantidad > $valor){
                    return response()->json(['success' => false, 'message' => 'La cantidad solicitada supera la disponibilidad de la sala seleccionada.']);
                }
            }
            $precioTotal = round($cantidad * $precio, 2);

            $detalle = new Detalle();
            $detalle->fk_movimiento = $id_enca;
            if(isset($id_pieza)){
                $detalle->fk_pieza = $id_pieza;
            }
            if(isset($id_salas)){
                $detalle->fk_sala = $id_salas;
            }
            $detalle->costo_unitario = $precio;
            $detalle->unidades = $cantidad;
            $detalle->costo_total = $precioTotal;
            $detalle->save();

            $movimiento = Movimiento::find($id_enca);
            $total = $movimiento->totalizar();
            Movimiento::where('id_movimiento', $id_enca)->update(['total' => $total]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Detalle guardado con éxito.']);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage() ]);
        }
    }

    public function cargarDetalles($id = null){

        try{
            $detalle = Detalle::with(['pieza', 'sala'])->where('fk_movimiento', $id)->get();

            $total_pieza = 0;
            $total_sala = 0;

            foreach($detalle as $item){
                if( !is_null($item->fk_pieza)){
                    $total_pieza += $item->unidades;
                }
                if( !is_null($item->fk_sala)){
                    $total_sala += $item->unidades;
                }
            }

            return response()->json(['success' => true, 'data' => $detalle, 'total_pieza' => $total_pieza, 'total_sala' => $total_sala]);
        }catch(\Exception $e){
            return response()->json(['success' => false, 'message' => 'Error al cargar los detalles, contacte con Soporte Técnico.']);
        } 

    }

    public function actualizarDetalle($id = null, $cant = null, Request $request){
        try{
            $detalle = Detalle::find($id);
            $id_enca = $detalle->fk_movimiento;

            if($cant <= 0){
                return response()->json(['success' => false, 'message' => 'La cantidad debe ser mayor a cero.']);
            }

            $enca = Movimiento::find($id_enca);
            $tapicero = $enca->tapicero;

            if($detalle->fk_pieza){
                $id_pieza = $detalle->fk_pieza;
                $piezaService = new PiezasServices();
                $valor = $piezaService->disPiezaTraslado($id_pieza) + $detalle->unidades;
                if($cant > $valor){
                    return response()->json(['success' => false, 'message' => 'La cantidad solicitada supera la disponibilidad de la pieza seleccionada.']);
                }
            }

            if($detalle->fk_sala){
                $id_salas = $detalle->fk_sala;
                $salasService = new SalasServices();
                $valor = $salasService->disSalasTraslado($id_salas) + $detalle->unidades;
                if($cant > $valor){
                    return response()->json(['success' => false, 'message' => 'La cantidad solicitada supera la disponibilidad de la sala seleccionada.']);
                }
            }

            $cantidad = $cant;
            $detalle->unidades = $cantidad;
            $detalle->save();

            $movimiento = Movimiento::find($id_enca);
            $total = $movimiento->totalizar();
            Movimiento::where('id_movimiento', $id_enca)->update(['total' => $total]);
            return response()->json(['success' => true, 'message' => 'Detalle actualizado con éxito.']);
        }catch(\Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function borrarDetalle($id = null){
        try{
            DB::beginTransaction();
            $detalle = Detalle::find($id);
            $fk_movimiento = $detalle->fk_movimiento;
            $detalle->delete();

            $movimiento = Movimiento::find($fk_movimiento);
            $total = $movimiento->totalizar();
            Movimiento::where('id_movimiento', $fk_movimiento)->update(['total' => $total]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Detalle eliminado con éxito.']);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el detalle, contacte con Soporte Técnico.']);
        }
    }

    public function imprimirPreliminar($id){
        $data = [];
        $data['title'] = strtoupper( $this->nombre_doc . ' PRELIMINAR');
        $entrada = Movimiento::find($id);
        $data['entrada'] = $entrada;
        $data['correlativo'] = $entrada->correlativo_formateado;
        $data['fecha_ingreso'] = $entrada->fecha_ingreso_formateada;
        $data['tapicero'] = $entrada->nombre_tapicero;
        $detalles_pieza = $entrada->detalles()->where('fk_pieza', '!=', null)->with('pieza')->get();
        $detalles_sala = $entrada->detalles()->where('fk_sala', '!=', null)->with('sala')->get();
        $total = $entrada->totalizar();
        $totalUnidades = $entrada->totalizarUnidades();

        return $this->renderPDF($entrada, $detalles_pieza, $detalles_sala, $total, $totalUnidades, $data);
    }

     public function imprimirFinal($id){
        $data = [];
        $data['title'] = strtoupper( $this->nombre_doc);
        $entrada = Movimiento::find($id);
        $data['entrada'] = $entrada;
        $data['correlativo'] = $entrada->correlativo_formateado;
        $data['fecha_ingreso'] = $entrada->fecha_ingreso_formateada;
        $data['tapicero'] = $entrada->nombre_tapicero;
        $detalles_pieza = $entrada->detalles()->where('fk_pieza', '!=', null)->with('pieza')->get();
        $detalles_sala = $entrada->detalles()->where('fk_sala', '!=', null)->with('sala')->get();
        $total = $entrada->totalizar();
        $totalUnidades = $entrada->totalizarUnidades();

        $entrada->estado = 'I'; // Cambiar el estado a Inactivo
        $entrada->fecha_impresion = now(); 
        $entrada->save();

        foreach ($detalles_pieza as $detalle) {
            $id_pieza = $detalle->fk_pieza;
            $pieza = Pieza::find($id_pieza);
            $pieza->existencia_traslado = $pieza->totalizarExistenciasTraslado();
            $pieza->existencia_tapizado = $pieza->totalizarExistenciasTapizado();
            $pieza->save();
        }

        foreach ($detalles_sala as $detalle) {
            $id_sala = $detalle->fk_sala;
            $sala = Salas::find($id_sala);
            $sala->existencia_traslado = $sala->totalizarExistenciasTraslado();
            $sala->existencia_tapizado = $sala->totalizarExistenciasTapizado();
            $sala->save();
        }

        return $this->renderPDF($entrada, $detalles_pieza, $detalles_sala, $total, $totalUnidades, $data);
    }

}
