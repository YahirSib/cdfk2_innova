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

class TrasladoTapiceriaController extends Controller
{

    private $tipo_doc = 'TP'; // Agrupación de Salas
    private $tipo_mov = 'S'; // Tipo de movimiento para Nota de Pieza
    private $nombre_doc = 'Traslado a Tapiceria'; // Nombre del documento

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
        return view('movimientos.traslado-tapiceria.mvTrasladoTapiceria', ['menu' => $this->getMenu()]);
    }

    public function datatable(Request $request)
    {
        $trasladoTapiceria = Movimiento::query()
            ->select(
                'id_movimiento',
                DB::raw('CONCAT("' . $this->getTipoDoc() . '-", LPAD(correlativo, 5, "0")) AS correlativo'),
                DB::raw('DATE_FORMAT(fecha_ingreso, "%d/%m/%Y") AS fecha_ingreso'),
                'cacastero',
                'comentario',
                'estado',
                DB::raw('CONCAT(trabajadores.nombre1, " ", trabajadores.nombre2, " ", trabajadores.apellido1, " ", trabajadores.apellido2) AS nombre_cacastero')
            )
            ->join('trabajadores', 'inv_movimiento.cacastero', '=', 'trabajadores.id_trabajador')
            ->where('tipo_doc', $this->getTipoDoc())
            ->orderBy('id_movimiento', 'desc');

        if ($request->filled('mes')) {
            $trasladoTapiceria->whereMonth('fecha_ingreso', $request->mes);
        }

        return datatables()->of($trasladoTapiceria)
            ->filterColumn('nombre_cacastero', function ($query, $keyword) {
                $sql = 'LOWER(CONCAT(trabajadores.nombre1, " ", trabajadores.nombre2, " ", trabajadores.apellido1, " ", trabajadores.apellido2)) LIKE ?';
                $query->whereRaw($sql, ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('correlativo', function ($query, $keyword) {
                $sql = 'LOWER(CONCAT("' . $this->getTipoDoc() . '-", LPAD(correlativo, 5, "0"))) LIKE ?';
                $query->whereRaw($sql, ["%" . strtolower($keyword) . "%"]);
            })
            ->addColumn('acciones', function ($trasladoTapiceria) {

                $html = '<div class="flex justify-evenly items-center">';

                if ($trasladoTapiceria->estado == 'A') {
                    $html .= '<a data-id="' . $trasladoTapiceria->id_movimiento . '" href ="' . route('traslado-tapiceria.edit', ['id' => $trasladoTapiceria->id_movimiento]) . '" class="btn_editar btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></a>';
                    $html .= '<button data-id="' . $trasladoTapiceria->id_movimiento . '" class="btn_eliminar btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>';
                } else if ($trasladoTapiceria->estado == "I") {
                    $html .= '<a data-id="' . $trasladoTapiceria->id_movimiento . '" data-estado="' . $trasladoTapiceria->estado . '" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
                    $html .= '<button data-id="' . $trasladoTapiceria->id_movimiento . '" class="btnAnular btn btn-sm btn-danger cursor-pointer"> <i class="bx bx-revision text-2xl text-red-600 hover:text-red-400"></i> </button>';
                } else {
                    $html .= '<a data-id="' . $trasladoTapiceria->id_movimiento . '" data-estado="' . $trasladoTapiceria->estado . '" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
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
        $trasladoTapiceria = Movimiento::findOrFail($id);
        $data['action'] = 'editar';
        $trasladoTapiceria->correlativo = $this->getTipoDoc() . '-' . str_pad($trasladoTapiceria->correlativo, 5, '0', STR_PAD_LEFT);
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $data['trasladoTapiceria'] = $trasladoTapiceria;

        if ($trasladoTapiceria->estado != 'A') {
            return redirect()->route('traslado-tapiceria.index')->with('error', 'No se puede editar una ' . $this->getNombreDoc() . ' que no está activa.');
        }

        return view('movimientos.traslado-tapiceria.mvCargarTraslado', ['data' => $data, 'menu' => $this->getMenu()]);
    }

    public function destroy($id)
    {
        $trasladoTapiceria = Movimiento::find($id);
        if (!$trasladoTapiceria) {
            return response()->json(['success' => false, 'message' => $this->getNombreDoc() . ' no encontrada.']);
        }

        if ($trasladoTapiceria->estado !== 'A') {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una ' . $this->getNombreDoc() . ' que no está activa.']);
        }

        // Borrar los detalles asociados al Traslado de Tapicería
        $detalles = Detalle::where('fk_movimiento', $trasladoTapiceria->id_movimiento)->get();
        foreach ($detalles as $detalle) {
            $detalle->delete();
        }

        try {
            DB::beginTransaction();
            $trasladoTapiceria->delete();
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

        $trasladoTapiceria = Movimiento::find($id);

        if ($trasladoTapiceria->estado == "I") {
            $data['title'] = strtoupper($this->nombre_doc . ' HISTORICA');
        } else {
            $data['title'] = strtoupper($this->nombre_doc . ' ANULADA');
        }

        $data['trasladoTapiceria'] = $trasladoTapiceria;
        $data['correlativo'] = $trasladoTapiceria->correlativo_formateado;
        $data['fecha_ingreso'] = $trasladoTapiceria->fecha_ingreso_formateada;
        $data['cacastero'] = $trasladoTapiceria->nombre_cacastero;
        $detalles = $trasladoTapiceria->detalles()->with('pieza')->get();
        $total = $trasladoTapiceria->totalizar();
        $totalUnidades = $trasladoTapiceria->totalizarUnidades();
        return $this->renderPDF($trasladoTapiceria, $detalles, $total, $totalUnidades, $data);
    }

    public function imprimirAnular($id)
    {
        try {
            DB::beginTransaction();
            $data = [];
            $data['title'] = strtoupper($this->nombre_doc . ' ANULADA');
            $trasladoTapiceria = Movimiento::find($id);
            $data['trasladoTapiceria'] = $trasladoTapiceria;
            $data['correlativo'] = $trasladoTapiceria->correlativo_formateado;
            $data['fecha_ingreso'] = $trasladoTapiceria->fecha_ingreso_formateada;
            $data['cacastero'] = $trasladoTapiceria->nombre_cacastero;
            $detalles = $trasladoTapiceria->detalles()->with('pieza')->get();
            $total = $trasladoTapiceria->totalizar();
            $totalUnidades = $trasladoTapiceria->totalizarUnidades();

            $trasladoTapiceria->estado = 'Z';
            $trasladoTapiceria->fecha_anulacion = now();
            $trasladoTapiceria->save();

            foreach ($detalles as $detalle) {
                $id_pieza = $detalle->fk_pieza;
                $pieza = Pieza::find($id_pieza);
                $pieza->existencia = $pieza->totalizarExistencias();
                $pieza->save();
            }

            DB::commit();
            return $this->renderPDF($trasladoTapiceria, $detalles, $total, $totalUnidades, $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al generar el PDF, contacte con Soporte Técnico.']);
        }
    }

    public function renderPDF($trasladoTapiceria, $detalles, $total, $totalUnidades, $data)
    {
        ini_set('memory_limit', '512M');
        $pdf = Pdf::loadView('movimientos.traslado-tapiceria.pdfTT', compact('trasladoTapiceria', 'detalles', 'total', 'totalUnidades', 'data'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('traslado_tapiceria' . $trasladoTapiceria->correlativo . '.pdf');
    }

    public function create()
    {
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $maximo = Movimiento::where('tipo_doc', $this->getTipoDoc())->max('correlativo');
        if ($maximo === null) {
            $numero = 1;
        } else {
            $numero = $maximo + 1;
        }
        $data['correlativo'] = $this->getTipoDoc() . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        $data['action'] = 'crear';
        return view('movimientos.traslado-tapiceria.mvCargarTraslado', ['data' => $data, 'menu' => $this->getMenu()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cacastero' => 'required|min:1',
            'comentario' => 'nullable|string|max:255',
        ]);

        $trasladoTapiceria = new Movimiento();

        $trasladoTapiceria->tipo_mov = $this->getTipoMov(); // Asignar tipo de movimiento
        $trasladoTapiceria->tipo_doc = $this->getTipoDoc(); // Asignar tipo de documento
        $trasladoTapiceria->fecha_ingreso = now(); // Fecha actual
        $trasladoTapiceria->cacastero = $request->cacastero; // Asignar el ID del carpintero
        $trasladoTapiceria->total = 0; // Inicializar total en 0
        $trasladoTapiceria->comentario = $request->comentario; // Asignar comentario
        $trasladoTapiceria->estado = 'A'; // Asignar estado activo
        $numero_cor = (int) preg_replace('/[^0-9]/', '', $request->correlativo);
        $trasladoTapiceria->correlativo = $numero_cor; // Asignar correlativo

        try {
            DB::beginTransaction();
            $trasladoTapiceria->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => $this->getNombreDoc() . ' registrada con exito.', 'redirect' => route('traslado-tapiceria.edit', ['id' => $trasladoTapiceria->id_movimiento])]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar ' . $this->getNombreDoc() . ', contacte con Soporte Técnico.']);
        }


    }

    public function update(Request $request)
    {
        $request->validate([
            'cacastero' => 'required|min:1',
            'comentario' => 'nullable|string|max:255',
        ]);

        $trasladoTapiceria = Movimiento::findOrFail($request->id_movimiento);
        $trasladoTapiceria->cacastero = $request->cacastero; // Asignar el ID del carpintero
        $trasladoTapiceria->comentario = $request->comentario; // Asignar comentario

        try {
            DB::beginTransaction();
            $trasladoTapiceria->update();
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
            $cacastero = $enca->cacastero;
            
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
                $precio = DB::table('piezas')->where('id_pieza', $id_pieza)->value('costo_cacastero');
                
                $piezaService = new PiezasServices();
                $valor = $piezaService->disPiezaByTrabajador($id_pieza, $cacastero);

                if($valor <= 0){
                    return response()->json(['success' => false, 'message' => 'No hay piezas disponibles para traslado a tapiceria.']);
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
                $precio = DB::table('salas')->where('id_salas', $id_salas)->value('costo_cacastero');

                $salasService = new SalasServices();
                $valor = $salasService->disSalasbyTrabajador($id_salas, $cacastero);
                if($valor <= 0){
                    return response()->json(['success' => false, 'message' => 'No hay salas disponibles para traslado a tapiceria.']);
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
            return response()->json(['success' => true, 'message' => 'Detalle actualizado con éxito.']);
        }catch(\Exception $e){
            return response()->json(['success' => false, 'message' => 'Error al actualizar el detalle, contacte con Soporte Técnico.']);
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

}
