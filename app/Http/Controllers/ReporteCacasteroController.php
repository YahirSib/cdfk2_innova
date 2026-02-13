<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movimiento;
use App\Models\Trabajadores;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteCacasteroController extends Controller
{
    public function index()
    {
        // Obtener trabajadores que sean Cacasteros (Tipo 2 = Carpintero)
        $cacasteros = Trabajadores::where('tipo', 2)->get();
        return view('reportes.repCacastero', ['menu' => $this->getMenu(), 'cacasteros' => $cacasteros]);
    }

    public function generarReporte(Request $request)
    {
        ini_set('memory_limit', '1024M');
        $id_trabajador = $request->input('id_trabajador');

        $request->validate([
            'id_trabajador' => 'required|exists:trabajadores,id_trabajador'
        ], [
            'id_trabajador.required' => 'Debe seleccionar un cacastero',
            'id_trabajador.exists' => 'El cacastero seleccionado no es válido'
        ]);

        $cacastero = Trabajadores::find($id_trabajador);

        // Obtener movimientos del cacastero
        // Agrupados por Tipo, Mes, Fecha
        // Ordenados por correlativo
        // "tomar en cuenta que los documentos en estado 'Z' no deben mostrar valor ya que estan anulados ni deben sumar pero si deben verse reflejados en el reporte"

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $query = Movimiento::where('cacastero', $id_trabajador);

        if ($fechaInicio) {
            $query->whereDate('fecha_ingreso', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('fecha_ingreso', '<=', $fechaFin);
        }

        $movimientos = $query->orderBy('tipo_doc')
            ->orderBy('fecha_ingreso')
            ->orderBy('correlativo')
            ->get();

        // Agrupar los datos para la vista
        // Estructura: Tipo -> Mes -> Movimientos
        $grupos = $movimientos->groupBy(function ($item) {
            return $item->tipo_doc;
        })->map(function ($tipoGroup) {
            return $tipoGroup->groupBy(function ($item) {
                return Carbon::parse($item->fecha_ingreso)->format('Y-m'); // Agrupar por Mes
            });
        });

        // Obtener mapa de tipos de documentos para mostrar nombre completo
        // Clave: abreviacion, Valor: nombre
        $tiposDocMap = DB::table('tipo_doc')->pluck('nombre', 'abreviacion')->toArray();

        $pdf = Pdf::loadView('reportes.pdfs.cacastero_pdf', [
            'cacastero' => $cacastero,
            'grupos' => $grupos,
            'tiposDocMap' => $tiposDocMap,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            'fecha_inicio' => $fechaInicio ? Carbon::parse($fechaInicio)->format('d/m/Y') : null,
            'fecha_fin' => $fechaFin ? Carbon::parse($fechaFin)->format('d/m/Y') : null,
        ])
            ->setPaper('letter', 'portrait');

        return $pdf->stream('reporte-cacastero.pdf');
    }

    public function generarReporteDetallado(Request $request)
    {
        ini_set('memory_limit', '1024M');
        $id_trabajador = $request->input('id_trabajador');

        $request->validate([
            'id_trabajador' => 'required|exists:trabajadores,id_trabajador'
        ], [
            'id_trabajador.required' => 'Debe seleccionar un cacastero',
            'id_trabajador.exists' => 'El cacastero seleccionado no es válido'
        ]);

        $cacastero = Trabajadores::find($id_trabajador);

        $cacastero = Trabajadores::find($id_trabajador);
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // Obtener movimientos
        $query = Movimiento::where('cacastero', $id_trabajador);

        if ($fechaInicio) {
            $query->whereDate('fecha_ingreso', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('fecha_ingreso', '<=', $fechaFin);
        }

        $movimientosIds = $query->pluck('id_movimiento');

        // Obtener detalles con relaciones
        $detalles = \App\Models\Detalle::whereIn('fk_movimiento', $movimientosIds)
            ->with(['pieza', 'sala', 'movimiento'])
            ->get();

        // Agrupar por Producto (Pieza o Sala)
        $grupos = $detalles->groupBy(function ($detalle) {
            if ($detalle->fk_pieza) {
                return $detalle->pieza ? ('Pieza: ' . $detalle->pieza->codigo . ' - ' . $detalle->pieza->nombre) : 'Pieza (Sin Datos)';
            } elseif ($detalle->fk_sala) {
                return $detalle->sala ? ('Sala: ' . $detalle->sala->codigo . ' - ' . $detalle->sala->nombre) : 'Sala (Sin Datos)';
            }
            return 'Otros';
        })->map(function ($prodGroup) {
            // Agrupar por Tipo de Documento
            return $prodGroup->groupBy(function ($detalle) {
                return $detalle->movimiento->tipo_doc;
            })->map(function ($typeGroup) {
                // Agrupar por Mes
                return $typeGroup->groupBy(function ($detalle) {
                    return Carbon::parse($detalle->movimiento->fecha_ingreso)->format('Y-m');
                })->map(function ($monthGroup) {
                    // Ordenar por Fecha y Correlativo
                    return $monthGroup->sortBy([
                        [
                            function ($detalle) {
                                return $detalle->movimiento->fecha_ingreso;
                            },
                            'asc'
                        ],
                        [
                            function ($detalle) {
                                return $detalle->movimiento->correlativo;
                            },
                            'asc'
                        ],
                    ]);
                });
            });
        });

        // Títulos de documentos
        $tiposDocMap = DB::table('tipo_doc')->pluck('nombre', 'abreviacion')->toArray();

        $pdf = Pdf::loadView('reportes.pdfs.cacastero_detallado_pdf', [
            'cacastero' => $cacastero,
            'grupos' => $grupos,
            'tiposDocMap' => $tiposDocMap,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            'fecha_inicio' => $fechaInicio ? Carbon::parse($fechaInicio)->format('d/m/Y') : null,
            'fecha_fin' => $fechaFin ? Carbon::parse($fechaFin)->format('d/m/Y') : null,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream('reporte-cacastero-detallado.pdf');
    }
}
