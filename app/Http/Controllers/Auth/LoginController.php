<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {

        if (Auth::check()) {
            return redirect()->route('index'); // o donde quieras enviarlo
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.']);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Contraseña incorrecta.']);
        }

        Auth::login($user); // login manual

         return response()->json(['success' => true, 'redirect' => route('index')]);

    }
}

