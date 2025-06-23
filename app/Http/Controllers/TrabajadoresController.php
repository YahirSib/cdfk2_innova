<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trabajadores;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use DataTables; 

class TrabajadoresController extends Controller
{
    public function index()
    {
        $menu = (new MenuController)->obtenerMenu();
        return view('mantenimientos.mtTrabajadores', ['menu' => $menu]);
    }

    public function store(Request $request)
    {
        $trabajador = new Trabajadores();
        $trabajador->nombre1 = $request->input('nombre1');
        $trabajador->nombre2 = $request->input('nombre2');
        $trabajador->apellido1 = $request->input('apellido1');
        $trabajador->apellido2 = $request->input('apellido2');
        $trabajador->edad = $request->input('edad');
        $trabajador->tipo = $request->input('tipo');
        $trabajador->dui = $request->input('dui');
        $trabajador->telefono = $request->input('telefono');

        // Validar los datos
        $request->validate([
            'nombre1' => 'required|string|max:255',
            'nombre2' => 'nullable|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'nullable|string|max:255',
            'edad' => 'required|integer|min:15',
            'tipo' => 'required|integer|min:0|in:1,2',
            'dui' => 'nullable|string|max:25',
            'telefono' => 'nullable|string|max:15'
        ],[
            'nombre1.required' => 'El Primer nombre es obligatorio',
            'apellido1.required' => 'El Primer apellido es obligatorio',
            'edad.required' => 'La edad es obligatoria',
            'tipo.in' => 'El tipo de trabajador se debe seleccionar',
            'edad.min' => 'La edad mínima es 15 años',
        ]);
 
        // Verificar si el trabajador ya existe
        $trabajadorExistente = Trabajadores::where('dui', $trabajador->dui)->first();
        if ($trabajadorExistente) {
            throw new \Exception('El dui ingresado ya existe.');
        }
        // Guardar el trabajador
        $trabajador->dui = strtoupper($trabajador->dui);
        $trabajador->telefono = strtoupper($trabajador->telefono);
        $trabajador->nombre1 = strtoupper($trabajador->nombre1);
        $trabajador->nombre2 = strtoupper($trabajador->nombre2);
        $trabajador->apellido1 = strtoupper($trabajador->apellido1);
        $trabajador->apellido2 = strtoupper($trabajador->apellido2);
        $trabajador->tipo = strtoupper($trabajador->tipo);
        $trabajador->edad = $trabajador->edad;
        $trabajador->dui = $trabajador->dui;
        $trabajador->telefono = $trabajador->telefono;
    

        if ($trabajador->save()) {
            return response()->json(['success' => true, 'message' => 'Trabajador creado exitosamente.']);
        } else {
            throw new \Exception('Error al crear el trabajador.');
        }
    }

    public function edit($id)
    {
        $trabajador = Trabajadores::find($id);
        if (!$trabajador) {
            return response()->json(['success' => false, 'message' => 'Trabajador no encontrado.']);
        }

        return response()->json(['success' => true, 'data' => $trabajador]);  
    }

    public function update(Request $request)
    {
        $trabajador = Trabajadores::find($request->id_trabajador);
        if (!$trabajador) {
            return response()->json(['success' => false, 'message' => $request]);
        }

        // Validar los datos
        $request->validate([
            'nombre1' => 'required|string|max:255',
            'nombre2' => 'nullable|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'nullable|string|max:255',
            'edad' => 'required|integer|min:15',
            'tipo' => 'required|integer|min:0|in:1,2',
            'dui' => 'nullable|string|max:25',
            'telefono' => 'nullable|string|max:15'
        ],[
            'nombre1.required' => 'El Primer nombre es obligatorio',
            'apellido1.required' => 'El Primer apellido es obligatorio',
            'edad.required' => 'La edad es obligatoria',
            'tipo.in' => 'El tipo de trabajador se debe seleccionar',
            'edad.min' => 'La edad mínima es 15 años',
        ]);

        // Actualizar los datos del trabajador
        $trabajador->nombre1 = strtoupper($request->input('nombre1'));
        $trabajador->nombre2 = strtoupper($request->input('nombre2'));
        $trabajador->apellido1 = strtoupper($request->input('apellido1'));
        $trabajador->apellido2 = strtoupper($request->input('apellido2'));
        $trabajador->edad = $request->input('edad');
        $trabajador->tipo = strtoupper($request->input('tipo'));
        $trabajador->dui = strtoupper($request->input('dui'));
        $trabajador->telefono = strtoupper($request->input('telefono'));

        if ($trabajador->update()) {
            return response()->json(['success' => true, 'message' => 'Trabajador actualizado exitosamente.']);
        } else {
            throw new \Exception('Error al actualizar el trabajador.');
        }
    }

    public function destroy($id)
    {
        $trabajador = Trabajadores::find($id);
        if (!$trabajador) {
            return response()->json(['success' => false, 'message' => 'Trabajador no encontrado.']);
        }

        if ($trabajador->delete()) {
            return response()->json(['success' => true, 'message' => 'Trabajador eliminado exitosamente.']);
        } else {
            throw new \Exception('Error al eliminar el trabajador.');
        }
    }

    public function datatable()
    {
        $trabajadores = Trabajadores::select([
            'id_trabajador', 
            DB::raw("CONCAT(nombre1, ' ', nombre2, ' ', apellido1, ' ', apellido2) AS nombre_completo"),
            'tipo',
        ]);
        
        return datatables()->of($trabajadores)
            ->filterColumn('nombre_completo', function($query, $keyword) {
                $query->whereRaw("LOWER(CONCAT(nombre1, ' ', nombre2, ' ', apellido1, ' ', apellido2)) LIKE ?", ["%" . strtoupper($keyword) . "%"]);
            })
            ->addColumn('acciones', function($trabajador) {
                return '
                
            <div class="flex justify-evenly items-center">
                <button id="btn_editar" data_id="'.$trabajador->id_trabajador.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></button>

                <button id="btn_eliminar" data_id="'.$trabajador->id_trabajador.'" class="btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>
                
                <button id="btn_ver" data_id="'.$trabajador->id_trabajador.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-blue-600 hover:text-blue-400 bx bxs-info-circle bx-rotate-180"></i>  </button>
            </div>';
            })->rawColumns(['acciones'])
            ->toJson();
    }
}
