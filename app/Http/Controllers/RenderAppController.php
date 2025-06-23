<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;

class RenderAppController extends Controller
{
    public function index(){
        $menu = (new MenuController)->obtenerMenu();
        return view('index', ['menu' => $menu]);
    }
}
