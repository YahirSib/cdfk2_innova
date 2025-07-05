<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Salas;
use DataTables;

class SalasController extends Controller
{
    public function index()
    {
        return view('mantenimientos.mtSalas', ['menu' => $this->getMenu()]);
    }

    public function store(Request $request)
    {
        $salas = new Salas();
        $salas->codigo = $request->input('codigo');
        $salas->nombre = $request->input('nombre');
        $salas->descripcion = $request->input('descripcion');
        $salas->costo_cacastero = $request->input('costo_cacastero');
        $salas->costo_tapicero = $request->input('costo_tapicero');
        $salas->estado = $request->input('estado');

        $request->validate([
            'codigo' => 'required|string|max:225',
            'nombre' => 'required|string|max:225',
            'descripcion' => 'nullable|string|max:225',
            'costo_cacastero' => 'required|numeric|min:0',
            'costo_tapicero' => 'required|numeric|min:0',
            'estado' => 'required|integer|min:0|in:1,2',
        ],
        [
            'codigo.required' => 'El codigo es obligatorio',
            'nombre.required' => 'El nombre es obligatorio',
            'costo_cacastero.required' => 'El costo por cacastero es obligatorio',
            'costo_tapicero.required' => 'El costo por tapicero es obligatorio',
            'costo_cacastero.numeric' => 'El costo por cacastero debe ser un número',
            'costo_tapicero.numeric' => 'El costo por tapicero debe ser un número',
            'costo_cacastero.min' => 'El costo por cacastero debe ser mayor o igual a 0',
            'costo_tapicero.min' => 'El costo por tapicero debe ser mayor o igual a 0',
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado debe ser seleccionado',
        ]);
        $codigoExiste = Salas::where('codigo', $salas->codigo)->first();
        if($codigoExiste){
            throw new \Exception('El codigo ingresado ya existe.');
        }

        $salas->codigo = strtoupper($salas->codigo);
        $salas->nombre = strtoupper($salas->nombre);
        $salas->descripcion = strtoupper($salas->descripcion);
        $salas->costo_cacastero = $salas->costo_cacastero;
        $salas->costo_tapicero = $salas->costo_tapicero;
        $salas->estado = $salas->estado;

        try {
            DB::beginTransaction();
            $salas->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sala registrada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar la sala. Contacte con soporte tecnico.']);
        }
    }

    public function edit($id)
    {
        $salas = Salas::find($id);
        if (!$salas) {
            return response()->json(['success' => false, 'message' => 'Sala no encontrada.']);
        }
        return response()->json(['success' => true, 'data' => $salas]);
    }

    public function update(Request $request, $id)
    {
        $salas = Salas::find($id);
        if (!$salas) {
            return response()->json(['success' => false, 'message' => 'Sala no encontrada.']);
        }

        $request->validate([
            'codigo' => 'required|string|max:225',
            'nombre' => 'required|string|max:225',
            'descripcion' => 'nullable|string|max:225',
            'costo_cacastero' => 'required|numeric|min:0',
            'costo_tapicero' => 'required|numeric|min:0',
            'estado' => 'required|integer|min:0|in:1,2',
        ],
        [
            'codigo.required' => 'El codigo es obligatorio',
            'nombre.required' => 'El nombre es obligatorio',
            'costo_cacastero.required' => 'El costo por cacastero es obligatorio',
            'costo_tapicero.required' => 'El costo por tapicero es obligatorio',
            'costo_cacastero.numeric' => 'El cosresources/js/mantenimientos/mtPiezas.jsto por cacastero debe ser un número',
            'costo_tapicero.numeric' => 'El costo por tapicero debe ser un número',
            'costo_cacastero.min' => 'El costo por cacastero debe ser mayor o igual a 0',
            'costo_tapicero.min' => 'El costo por tapicero debe ser mayor o igual a 0',
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado debe ser seleccionado',
        ]);

        $codigoExiste = Salas::where('codigo', $salas->codigo)->where('id_salas', '<>', $id)->first();
        if($codigoExiste){
            throw new \Exception('El codigo ingresado ya existe.');
        }

        $salas->codigo = strtoupper($request->input('codigo'));
        $salas->nombre = strtoupper($request->input('nombre'));
        $salas->descripcion = strtoupper($request->input('descripcion'));
        $salas->costo_cacastero = $request->input('costo_cacastero');
        $salas->costo_tapicero = $request->input('costo_tapicero');
        $salas->estado = $request->input('estado');

        try {
            DB::beginTransaction();
            $salas->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sala actualizada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar la sala.']);
        }  
    }

