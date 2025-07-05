@extends('layouts.app')

@section('title', 'Agrupación de Sala')

@section('content')

<meta name="datatable" content="{{ route('nota-pieza.datatable') }}">
<meta name="delete" content="{{ route('nota-pieza.delete', ['id' => '__ID__']) }}">
<meta name="action" content = "ver">
<meta name="print_historico" content="{{ route('nota-pieza.print_historico', ['id' => '__ID__']) }}">
<meta name="print_anulada" content="{{ route('nota-pieza.print_anulada', ['id' => '__ID__']) }}">

<div class="container w-full p-4">
    <div class="w-full mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Agrupación de Sala</h1>
        <p class="text-gray-600">Generación de almacenamiento de salas</p>
    </div>

    <!-- DataTable -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="mb-4 w-full flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Listado de Salas</h2>

            <a id="btnNuevo" href="{{ route('agrupacion-sala.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center m-1">
                    Agregar Sala
            </a>

        </div>
        <div class="relative overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700" id="tblPiezas">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">N° Doc</th>
                        <th class="px-4 py-2 text-left">Cacastero</th>
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
    @vite(['resources/js/movimientos/mvAgrupacionSala.js'])
@endsection