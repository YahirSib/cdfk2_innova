@extends('layouts.app')

@section('title', 'Venta de Salas')

@section('content')

<meta name="datatable" content="{{ route('venta-sala.datatable') }}">
<meta name="delete" content="{{ route('venta-sala.delete', ['id' => '__ID__']) }}">
<meta name="action" content = "ver">
<meta name="print_historico" content="{{ route('venta-sala.imprimirHistorico', ['id' => '__ID__']) }}">
<meta name="print_anulada" content="{{ route('venta-sala.imprimirAnular', ['id' => '__ID__']) }}">
<meta name="meses-url" content="{{ route('nota-pieza.meses') }}">


<div class="container w-full p-4">
    <div class="w-full mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Venta de Salas</h1>
        <p class="text-gray-600">Venta de Salas y Piezas</p>
    </div>

    <!-- DataTable -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="mb-4 w-full flex sm:flex-row flex-col items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 sm:mb-0 mb-2">Listado de Ventas</h2>

            <a id="btnNuevo" href="{{ route('venta-sala.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center m-1">
                    Agregar Venta
            </a>

        </div>
        <div class="relative overflow-x-auto">

            <div class="flex justify-end sm:justify-start sm:absolute relative items-center mb-4 z-10">
                <div id="filtroMesContainer">
                    <select id="filtroMes" class="border border-gray-300 rounded pr-10 text-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
                <!-- El buscador de DataTables aparecerá automáticamente a la derecha -->
            </div>


            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700" id="tblTrasladoTapiceria">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">N° Doc</th>
                        <th class="px-4 py-2 text-left">Cliente</th>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Estado</th>
                        <th class="px-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
    
@endsection

@section('script')
    @vite(['resources/js/movimientos/mvVentaSala.js'])
@endsection