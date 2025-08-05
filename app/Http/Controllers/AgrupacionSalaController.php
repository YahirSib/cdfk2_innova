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
use App\Services\PiezasServices;
use App\Http\Controllers\SalasController;

class AgrupacionSalaController extends Controller
{

    private $tipo_entrada = 'AS';
    private $tipo_salida = 'SP'; // Agrupación de Salas
    private $mov_entrada = 'E';
    private $mov_salida = 'S';
    private $nombre_doc = 'Agrupación de Salas';

    public function getDocEntrada()
    {
        return $this->tipo_entrada;
    }

    public function getDocSalida()
    {
        return $this->tipo_salida;
    }

    public function getTipoEntrada()
    {
        return $this->mov_entrada;
    }

    public function getTipoSalida()
    {
        return $this->mov_salida;
    }

    public function getNombreDoc()
    {
        return $this->nombre_doc;
    }

    public function index()
    {
        return view('movimientos.agrupacion-sala.mvCargarSalas', ['menu' => $this->getMenu()]);
    }

    public function create()
    {
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $maximo = Movimiento::where('tipo_doc', $this->getDocEntrada())->max('correlativo');
        if($maximo === null) {
            $numero = 1;
        }else{
            $numero = $maximo + 1;
        }
        $data['correlativo'] = $this->getDocEntrada() . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        $data['action'] = 'crear';
        return view('movimientos.agrupacion-sala.mvAgrupacionSalas', ['data' => $data, 'menu' => $this->getMenu()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cacastero' => 'required|min:1',
            'comentario' => 'nullable|string|max:255',
        ]);

        $numero_cor = (int) preg_replace('/[^0-9]/', '', $request->correlativo);

        $AS_entrada = new Movimiento();
        $AS_entrada->tipo_mov = $this->getTipoEntrada(); // Asignar tipo de movimiento
        $AS_entrada->tipo_doc = $this->getDocEntrada(); // Asignar tipo de documento
        $AS_entrada->fecha_ingreso = now(); // Fecha actual
        $AS_entrada->cacastero = $request->cacastero; // Asignar el ID del carpintero
        $AS_entrada->total = 0; // Inicializar total en 0
        $AS_entrada->comentario = $request->comentario; // Asignar comentario
        $AS_entrada->estado = 'A'; // Asignar estado activo        
        $AS_entrada->correlativo = $numero_cor; // Asignar correlativo

        $AS_salida = new Movimiento();
        $AS_salida->tipo_mov = $this->getTipoSalida(); // Asignar tipo de movimiento
        $AS_salida->tipo_doc = $this->getDocSalida(); // Asignar tipo de documento
        $AS_salida->fecha_ingreso = now(); // Fecha actual
        $AS_salida->cacastero = $request->cacastero; // Asignar el ID del carpintero
        $AS_salida->total = 0; // Inicializar total en 0
        $AS_salida->comentario = $request->comentario; // Asignar comentario
        $AS_salida->estado = 'A';
        $AS_salida->correlativo = $numero_cor; // Asignar correlativo

    
        try {
            DB::beginTransaction();
            $AS_entrada->save();

            $AS_salida->fk_doc_afecta = $AS_entrada->id_movimiento; // Asignar el ID del movimiento de entrada a la salida

            $AS_salida->save();
            DB::commit();
            return response()->json(['success' => true, 'message' =>  $this->getNombreDoc().' registrada con exito.', 'redirect' => route('agrupacion-sala.edit', ['id' => $AS_entrada->id_movimiento])]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar '.$this->getNombreDoc().', contacte con Soporte Técnico.']);
        }  
        
        
    }

    public function edit($id)
    {
        $AC_entrada = Movimiento::findOrFail($id);
        $data['action'] = 'editar';
        $AC_entrada->correlativo = $this->getDocEntrada(). '-' . str_pad($AC_entrada->correlativo, 5, '0', STR_PAD_LEFT);
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $data['AC_entrada'] = $AC_entrada;

        $AC_salida = Movimiento::where('fk_doc_afecta', $AC_entrada->id_movimiento)
            ->where('tipo_mov', $this->getTipoSalida())
            ->first();

        $AC_salida->correlativo = $this->getDocSalida(). '-' . str_pad($AC_salida->correlativo, 5, '0', STR_PAD_LEFT);
        $data['AC_salida'] = $AC_salida;

        if($AC_entrada->estado != 'A') {
            return redirect()->route('agrupacion-sala.index')->with('error', 'No se puede editar una '.$this->getNombreDoc().' que no está activa.');
        }

        return view('movimientos.agrupacion-sala.mvAgrupacionSalas', ['data' => $data, 'menu' => $this->getMenu()]);
    }

    public function obtenerMeses()
    {
        return $this->obtenerMesesTipoDoc($this->getDocEntrada());
    }

    public function datatable(Request $request)
    {
        $AS_entrada = Movimiento::query()
            ->select('id_movimiento', 
                DB::raw('CONCAT("'.$this->getDocEntrada().'-", LPAD(correlativo, 5, "0")) AS correlativo'),
                DB::raw('DATE_FORMAT(fecha_ingreso, "%d/%m/%Y") AS fecha_ingreso'), 
                'cacastero', 
                'comentario', 
                'estado', 
                DB::raw('CONCAT(trabajadores.nombre1, " ", trabajadores.nombre2, " ", trabajadores.apellido1, " ", trabajadores.apellido2) AS nombre_cacastero'))
            ->join('trabajadores', 'inv_movimiento.cacastero', '=', 'trabajadores.id_trabajador')
            ->where('tipo_doc', $this->getDocEntrada())
            ->orderBy('id_movimiento', 'desc');

        if ($request->filled('mes')) {
            $AS_entrada->whereMonth('fecha_ingreso', $request->mes);
        }

        return datatables()->of($AS_entrada)
            ->filterColumn('nombre_cacastero', function($query, $keyword) {
                $sql = 'LOWER(CONCAT(trabajadores.nombre1, " ", trabajadores.nombre2, " ", trabajadores.apellido1, " ", trabajadores.apellido2)) LIKE ?';
                $query->whereRaw($sql, ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('correlativo', function($query, $keyword) {
                $sql = 'LOWER(CONCAT("'.$this->getDocEntrada().'-", LPAD(correlativo, 5, "0"))) LIKE ?';
                $query->whereRaw($sql, ["%" . strtolower($keyword) . "%"]);
            })
            ->addColumn('acciones', function($AS_entrada) {

                $html = '<div class="flex justify-evenly items-center">';

                if($AS_entrada->estado == 'A') {
                    $html .= '<a data-id="'.$AS_entrada->id_movimiento.'" href ="'. route('agrupacion-sala.edit', ['id' => $AS_entrada->id_movimiento]) .'" class="btn_editar btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></a>';
                    $html .= '<button data-id="'.$AS_entrada->id_movimiento.'" class="btn_eliminar btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>';
                }else if($AS_entrada->estado == "I"){
                    $html .= '<a data-id="'.$AS_entrada->id_movimiento.'" data-estado="'.$AS_entrada->estado.'" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
                    $html .= '<button data-id="'.$AS_entrada->id_movimiento.'" class="btnAnular btn btn-sm btn-danger cursor-pointer"> <i class="bx bx-revision text-2xl text-red-600 hover:text-red-400"></i> </button>';
                }else{
                    $html .= '<a data-id="'.$AS_entrada->id_movimiento.'" data-estado="'.$AS_entrada->estado.'" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
                }

                $html .= '</div>';

                return $html;
            })->rawColumns(['acciones'])
            ->toJson();
    }

    public function destroy($id)
    {

        DB::beginTransaction();

        $AC_entrada = Movimiento::find($id);
        if (!$AC_entrada) {
            return response()->json(['success' => false, 'message' =>  $this->getNombreDoc().' no encontrada.']);
        }

        if ($AC_entrada->estado !== 'A') {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una '.$this->getNombreDoc().' que no está activa.']);
        }

        // Borrar los detalles asociados a la Nota de Pieza
        $detalles = Detalle::where('fk_movimiento', $AC_entrada->id_movimiento)->get();
        foreach ($detalles as $detalle) {
            $detalle->delete();
        }

        $AC_salida = Movimiento::where('fk_doc_afecta', $AC_entrada->id_movimiento)
            ->where('tipo_mov', $this->getTipoSalida())
            ->first();

        $detalles_salida = Detalle::where('fk_movimiento', $AC_salida->id_movimiento)->get();
        foreach ($detalles_salida as $detalle_salida) {
            $detalle_salida->delete();
        }

        try {
            $AC_entrada->delete();
            $AC_salida->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' =>  $this->getNombreDoc().' eliminada con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar '.$this->getNombreDoc().', contacte con Soporte Técnico.']);
        }
    }

    public function guardarDetalle(Request $request){
        $request->validate([
            'id_sala' => 'required|integer',
            'cantidad' => 'required|integer|min:1'
        ]);

        $id_enca = $request->id_enca;
        $id_sala = $request->id_sala;
        $cantidad = $request->cantidad;

        $validarSala = Salas::find($id_sala);

        if (!$validarSala) {
            return response()->json(['success' => false, 'message' => 'Sala no encontrada.']);
        }

        $precio = DB::table('salas')->where('id_salas', $id_sala)->value('costo_cacastero');

        $precioTotal = round($cantidad * $precio, 2);

        $sala = new SalasController();
        $piezas = $sala->obtenerPiezas($request->id_sala);

        $AC_salida = Movimiento::where('fk_doc_afecta', $id_enca)
            ->where('tipo_mov', $this->getTipoSalida())
            ->first();

        try {
            DB::beginTransaction();

            $detalle = new Detalle();
            $detalle->fk_movimiento = $id_enca; // ID del movimiento
            $detalle->fk_pieza = 0; // ID de la pieza
            $detalle->fk_sala = $id_sala; // Asignar sala
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

    public function renderDetalle($id_enca)
    {
        $data = [];
        $AS_entrada = Movimiento::find($id_enca);
        $AC_salida = Movimiento::where('fk_doc_afecta', $id_enca)
            ->where('tipo_mov', $this->getTipoSalida())
            ->first();

        if (!$AS_entrada) {
            return response()->json(['success' => false, 'message' => 'Movimiento de entrada no encontrado.']);
        }

        if ($AS_entrada->estado != 'A') {
            return response()->json(['success' => false, 'message' => 'No se puede ver los detalles de una '.$this->getNombreDoc().' que no está activa.']);
        }

        if (!$AC_salida) {
            return response()->json(['success' => false, 'message' => 'Movimiento de salida no encontrado.']);
        }

        $detalles_entrada = Detalle::with('sala')
            ->where('fk_movimiento', $AS_entrada->id_movimiento)
            ->get();

        $disponibilidad = [];

        foreach ($detalles_entrada as $sala) {

            $detalles_salida = Detalle::with('pieza')->where('fk_movimiento', $AC_salida->id_movimiento)->where('fk_detalle_prin', $sala->id_detalle)->get();

            $data['detalles'][$sala->id_detalle] = [
                'sala' => $sala,
                'salidas' => $detalles_salida
            ];

            $detallesRequeridos = new SalasController();
            $detallesRequeridos = $detallesRequeridos->obtenerPiezas($sala->fk_sala);

            $data['detalles'][$sala->id_detalle]['requeridos'] = $detallesRequeridos;

            $salas_posibles = [];

            foreach ($detallesRequeridos as $pieza){
                $cacastero = $AC_salida->cacastero; 
                $piezaService = new PiezasServices();
                $valor = $piezaService->disPiezaByTrabajador($pieza->id_pieza, $cacastero);

                $disponibilidad[$pieza->id_pieza] = [
                    'disponibilidad' => $valor,
                    'pieza' => $pieza->id_pieza
                ];
                //validar si ya armo al menos una sala
                $detalle_pieza = collect($detalles_salida)->where('fk_pieza', $pieza->id_pieza)->first();

                $unidades_entregadas = $detalle_pieza ? $detalle_pieza->unidades : 0;

                $cantidad_por_sala = $pieza->cantidad; // esto viene del objeto 'requerido'
                if ($cantidad_por_sala > 0) {
                    $salas_con_esta_pieza = floor($unidades_entregadas / $cantidad_por_sala);
                } else {
                    $salas_con_esta_pieza = 0;
                }

                $salas_posibles[] = $salas_con_esta_pieza;
            }

            $armadas = count($salas_posibles) > 0 ? min($salas_posibles) : 0;
            $data['detalles'][$sala->id_detalle]['armadas'] = $armadas;

        }
        $data['disponibilidad'] = $disponibilidad;
        return  response()->json(['success' => true, 'data' => $data]);
    }

    public function sumar(Request $request)
    {
        $id = $request->id;
        $pieza = $request->pieza;
        $detalle = $request->detalle;
        try{
            DB::beginTransaction();
            $detalle_entrada = Detalle::find($detalle);
            if(!$detalle_entrada) {
                return response()->json(['success' => false, 'message' => 'Detalle de entrada no encontrado.']);
            }
            $enca_salida = Movimiento::where('fk_doc_afecta', $detalle_entrada->fk_movimiento)->first();
            if(!$enca_salida) {
                return response()->json(['success' => false, 'message' => 'Movimiento de salida no encontrado.']);
            }
            $sala = Salas::find($detalle_entrada->fk_sala);
            if(!$sala) {
                return response()->json(['success' => false, 'message' => 'Sala no encontrada .']);
            }
            $requerido = DB::table('salas_piezas')->where('id_sala', $sala->id_salas)->where('id_pieza', $pieza)->first()->cantidad;
            if($detalle_entrada->unidades > 1) {
                $requerido = $requerido * $detalle_entrada->unidades;
            }
            $detalle_salida = Detalle::find($id);

            $cacastero = $enca_salida->cacastero; // Obtener el ID del carpintero del movimiento de salida
            $piezaService = new PiezasServices();

            $valor = $piezaService->disPiezaByTrabajador($pieza, $cacastero);

            if($valor <= 0){
                return response()->json(['success' => false, 'message' => 'No hay piezas disponibles para agregar a la sala.']);
            }

            if($detalle_salida){
                
                if($requerido <= $detalle_salida->unidades){
                    return response()->json(['success' => false, 'message' => 'No se puede agregar más piezas a la sala, ya se ha alcanzado el máximo requerido.']);
                }else{
                    $detalle_salida->unidades += 1;
                    $detalle_salida->costo_total = ($detalle_salida->unidades * $detalle_salida->costo_unitario);
                    $detalle_salida->save();
                    $movimiento = Movimiento::find($enca_salida->id_movimiento);
                    $total = $movimiento->totalizar();
                    Movimiento::where('id_movimiento', $enca_salida->id_movimiento)->update(['total' => $total]);
                }
            }else{                
                $detalle_salida = new Detalle();
                $detalle_salida->fk_movimiento =  $enca_salida->id_movimiento; // ID del movimiento
                $detalle_salida->fk_pieza = $pieza; // ID de la pieza
                $detalle_salida->fk_sala = null; // Asignar sala
                $detalle_salida->unidades = 1; // Cantidad de piezas
                $detalle_salida->costo_unitario = Pieza::where('id_pieza', $pieza)->first()->costo_cacastero; // Costo unitario de la pieza
                $detalle_salida->costo_total = $detalle_salida->costo_unitario; // Costo total de la pieza
                $detalle_salida->fk_detalle_prin = $detalle_entrada->id_detalle; // Asignar el detalle de entrada principal
                $detalle_salida->save();
                $movimiento = Movimiento::find($enca_salida->id_movimiento);
                $total = $movimiento->totalizar();
                Movimiento::where('id_movimiento', $enca_salida->id_movimiento)->update(['total' => $total]);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pieza agregada con éxito.', 'total' => $total]);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function restar(Request $request)
    {
        $id = $request->id;
        $pieza = $request->pieza;
        $detalle = $request->detalle;
        try{
            DB::beginTransaction();
           
            $detalle_entrada = Detalle::find($detalle);
            if(!$detalle_entrada) {
                return response()->json(['success' => false, 'message' => 'Detalle de entrada no encontrado.']);
            }
            $enca_salida = Movimiento::where('fk_doc_afecta', $detalle_entrada->fk_movimiento)->first();
            if(!$enca_salida) {
                return response()->json(['success' => false, 'message' => 'Movimiento de salida no encontrado.']);
            }
            $sala = Salas::find($detalle_entrada->fk_sala);
            if(!$sala) {
                return response()->json(['success' => false, 'message' => 'Sala no encontrada .']);
            }
            $requerido = DB::table('salas_piezas')->where('id_sala', $sala->id_salas)->where('id_pieza', $pieza)->first()->cantidad;
            if($detalle_entrada->unidades > 1) {
                $requerido = $requerido * $detalle_entrada->unidades;
            }
            $detalle_salida = Detalle::find($id);

            if(!$detalle_salida) {
                return response()->json(['success' => false, 'message' => 'No puede ser negativo el valor en piezas.']);
            }

            if($detalle_salida->unidades == 1){
                $detalle_salida->delete();
                $movimiento = Movimiento::find($enca_salida->id_movimiento);
                $total = $movimiento->totalizar();
                Movimiento::where('id_movimiento', $enca_salida->id_movimiento)->update(['total' => $total]);
            }else if($detalle_salida->unidades > 1){
                $detalle_salida->unidades -= 1;
                $detalle_salida->costo_total = ($detalle_salida->costo_unitario * $detalle_salida->unidades);
                $detalle_salida->save();
                $movimiento = Movimiento::find($enca_salida->id_movimiento);
                $total = $movimiento->totalizar();
                Movimiento::where('id_movimiento', $enca_salida->id_movimiento)->update(['total' => $total]);
            }else{
                return response()->json(['success' => false, 'message' => 'No se puede restar más piezas a la sala, ya se ha alcanzado el mínimo requerido.']);
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pieza restada con éxito.', 'total' => $total]);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function eliminarDetalle($id_det)
    {
        $id = $id_det;
        try {
            DB::beginTransaction();
            $detalle = Detalle::find($id);
            if (!$detalle) {
                return response()->json(['success' => false, 'message' => 'Detalle no encontrado.']);
            }

            $enca_salida = Movimiento::where('fk_doc_afecta', $detalle->fk_movimiento)->first();
            if (!$enca_salida) {
                return response()->json(['success' => false, 'message' => 'Movimiento de salida no encontrado.']);
            }

            foreach ($enca_salida->detalles as $detalle_salida) {
                if ($detalle_salida->fk_detalle_prin == $detalle->id_detalle) {
                    $detalle_salida->delete();
                }
            }

            $detalle->delete();

            $movimiento = Movimiento::find($enca_salida->id_movimiento);
            $total = $movimiento->totalizar();
            Movimiento::where('id_movimiento', $enca_salida->id_movimiento)->update(['total' => $total]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Detalle eliminado con éxito.', 'total' => $total]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el detalle: ' . $e->getMessage()]);
        }
    }

    public function renderResumen($id_enca){

        $data = [];

        $entrada = Movimiento::find($id_enca);
        if(!$entrada) {
            return response()->json(['success' => false, 'message' => 'Movimiento de entrada no encontrado.']);
        }

        $salida = Movimiento::where('fk_doc_afecta', $id_enca)
            ->first();

        if(!$salida) {
            return response()->json(['success' => false, 'message' => 'Movimiento de salida no encontrado.']);
        }

        $data['entrada'] = $entrada;
        $data['salida'] = $salida;

        $detalles_entrada = Detalle::with('sala')
            ->where('fk_movimiento', $entrada->id_movimiento)
            ->get();

        $data['detalles_entrada'] = $detalles_entrada;

        $detalles_salida = Detalle::with('pieza')
            ->where('fk_movimiento', $salida->id_movimiento)
            ->get();

        $data['detalles_salida'] = $detalles_salida;

        $total_entrada = $entrada->totalizar();
        $total_salida = $salida->totalizar();

        Movimiento::where('id_movimiento', $entrada->id_movimiento)->update(['total' => $total_entrada]);
        Movimiento::where('id_movimiento', $salida->id_movimiento)->update(['total' => $total_salida]);

        return response()->json(['success' => true, 'data' => $data]);
    }


}