<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Perfil;

class UserController extends Controller
{

    public function index(){
        $menu = (new MenuController)->obtenerMenu();

        $perfiles = \App\Models\Perfil::all()->where('estado', 1);

        return view('mantenimientos.mtUsuarios', ['menu' => $menu, 'perfiles' => $perfiles]);
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
                    <button id="btn_ver" data_id="'.$usuario->id.'" class="btn btn-sm btn-primary cursor-pointer"><i class=" text-2xl text-blue-600 hover:text-blue-400 bx bxs-info-circle bx-rotate-180"></i>  </button>
                </div>';
            })
            ->rawColumns(['acciones'])
            ->toJson();
    }

}
