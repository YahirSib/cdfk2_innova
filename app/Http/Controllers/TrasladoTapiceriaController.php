<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Trabajadores;
use Illuminate\Support\Facades\DB;
use App\Models\Pieza;
use App\Models\Detalle;
use App\Services\MenuService;
use Barryvdh\DomPDF\Facade\Pdf;

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
            ->select('id_movimiento', 
                DB::raw('CONCAT("'.$this->getTipoDoc().'-", LPAD(correlativo, 5, "0")) AS correlativo'),
                DB::raw('DATE_FORMAT(fecha_ingreso, "%d/%m/%Y") AS fecha_ingreso'), 
                'cacastero', 
                'comentario', 
                'estado', 
                DB::raw('CONCAT(trabajadores.nombre1, " ", trabajadores.nombre2, " ", trabajadores.apellido1, " ", trabajadores.apellido2) AS nombre_cacastero'))
            ->join('trabajadores', 'inv_movimiento.cacastero', '=', 'trabajadores.id_trabajador')
            ->where('tipo_doc', $this->getTipoDoc())
            ->orderBy('id_movimiento', 'desc');

        if ($request->filled('mes')) {
            $trasladoTapiceria->whereMonth('fecha_ingreso', $request->mes);
        }

        return datatables()->of($trasladoTapiceria)
            ->filterColumn('nombre_cacastero', function($query, $keyword) {
                $sql = 'LOWER(CONCAT(trabajadores.nombre1, " ", trabajadores.nombre2, " ", trabajadores.apellido1, " ", trabajadores.apellido2)) LIKE ?';
                $query->whereRaw($sql, ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('correlativo', function($query, $keyword) {
                $sql = 'LOWER(CONCAT("'.$this->getTipoDoc().'-", LPAD(correlativo, 5, "0"))) LIKE ?';
                $query->whereRaw($sql, ["%" . strtolower($keyword) . "%"]);
            })
            ->addColumn('acciones', function($trasladoTapiceria) {

                $html = '<div class="flex justify-evenly items-center">';

                if($trasladoTapiceria->estado == 'A') {
                    $html .= '<a data-id="'.$trasladoTapiceria->id_movimiento.'" href ="'. route('traslado-tapiceria.edit', ['id' => $trasladoTapiceria->id_movimiento]) .'" class="btn_editar btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></a>';
                    $html .= '<button data-id="'.$trasladoTapiceria->id_movimiento.'" class="btn_eliminar btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>';
                }else if($trasladoTapiceria->estado == "I"){
                    $html .= '<a data-id="'.$trasladoTapiceria->id_movimiento.'" data-estado="'.$trasladoTapiceria->estado.'" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
                    $html .= '<button data-id="'.$trasladoTapiceria->id_movimiento.'" class="btnAnular btn btn-sm btn-danger cursor-pointer"> <i class="bx bx-revision text-2xl text-red-600 hover:text-red-400"></i> </button>';
                }else{
                    $html .= '<a data-id="'.$trasladoTapiceria->id_movimiento.'" data-estado="'.$trasladoTapiceria->estado.'" class="btnReporte btn btn-sm btn-primary cursor-pointer"><i class="bx bxs-file-pdf text-2xl text-blue-600 hover:text-blue-400"></i></a>';
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
        $trasladoTapiceria->correlativo = $this->getTipoDoc(). '-' . str_pad($trasladoTapiceria->correlativo, 5, '0', STR_PAD_LEFT);
        $data['carpintero'] = Trabajadores::all()->where('tipo', 2);
        $data['trasladoTapiceria'] = $trasladoTapiceria;

        if($trasladoTapiceria->estado != 'A') {
            return redirect()->route('traslado-tapiceria.index')->with('error', 'No se puede editar una '.$this->getNombreDoc().' que no está activa.');
        }

        return view('movimientos.traslado-tapiceria.mvCargarTraslado', ['data' => $data, 'menu' => $this->getMenu()]);
    }

    public function destroy($id)
    {
        $trasladoTapiceria = Movimiento::find($id);
        if (!$trasladoTapiceria) {
            return response()->json(['success' => false, 'message' =>  $this->getNombreDoc().' no encontrada.']);
        }

        if ($trasladoTapiceria->estado !== 'A') {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una '.$this->getNombreDoc().' que no está activa.']);
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
            return response()->json(['success' => true, 'message' =>  $this->getNombreDoc().' eliminada con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar '.$this->getNombreDoc().', contacte con Soporte Técnico.']);
        }
    }

     public function imprimirHistorico($id)
    {
        $data = [];

        $trasladoTapiceria = Movimiento::find($id);

        if($trasladoTapiceria->estado == "I"){
            $data['title'] = strtoupper($this->nombre_doc . ' HISTORICA');
        }else{
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
        try{
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
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al generar el PDF, contacte con Soporte Técnico.']);
        }
    }

    public function renderPDF($trasladoTapiceria, $detalles, $total, $totalUnidades, $data){
        ini_set('memory_limit', '512M');
        $pdf = Pdf::loadView('movimientos.traslado-tapiceria.pdfTT', compact('trasladoTapiceria', 'detalles', 'total', 'totalUnidades', 'data'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('traslado_tapiceria' . $trasladoTapiceria->correlativo . '.pdf');
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
        $data['correlativo'] = $this->getTipoDoc(). '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
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
            return response()->json(['success' => true, 'message' =>  $this->getNombreDoc().' registrada con exito.', 'redirect' => route('traslado-tapiceria.edit', ['id' => $trasladoTapiceria->id_movimiento])]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar '.$this->getNombreDoc().', contacte con Soporte Técnico.']);
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
            return response()->json(['success' => true, 'message' =>  $this->getNombreDoc().' actualizada con exito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar '.$this->getNombreDoc().', contacte con Soporte Técnico.']);
        }
    }

}
