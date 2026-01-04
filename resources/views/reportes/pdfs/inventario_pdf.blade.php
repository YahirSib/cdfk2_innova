<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Disponibilidad</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .document { width: 95%; margin: 20px auto; border: 1px solid #ccc; padding: 25px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .header img { width: 120px; margin-bottom: 5px; }
        .title { font-size: 20px; font-weight: bold; text-align: right; margin-bottom: -10px; }
        .subtitle { border-bottom: 1px solid #999; color: #800d0d; margin-bottom: 15px; font-size: 18px; padding-bottom: 5px; text-align: right; }
        
        table.table-detalle { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        table.table-detalle th, table.table-detalle td { border: 1px solid #999; padding: 6px 8px; word-wrap: break-word; }
        table.table-detalle th { background-color: #f2f2f2; }
        
        .right { text-align: right; }
        .center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #777; }
    </style>
</head>
<body>
    <div class="document">
        <div class="header">
            <img src="{{ public_path('images/innova_color.png') }}" alt="Logo">
            <div>
                <h1 class="title">Reporte de Disponibilidad</h1>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="position: absolute; width: 50%; text-align: left;"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</h3>
                    <h2 class="subtitle">Existencia por Bodega</h2>
                </div>
            </div>
        </div>

        {{-- Calculamos el colspan dinámico: 3 columnas fijas (#, Cod, Nom) + cantidad de bodegas seleccionadas --}}
        @php
            $dynamicColspan = 3 + count($bodegasVisibles);
        @endphp

        {{-- 1) Piezas Individuales --}}
        @if ($piezasIndividuales->count() > 0)
            <table class="table-detalle">
                <thead>
                    <tr>
                        <th colspan="{{ $dynamicColspan }}" class="center">Piezas Individuales</th>
                    </tr>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Código</th>
                        <th>Nombre</th>
                        
                        {{-- Encabezados Dinámicos --}}
                        @if(in_array(1, $bodegasVisibles)) <th style="width: 12%;">Cacastes</th> @endif
                        @if(in_array(2, $bodegasVisibles)) <th style="width: 12%;">Traslado</th> @endif
                        @if(in_array(3, $bodegasVisibles)) <th style="width: 12%;">General</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($piezasIndividuales as $index => $row)
                        <tr>
                            <td class="right">{{ $index + 1 }}</td>
                            <td>{{ $row->codigo }}</td>
                            <td>{{ $row->nombre }}</td>

                            {{-- Celdas Dinámicas --}}
                            @if(in_array(1, $bodegasVisibles)) <td class="right">{{ (int)$row->existencia }}</td> @endif
                            @if(in_array(2, $bodegasVisibles)) <td class="right">{{ (int)$row->existencia_traslado }}</td> @endif
                            @if(in_array(3, $bodegasVisibles)) <td class="right">{{ (int)$row->existencia_tapizado }}</td> @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- 2) Piezas No Individuales --}}
        {{-- NOTA: Según tu controlador, estas piezas SOLO tienen 'existencia' (Cacastes). 
             Si el usuario filtra solo por Traslado, esta tabla podría aparecer vacía de datos o no mostrarse.
             Aquí asumo que solo mostramos la columna Cacastes si la bodega 1 fue seleccionada. --}}
        @if ($piezasNoIndividuales->count() > 0 && in_array(1, $bodegasVisibles))
            <table class="table-detalle">
                <thead>
                    <tr>
                        {{-- 3 Fijas + 1 Dinámica (Solo Cacastes) --}}
                        <th colspan="4" class="center">Piezas No Individuales</th>
                    </tr>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Código</th>
                        <th>Nombre</th>
                        {{-- Solo mostramos Cacastes aquí porque es lo único que tiene este tipo --}}
                        <th style="width: 15%;">Cacastes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($piezasNoIndividuales as $index => $row)
                        <tr>
                            <td class="right">{{ $index + 1 }}</td>
                            <td>{{ $row->codigo }}</td>
                            <td>{{ $row->nombre }}</td>
                            <td class="right">{{ (int)$row->existencia }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- 3) Salas --}}
        @if ($salas->count() > 0)
            <table class="table-detalle">
                <thead>
                    <tr>
                        <th colspan="{{ $dynamicColspan }}" class="center">Salas</th>
                    </tr>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Código</th>
                        <th>Nombre</th>

                        {{-- Encabezados Dinámicos --}}
                        @if(in_array(1, $bodegasVisibles)) <th style="width: 12%;">Cacastes</th> @endif
                        @if(in_array(2, $bodegasVisibles)) <th style="width: 12%;">Traslado</th> @endif
                        @if(in_array(3, $bodegasVisibles)) <th style="width: 12%;">General</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($salas as $index => $row)
                        <tr>
                            <td class="right">{{ $index + 1 }}</td>
                            <td>{{ $row->codigo }}</td>
                            <td>{{ $row->nombre }}</td>

                            {{-- Celdas Dinámicas --}}
                            @if(in_array(1, $bodegasVisibles)) <td class="right">{{ (int)$row->existencia }}</td> @endif
                            @if(in_array(2, $bodegasVisibles)) <td class="right">{{ (int)$row->existencia_traslado }}</td> @endif
                            @if(in_array(3, $bodegasVisibles)) <td class="right">{{ (int)$row->existencia_tapizado }}</td> @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Mensaje si no se seleccionó nada o no hay resultados --}}
        @if($piezasIndividuales->isEmpty() && $piezasNoIndividuales->isEmpty() && $salas->isEmpty())
            <div class="center" style="margin-top: 50px; color: #777;">
                <h3>No se encontraron registros con los filtros seleccionados.</h3>
            </div>
        @endif

        <div class="footer">
            Documento generado automáticamente - {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>