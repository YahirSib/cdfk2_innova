<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PerfilController extends Controller
{
    public function index()
    {
        $menu = (new MenuController)->obtenerMenu();
        return view('mantenimientos.mtPerfil', ['menu' => $menu]);
    }

    public function edit($id)
    {
        $perfil = Perfil::find($id);

        if (!$perfil) {
            return response()->json(['success' => false, 'message' => 'Perfil no encontrada.']);
        }
        return response()->json(['success' => true, 'data' => $perfil]);
    }

    public function destroy($id)
    {
        $perfil = Perfil::find($id);

        if (!$perfil) {
            return response()->json(['success' => false, 'message' => 'Perfil no encontrada.']);
        }

        $validacion = DB::table('permisos_menu')
            ->where('id_perfil', $perfil->id)
            ->exists();
            
        if ($validacion) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar el perfil tiene relaciones a permisos.']);
        }

        $validacion2 = DB::table('users')
            ->where('perfil_id', $perfil->id)
            ->exists();

        if ($validacion2) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar el perfil asignado a usuarios.']);
        }

        if ($perfil->delete()) {
            return response()->json(['success' => true, 'message' => 'Perfil eliminada exitosamente.']);
        } else {
            throw new \Exception('Error al eliminar el perfil.');
        }
    }


    public function store(Request $request)
    {
        $perfil = new Perfil();
        $perfil->nombre = $request->input('nombre');
        $perfil->estado = $request->input('estado');

        $request->validate([
            'nombre' => 'required|string|max:225',
            'estado' => 'required|integer|min:0|in:1,2',
        ],
        [
            'nombre.required' => 'El nombre es obligatorio',
            'estado.in' => 'El estado debe ser seleccionado',
        ]);

        $perfil->estado = $perfil->estado;
        $perfil->nombre = strtoupper($perfil->nombre);
        $perfil->created_at = now();
        $perfil->updated_at = now();

        if($perfil->save()){
            return response()->json(['success' => true, 'message' => 'Perfil creada exitosamente.']);
        }else{
             throw new \Exception('Error al crear la pieza.');
        }

    }

    public function update(Request $request)
    {
        $perfil = Perfil::find($request->input('id'));
        if (!$perfil) {
            return response()->json(['success' => false, 'message' => 'Pieza no encontrada.']);
        }
        $perfil->nombre = $request->input('nombre');
        $perfil->estado = $request->input('estado');
        $request->validate([
            'nombre' => 'required|string|max:225',
            'estado' => 'required|integer|min:0|in:1,2',
        ],
        [
            'nombre.required' => 'El nombre es obligatorio',
            'estado.in' => 'El estado debe ser seleccionado',
        ]);
        $perfil->nombre = strtoupper($perfil->nombre);
        $perfil->estado = $perfil->estado;
        $perfil->updated_at = now();
        
        if($perfil->save()){
            return response()->json(['success' => true, 'message' => 'Pieza actualizada exitosamente.']);  
        }else{
            throw new \Exception('Error al actualizar la pieza.');
        }
    }

    public function datatable(){
        $perfiles = Perfil::select([
            'id', 
            'nombre',
            'estado',
        ]);
        
        return datatables()->of($perfiles)
            ->addColumn('acciones', function($perfil) {
                return '
                
            <div class="flex justify-evenly items-center">
                <button id="btn_editar" data_id="'.$perfil->id.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></button>

                <button id="btn_eliminar" data_id="'.$perfil->id.'" class="btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>
                
                <button id="btn_ver" data_id="'.$perfil->id.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-blue-600 hover:text-blue-400 bx bxs-info-circle bx-rotate-180"></i>  </button>
            </div>';
            })->rawColumns(['acciones'])
            ->toJson();
    }

}
