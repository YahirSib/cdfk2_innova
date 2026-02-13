<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos por Cacastero</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .document {
            width: 95%;
            margin: 20px auto;
            border: 1px solid #ccc;
            padding: 25px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 120px;
            margin-bottom: 5px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            margin-bottom: -10px;
        }

        .subtitle {
            border-bottom: 1px solid #999;
            color: #800d0d;
            margin-bottom: 15px;
            font-size: 16px;
            padding-bottom: 5px;
            text-align: right;
        }

        table.table-detalle {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }

        table.table-detalle th,
        table.table-detalle td {
            border: 1px solid #999;
            padding: 6px 8px;
            word-wrap: break-word;
        }

        table.table-detalle th {
            background-color: #f2f2f2;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #777;
        }

        .group-header {
            background-color: #e0e0e0;
            font-weight: bold;
            padding: 8px;
            margin-top: 10px;
            border: 1px solid #999;
            border-bottom: none;
        }

        .subtotal {
            font-weight: bold;
            background-color: #f9f9f9;
            text-align: right;
            padding: 5px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
        }

        .anulado {
            color: red;
            text-decoration: line-through;
        }

        .info-panel {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="document">
        <div class="header">
            <img src="{{ public_path('images/innova_color.png') }}" alt="Logo">
            <div>
                <h1 class="title">Reporte de Movimientos</h1>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-direction: column; align-items: flex-end;">
                    <h3 style="margin: 2px 0;"><strong>Fecha Impresión:</strong> {{ $fecha_generacion }}</h3>
                    @if($fecha_inicio || $fecha_fin)
                        <h4 style="margin: 2px 0;">
                            <strong>Rango:</strong> 
                            {{ $fecha_inicio ?? 'Inicio' }} - {{ $fecha_fin ?? 'Actualidad' }}
                        </h4>
                    @endif
                    <h2 class="subtitle">Historial por Cacastero</h2>
                </div>
            </div>
        </div>

        <div class="info-panel">
            <table style="width: 100%; border: none; margin: 0;">
                <tr>
                    <td style="border: none; padding: 2px;"><strong>Cacastero:</strong> {{ $cacastero->nombre1 }}
                        {{ $cacastero->nombre2 }} {{ $cacastero->apellido1 }} {{ $cacastero->apellido2 }}
                    </td>
                    <td style="border: none; padding: 2px; text-align: center;"><strong>DUI:</strong>
                        {{ $cacastero->dui }}</td>
                </tr>
            </table>
        </div>

        @foreach($grupos as $tipoDoc => $meses)
            <h3 style="color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 5px; margin-top: 30px;">
                Tipo de Documento: {{ $tiposDocMap[$tipoDoc] ?? $tipoDoc }}
            </h3>

            @php $subtotalTipo = 0; @endphp

            @foreach($meses as $mes => $movs)
                
                @php
                    $nombreMes = \Carbon\Carbon::createFromFormat('Y-m', $mes)->translatedFormat('F Y');
                @endphp

                <div class="group-header">
                    Mes: {{ ucfirst($nombreMes) }}
                </div>
                <table class="table-detalle">
                    <thead>
                        <tr>
                            <th style="width: 5%;" class="center">N°</th>
                            <th style="width: 15%;" class="center">Fecha</th>
                            <th style="width: 15%;" class="center">N-Doc</th>
                            <th class="center">Estado</th>
                            <th style="width: 15%;" class="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movs as $index => $mov)
                            @php
                                $esAnulado = $mov->estado === 'Z';
                                $valor = $esAnulado ? 0 : $mov->total;
                                $subtotalTipo += $valor;
                            @endphp
                            <tr class="{{ $esAnulado ? 'anulado' : '' }}">
                                <td class="center">{{ $index + 1 }}</td>
                                <td class="center">{{ \Carbon\Carbon::parse($mov->fecha_ingreso)->format('d/m/Y') }}</td>
                                <td class="center">{{ $mov->correlativo }}</td>
                                <td class="center">{{ ($esAnulado ? 'Anulado' : ($mov->estado == 'I' ? 'Impreso' : ($mov->estado == 'X' ? 'Procesado' : 'Activo'))) }}</td>
                                <td class="right">{{ $esAnulado ? '0.00' : number_format($mov->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            <div class="subtotal">
                Subtotal {{ $tipoDoc }}: {{ number_format($subtotalTipo, 2) }}
            </div>
        @endforeach

        <div class="footer">
            Documento generado automáticamente - {{ $fecha_generacion }} <br>
            Generado por: {{ Auth::user()->name ?? 'Sistema' }}
        </div>
    </div>
</body>

</html>