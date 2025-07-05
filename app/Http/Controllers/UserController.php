<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Perfil;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function index(){
        $perfiles = Perfil::all()->where('estado', 1);
        return view('mantenimientos.mtUsuarios', ['menu' => $this->getMenu(), 'perfiles' => $perfiles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'perfil_id' => 'required|integer|min:1'
        ]);

        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'perfil_id' => $request->perfil_id
        ]);

        if($user){
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente'
            ]);
        }else{
            return response()->json([
                'success' => true,
                'message' => 'Error al crear el usuario, Contacte con Soporte Tecnico'
            ]); 
        }
    }

    public function edit($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrada.']);
        }
        return response()->json(['success' => true, 'data' => $usuario]);
    }

    public function update(Request $request)
    {
        $usuario = User::find($request->input('id'));
        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrada.']);
        }
        $usuario->name = $request->input('name');
        $newEmail = $request->input('email');
        $usuario->perfil_id = $request->input('perfil_id');

        if($usuario->email != $newEmail){
            $request->validate([
                'name' => 'required|string|max:225',
                'email' => 'required|email|unique:users,email',
                ],
                [
                    'nombre.required' => 'El nombre es obligatorio',
                    'email.required' => 'El correo es obligatorio',
                    'email.unique' => 'El correo debe ser unico'
                ]);
        }else{
            $request->validate([
                'name' => 'required|string|max:225',
                'email' => 'required|email',
            ],
            [
                'nombre.required' => 'El nombre es obligatorio',
                'email.required' => 'El correo es obligatorio',
            ]);
        }
   

        $usuario->name = strtoupper($usuario->name);
        $usuario->email = $newEmail;
        $usuario->perfil_id = $usuario->perfil_id;
        $usuario->updated_at = now();
        
        if($usuario->save()){
            return response()->json(['success' => true, 'message' => 'Usuario actualizado exitosamente.']);  
        }else{
            throw new \Exception('Error al actualizar la pieza.');
        }
    }


    public function destroy($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrada.']);
        }

        $userId = Auth::user()->id;

        if($id == $userId){
            return response()->json(['success' => false, 'message' => 'No se puede eliminar el usuario logueado.']);
        }

        if ($usuario->delete()) {
            return response()->json(['success' => true, 'message' => 'Usuario eliminado exitosamente.']);
        } else {
            throw new \Exception('Error al eliminar el usuario.');
        }
    }

    public function reset_pass(Request $request){
        $usuario = User::find($request->input('id'));

        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.']);
        }

        $nuevaPassword = $request->input('password');

        if (!$nuevaPassword || strlen($nuevaPassword) < 5) {
            return response()->json(['success' => false, 'message' => 'La contraseña debe tener al menos 5 caracteres.']);
        }

        $userId = Auth::user()->id;

        if($request->input('id') == $userId){
            return response()->json(['success' => false, 'message' => 'No es permitido reiniciar la contraseña del usuario logueado.']);
        }

        $usuario->password = bcrypt($nuevaPassword);
        $usuario->save();

        return response()->json(['success' => true, 'message' => 'Contraseña reiniciada exitosamente.']);
    }



    public function datatable(){
        $usuarios = User::select([
                'users.id', 
                'users.name',
                'users.email',
                'perfil.nombre as perfil_nombre'
            ])
            ->join('perfil', 'users.perfil_id', '=', 'perfil.id');

        return datatables()->of($usuarios)
            ->addColumn('acciones', function($usuario) {
                return '
                <div class="flex justify-evenly items-center">
                    <button id="btn_editar" data_id="'.$usuario->id.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-yellow-600 hover:text-yellow-400 bx bxs-edit"></i></button>
                    <button id="btn_eliminar" data_id="'.$usuario->id.'" class="btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>
                    <button id="btn_reset" data_id="'.$usuario->id.'" class="btn btn-sm btn-primary cursor-pointer"> <i class=" text-2xl text-blue-600 hover:text-blue-400 bx bx-reset"></i> </button>
                </div>';
            })
            ->rawColumns(['acciones'])
            ->toJson();
    }

}
