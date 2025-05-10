<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index()
    {
        return view('mantenimientos.mtPerfil');
    }

    public function create()
    {
        return view('perfil.create');
    }

    public function edit($id)
    {
        return view('perfil.edit', compact('id'));
    }

    public function show($id)
    {
        return view('perfil.show', compact('id'));
    }
}
