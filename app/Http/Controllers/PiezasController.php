<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Pieza;
use DataTables; 

class PiezasController extends Controller
{
    public function index()
    {
        return view('mantenimientos.mtPiezas', ['menu' => $this->getMenu()]);
    }

    public function store(Request $request)
    {
        $pieza = new Pieza();
        $pieza->codigo = $request->input('codigo');
        $pieza->nombre = $request->input('nombre');
        $pieza->descripcion = $request->input('descripcion');
        $pieza->estado = $request->input('estado');
        $pieza->costo_cacastero = $request->input('costo_cacastero');
        $pieza->costo_tapicero = $request->input('costo_tapicero');

        $request->validate([
            'codigo' => 'required|string|max:225',
            'nombre' => 'required|string|max:225',
            'descripcion' => 'nullable|string|max:225',
            'estado' => 'required|integer|min:0|in:1,2',
            'costo_cacastero' => 'required|numeric|min:0',
            'costo_tapicero' => 'required|numeric|min:0',
            'individual_valor' => 'required|integer|min:0|in:0,1',
        ],
        [
            'codigo.required' => 'El codigo es obligatorio',
            'nombre.required' => 'El nombre es obligatorio',
            'estado.in' => 'El estado debe ser seleccionado',
            'costo_cacastero.required' => 'El costo por cacastero es obligatorio',
            'costo_tapicero.required' => 'El costo por tapicero es obligatorio',
            'costo_cacastero.numeric' => 'El costo por cacastero debe ser un número',
            'costo_tapicero.numeric' => 'El costo por tapicero debe ser un número',
            'costo_cacastero.min' => 'El costo por cacastero debe ser mayor o igual a 0',
            'costo_tapicero.min' => 'El costo por tapicero debe ser mayor o igual a 0',
            'individual_valor.required' => 'El campo individual es obligatorio',
            'individual_valor.integer' => 'El campo individual debe ser un número entero',
        ]);

        $codigoExiste = Pieza::where('codigo', $pieza->codigo)->first();
        if($codigoExiste){
            throw new \Exception('El codigo ingresado ya existe.');
        }

        $pieza->codigo = strtoupper($pieza->codigo);
        $pieza->nombre = strtoupper($pieza->nombre);
        $pieza->descripcion = strtoupper($pieza->descripcion);
        $pieza->estado = $pieza->estado;
        $pieza->costo_cacastero = $pieza->costo_cacastero;
        $pieza->costo_tapicero = $pieza->costo_tapicero;
        $pieza->individual = $request->input('individual_valor') == 1 ? 1 : 0;

        if($pieza->save()){
            return response()->json(['success' => true, 'message' => 'Pieza creada exitosamente.']);
        }else{
             throw new \Exception('Error al crear la pieza.');
        }

    }

    public function edit($id)
    {
        $pieza = Pieza::find($id);
        if (!$pieza) {
            return response()->json(['success' => false, 'message' => 'Pieza no encontrada.']);
        }
        return response()->json(['success' => true, 'data' => $pieza]);
    }

