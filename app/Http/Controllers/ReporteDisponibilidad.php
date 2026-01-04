<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Trabajadores;
use Illuminate\Support\Facades\DB;
use App\Models\Pieza;
use App\Models\Detalle;
use App\Models\Salas;
use App\Services\MenuService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\PiezasServices;
use App\Services\SalasServices;

class ReporteDisponibilidad extends Controller
{
    public function index()
    {
        return view('reportes.repDisponilidad', ['menu' => $this->getMenu()]);
    }

    public function generarReporte(Request $request)
    {
        ini_set('memory_limit', '1080M');

        // 1. Obtener filtros del request
        // Tipos: 2 = Pieza Indiv, 3 = Pieza No Indiv, 4 = Salas
        $tiposSeleccionados = $request->input('tipo', []); 
        
        // Bodegas: 1 = Cacastes, 2 = Traslado, 3 = General
        $bodegasSeleccionadas = $request->input('estado', []);

        // 2. Definir columnas dinámicas a seleccionar
        // Siempre necesitamos codigo y nombre
        $columnasSelect = ['codigo', 'nombre'];

        // Mapeo de ID Bodega -> Nombre Columna en BD
        $mapaColumnas = [
            1 => 'existencia',          // Cacastes
            2 => 'existencia_traslado', // Traslado
            3 => 'existencia_tapizado'  // General (Asumiendo que este es el campo para General)
        ];

        foreach ($bodegasSeleccionadas as $bodegaId) {
            if (isset($mapaColumnas[$bodegaId])) {
                $columnasSelect[] = $mapaColumnas[$bodegaId];
            }
        }

        // 3. Consultas Condicionales
        // Inicializamos colecciones vacías por si el usuario no selecciona esa categoría
        $piezasIndividuales = collect();
        $piezasNoIndividuales = collect();
        $salas = collect();

        // -- Lógica para Piezas Individuales (Tipo 2) --
        if (in_array(2, $tiposSeleccionados)) {
            $piezasIndividuales = Pieza::query()
                ->where('individual', 1)
                ->select($columnasSelect) // Solo trae las columnas de las bodegas pedidas
                ->orderBy('codigo')
                ->get();
        }

        // -- Lógica para Piezas No Individuales (Tipo 3) --
        if (in_array(3, $tiposSeleccionados)) {
            // Nota: Verifica si Piezas No Individuales tiene las columnas traslado/tapizado.
            // Si solo tiene 'existencia', filtramos las columnas para evitar error SQL
            $colsPiezaNoInd = array_intersect($columnasSelect, ['codigo', 'nombre', 'existencia']); 
            
            $piezasNoIndividuales = Pieza::query()
                ->where('individual', 0)
                ->select($colsPiezaNoInd)
                ->orderBy('codigo')
                ->get();
        }

        // -- Lógica para Salas (Tipo 4) --
        if (in_array(4, $tiposSeleccionados)) {
            $salas = Salas::query()
                ->select($columnasSelect)
                ->orderBy('codigo')
                ->get();
        }

        // 4. Generar PDF
        // Pasamos también $bodegasSeleccionadas para que la vista sepa qué columnas pintar en la tabla HTML
        $pdf = Pdf::loadView('reportes.pdfs.inventario_pdf', [
            'piezasIndividuales' => $piezasIndividuales,
            'piezasNoIndividuales' => $piezasNoIndividuales,
            'salas' => $salas,
            'bodegasVisibles' => $bodegasSeleccionadas // Pasamos esto para ocultar <th> en la vista
        ])
        ->setPaper('letter', 'portrait');

        return $pdf->stream('reporte-existencia-inventario.pdf');
    }

}