    public function destroy($id)
    {
        $salas = Salas::find($id);
        if (!$salas) {
            return response()->json(['success' => false, 'message' => 'Sala no encontrada.']);
        }

        $validar = DB::table('salas_piezas')->select('id_relacion')->where('id_sala', $salas->id_salas)->first();

        if($validar) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar la sala porque tiene piezas asociadas.']);
        }

        try {
            DB::beginTransaction();
            $salas->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sala eliminada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar la sala.']);
        }
    }

    public function datatable(){
        $salas = Salas::select([
            'id_salas', 
            'codigo',
            'nombre',
            'estado',
            'existencia',
        ]);
        
        return datatables()->of($salas)
            ->addColumn('acciones', function($sala) {
                return '
                
            <div class="flex justify-evenly items-center gap-2">
                <button id="btn_editar" data_id="'.$sala->id_salas.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></button>

                <button id="btn_eliminar" data_id="'.$sala->id_salas.'" class="btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>
                
                <button id="btn_ver" data_id="'.$sala->id_salas.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-blue-600 hover:text-blue-400 bx bxs-info-circle bx-rotate-180"></i>  </button>

                <button id="btn_settings" data_id="'.$sala->id_salas.'" data_sala="'.$sala->nombre.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-gray-600 hover:text-gray-400 bx bxs-cog bx-rotate-180"></i>  </button>
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
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->codigo . ' - ' . $item->nombre,
                ];
            });

        return response()->json($piezas);
    }

    public function savePiezas(Request $request)
    {

        $salas = Salas::find($request->id_sala);
        if (!$salas) {
            return response()->json(['success' => false, 'message' => 'Sala no encontrada.']);
        }

        $piezas = $request->id_pieza;
        if (empty($piezas)) {
            return response()->json(['success' => false, 'message' => 'Debe seleccionar al menos una pieza.']);
        }

        $cantidad = $request->cantidad;
        if(empty($cantidad) || !is_numeric($cantidad) || $cantidad <= 0) {
            return response()->json(['success' => false, 'message' => 'La cantidad debe ser un número mayor a 0.']);
        }

        $validacion = DB::table('salas_piezas')->select('id_relacion')->where('id_sala', $salas->id_salas)->where('id_pieza', $request->id_pieza);

        if(!empty($validacion->first())) {
            return response()->json(['success' => false, 'message' => 'La pieza ya se encuentra registrada en la sala.']);
        }

        try {
            DB::beginTransaction();
            DB::table('salas_piezas')->insert([
                'id_sala' => $salas->id_salas,
                'id_pieza' => $piezas,
                'cantidad' => $cantidad
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Piezas guardadas correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al guardar las piezas.']);
        }
    }

    public function getPiezasBySala(Request $request)
    {
        $id_sala = $request->id_sala;
        $piezas = DB::table('salas_piezas')
            ->join('piezas', 'salas_piezas.id_pieza', '=', 'piezas.id_pieza')
            ->select('salas_piezas.id_relacion', 'piezas.codigo' , 'piezas.nombre', 'salas_piezas.cantidad')
            ->where('salas_piezas.id_sala', $id_sala)
            ->get();

        if($piezas->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontraron piezas para esta sala.']);
        }else{
            return response()->json(['success' => true, 'data' => $piezas]);
        }
    }

    public function deletePiezaBySala($id)
    {
        $id_relacion = $id;
        $pieza = DB::table('salas_piezas')->where('id_relacion', $id_relacion)->first();

        if (!$pieza) {
            return response()->json(['success' => false, 'message' => 'Pieza no encontrada en la sala.']);
        }

        try {
            DB::beginTransaction();
            DB::table('salas_piezas')->where('id_relacion', $id_relacion)->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pieza eliminada correctamente de la sala.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar la pieza de la sala.']);
        }
    }

    public function updatePiezaBySala(Request $request)
    {
        $id_relacion = $request->id_relacion;
        $cantidad = $request->cantidad;

        if(empty($cantidad) || !is_numeric($cantidad) || $cantidad <= 0) {
            return response()->json(['success' => false, 'message' => 'La cantidad debe ser un número mayor a 0.']);
        }

        try {
            DB::beginTransaction();
            DB::table('salas_piezas')->where('id_relacion', $id_relacion)->update(['cantidad' => $cantidad]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cantidad actualizada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar la cantidad de la pieza en la sala.']);
        }
    }


}
