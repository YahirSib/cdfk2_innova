<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agrupacion de Sala - {{ $entrada->correlativo }}</title>
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
            margin-bottom: 10px;
        }

        .header img {
            width: 120px;
        }

        .subtitle {
            border-bottom: 1px solid #999;
            color: #800d0d;
            margin-bottom: 15px;
            font-size: 18px;
            padding-bottom: 5px;
            text-align: right;
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

        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 6px;
            vertical-align: top;
        }

        table.table-detalle {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.table-detalle th,
        table.table-detalle td {
            border: 1px solid #999;
            padding: 6px 8px;
        }

        table.table-detalle th {
            background-color: #f2f2f2;
        }

        .totals {
            width: 100%;
            margin-top: 20px;
        }

        .totals td {
            padding: 6px;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .comment {
            margin-top: 25px;
            font-size: 12px;
            font-style: italic;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="document">
        <div class="header">
            <img src="{{ public_path('images/innova_color.png') }}" alt="Logo">
            <div>
                <h1 class="title">{{$data['title']}}</h1>
                <h2 class="subtitle">{{$data['correlativo']}}</h2>
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td><strong>Carpintero:</strong> {{$data['cacastero']}}</td>
                <td style="text-align: right;"><strong>Fecha Documento:</strong> {{$data['fecha_ingreso']}}</td>
            </tr>
        </table>

        <table class="table-detalle">
            <thead>
                <tr colspan="6">
                    <th colspan="6">Entrada de Salas</th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>Cod. Sala</th>
                    <th>Nombre de Sala</th>
                    <th>Cantidad</th>
                    <th>Costo Unitario</th>
                    <th>Costo Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles_entrada as $index => $detalle)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detalle->sala->codigo }}</td>
                        <td>{{ $detalle->sala->nombre }}</td>
                        <td class="right">{{ $detalle->unidades }}</td>
                        <td class="right">${{ number_format($detalle->costo_unitario, 2) }}</td>
                        <td class="right">${{ number_format($detalle->costo_total, 2) }}</td>
                    </tr>
                @endforeach
               
            </tbody>
        </table>

        <table class="table-detalle">
            <thead>
                <tr colspan="6">
                    <th colspan="6">Salida de Piezas</th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>Cod. Pieza</th>
                    <th>Nombre de Pieza</th>
                    <th>Cantidad</th>
                    <th>Costo Unitario</th>
                    <th>Costo Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles_salida as $index => $detalle)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detalle->pieza->codigo }}</td>
                        <td>{{ $detalle->pieza->nombre }}</td>
                        <td class="right">{{ $detalle->unidades }}</td>
                        <td class="right">${{ number_format($detalle->costo_unitario, 2) }}</td>
                        <td class="right">${{ number_format($detalle->costo_total, 2) }}</td>
                    </tr>
                @endforeach
               
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="right bold">Total Costo por Entrada:</td>
                <td class="right">${{ number_format($total_entrada, 2) }}</td>
            </tr>
            <tr>
                <td class="right bold">Total Costo por Salida:</td>
                <td class="right">${{number_format($total_salida, 2)}}</td>
            </tr>
        </table>

        <div class="comment">
            <strong>Comentario:</strong> {{ $entrada->comentario ?? 'N/A' }}
        </div>

        <div class="footer">
            Documento generado automáticamente - {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
