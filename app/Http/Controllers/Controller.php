<?php

namespace App\Http\Controllers;
use App\Services\MenuService;
use Illuminate\Support\Facades\DB;
use App\Models\Movimiento;

abstract class Controller
{
    private $menu;

    public function __construct()
    {
        $this->menu = MenuService::obtenerMenu();
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function obtenerMesesTipoDoc($tipo_doc)
    {
        // Forzar idioma español
        DB::statement("SET lc_time_names = 'es_ES'");

        $meses = Movimiento::query()
            ->selectRaw("DISTINCT MONTH(fecha_ingreso) as mes, UPPER(MONTHNAME(fecha_ingreso)) as nombre_mes")
            ->where('tipo_doc', $tipo_doc)
            ->orderByRaw('mes')
            ->get();

        return response()->json($meses);
    }
}
