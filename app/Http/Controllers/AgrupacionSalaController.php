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


}