    public function update(Request $request)
    {
        $pieza = Pieza::find($request->input('id_pieza'));
        if (!$pieza) {
            return response()->json(['success' => false, 'message' => 'Pieza no encontrada.']);
        }
        $pieza->codigo = $request->input('codigo');
        $pieza->nombre = $request->input('nombre');
        $pieza->descripcion = $request->input('descripcion');
        $pieza->estado = $request->input('estado');
        $pieza->costo_cacastero = $request->input('costo_cacastero');
        $pieza->costo_tapicero = $request->input('costo_tapicero');
        $request->validate([
            'codigo' => 'required|string|max:225',
            'nombre' => 'required|string|max:225',
            'descripcion' => 'nullable|string|max:225',
            'estado' => 'required|integer|min:0|in:1,2',
            'costo_cacastero' => 'required|numeric|min:0',
            'costo_tapicero' => 'required|numeric|min:0',
            'individual_valor' => 'required|integer|min:0|in:0,1',
        ],
        [
            'codigo.required' => 'El codigo es obligatorio',
            'nombre.required' => 'El nombre es obligatorio',
            'estado.in' => 'El estado debe ser seleccionado',
            'costo_cacastero.required' => 'El costo por cacastero es obligatorio',
            'costo_tapicero.required' => 'El costo por tapicero es obligatorio',
            'costo_cacastero.numeric' => 'El costo por cacastero debe ser un número',
            'costo_tapicero.numeric' => 'El costo por tapicero debe ser un número',
            'costo_cacastero.min' => 'El costo por cacastero debe ser mayor o igual a 0',
            'costo_tapicero.min' => 'El costo por tapicero debe ser mayor o igual a 0',
            'individual_valor.required' => 'El campo individual es obligatorio',
            'individual_valor.integer' => 'El campo individual debe ser un número entero',
        ]);
        $pieza->codigo = strtoupper($pieza->codigo);
        $pieza->nombre = strtoupper($pieza->nombre);
        $pieza->descripcion = strtoupper($pieza->descripcion);
        $pieza->estado = $pieza->estado;
        $pieza->costo_cacastero = $pieza->costo_cacastero;
        $pieza->costo_tapicero = $pieza->costo_tapicero;
        $pieza->individual = $request->input('individual_valor') == 1 ? 1 : 0;

        if($pieza->save()){
            return response()->json(['success' => true, 'message' => 'Pieza actualizada exitosamente.']);  
        }else{
            throw new \Exception('Error al actualizar la pieza.');
        }
    }

    public function destroy($id)
    {
        $pieza = Pieza::find($id);
        if (!$pieza) {
            return response()->json(['success' => false, 'message' => 'Pieza no encontrada.']);
        }

        $validacion = DB::table('salas_piezas')
            ->where('id_pieza', $pieza->id_pieza)
            ->exists();
            
        if ($validacion) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar la pieza porque está asociada a una sala.']);
        }

        if ($pieza->delete()) {
            return response()->json(['success' => true, 'message' => 'Pieza eliminada exitosamente.']);
        } else {
            throw new \Exception('Error al eliminar la pieza.');
        }
    }

    public function datatable(){
        $piezas = Pieza::select([
            'id_pieza', 
            'codigo',
            'nombre',
            'estado',
            'existencia',
            'individual'
        ]);
        
        return datatables()->of($piezas)
            ->addColumn('acciones', function($pieza) {
                return '
                
            <div class="flex justify-evenly items-center">
                <button id="btn_editar" data_id="'.$pieza->id_pieza.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></button>

                <button id="btn_eliminar" data_id="'.$pieza->id_pieza.'" class="btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>
                
                <button id="btn_ver" data_id="'.$pieza->id_pieza.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-blue-600 hover:text-blue-400 bx bxs-info-circle bx-rotate-180"></i>  </button>
            </div>';
            })->rawColumns(['acciones'])
            ->toJson();
    }

    public function getPiezas(Request $request)
    {
        $term = $request->val;
        $piezas = DB::table('piezas')
            ->select('id_pieza as id', 'codigo', 'nombre')
            ->where('codigo', 'like', "%{$term}%")
            ->orWhere('nombre', 'like', "%{$term}%")
            ->where('estado', 1)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->codigo . ' - ' . $item->nombre,
                ];
            });

        return response()->json($piezas);
    }

    public function getPiezasDisponibles(Request $request){
        $term = $request->val;
        $id_trabajador = $request->id_trabajador;

        $piezas = DB::table('piezas')
            ->select('id_pieza as id', 'codigo', 'nombre')
            ->where(function ($query) use ($term) {
                $query->where('codigo', 'like', "%{$term}%")
                    ->orWhere('nombre', 'like', "%{$term}%");
            })
            ->where('estado', 1)
            ->get()
            ->map(function ($item) use ($id_trabajador) {
                $disponibilidad = (new \App\Services\PiezasServices())->disPiezaByTrabajador($item->id, $id_trabajador);
                
                return [
                    'id' => $item->id,
                    'text' => $item->codigo . ' - ' . $item->nombre,
                    'disponibilidad' => $disponibilidad, 
                ];
            })
            ->values();

        return response()->json($piezas);
    }


}
