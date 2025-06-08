@extends('layouts.app')

@section('title', 'Creacion de Nota de Pieza')

@section('content')

<meta name="store" content="{{ route('nota-pieza.store') }}">
<meta name="update" content="{{ route('nota-pieza.update') }}">

<div class="container w-full p-4">
    <div class="w-full mb-4 flex justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Nota de Pieza</h1>
            <p id="p-indicador" class="text-gray-600"> {{ $data['action'] == 'crear' ? 'Creación de documento' : 'Edición de documento' }} </p>
        </div>
        <div>
            <a id="btnNuevo" href="{{ route('nota-pieza.index') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center m-1">
                    Regresar
            </a>
        </div>
    </div>
    
    <!-- TABS -->
    <div class="bg-white px-6 flex justify-center rounded-lg shadow-md mb-4">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="default-tab" data-tabs-toggle="#default-tab-content"  role="tablist">
            <li class="me-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="profile-tab" data-tabs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">  <i class='text-xl bx bxs-file' ></i></button>
            </li>
            <li class="me-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="dashboard-tab" data-tabs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="false"><i class='text-xl bx bx-list-plus'></i></button>
            </li>
            <li class="me-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="settings-tab" data-tabs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false"><i class='text-xl bx bx-import'></i></button>
            </li>
        </ul>
    </div>
    <div id="default-tab-content" >
        <div class="hidden p-4 bg-white rounded-lg shadow-md" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <form id="frmCrear" data-id="{{ $data['action'] == 'crear' ? $data['notaPieza']->id_movimiento : '' }}"  method="POST">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Nombre del empleado-->
                    <div>
                        <label for="fecha" class="block mb-2 text-sm font-medium text-gray-900">Fecha Documento</label>
                        <input type="date" id="fecha" name="fecha" class="bg-red-50 border border-red-300 text-red-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5" value="{{ $data['action'] == 'crear' ? date('Y-m-d') : date('Y-m-d', strtotime($data['notaPieza']->fecha_ingreso)) }}" readonly>
                    </div>
                    <div>
                        <label for="correlativo" class="block mb-2 text-sm font-medium text-gray-900">N° Documento </label>
                        <input type="text" id="correlativo" name="correlativo" class="bg-red-50 border border-red-300 text-red-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5" value="{{ $data['action'] == 'crear' ? $data['correlativo'] : $data['notaPieza']->correlativo}}" readonly>
                    </div>
                    <div class="col-span-1 sm:col-span-2  lg:col-span-2">
                        <label for="cacastero" class="block mb-2 text-sm font-medium text-gray-900">Cacastero <span class="text-red-500">(*)</span></label>
                        <select id="cacastero" name="cacastero" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">Seleccione un cacastero</option>
                            @if($data['action'] == 'crear')
                                @foreach ($data['carpintero'] as $cacastero)
                                    <option value="{{ $cacastero->id_trabajador }}">{{ $cacastero->nombre1 }} {{ $cacastero->nombre2 }} {{ $cacastero->apellido1 }} {{$cacastero->apellido2}}</option>
                                @endforeach
                            @else
                                <option selected value="{{ $data['notaPieza']->cacastero }}"> {{$data['notaPieza']->nombre_cacastero}}</option>
                            @endif
                        </select>
                    </div>
                    <div class=" col-span-1 sm:col-span-2  lg:col-span-4">
                        <label for="comentario" class="block mb-2 text-sm font-medium text-gray-900">Comentario </label>
                        <textarea type="text" id="comentario" name="comentario" class="bg-gray-50 h-auto border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el comentario">{{ $data['action'] == 'crear' ? '' : $data['notaPieza']->comentario }}</textarea>
                    </div>
                </div>
                
                <!-- Botón Guardar -->
                <div class="flex justify-evenly items-center wrap ">
                    <button id="btnForm" type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center m-1">
                        {{ $data['action'] == 'crear' ? 'Crear' : 'Actualizar' }}
                    </button>
                </div>
            </form>
        </div>
        <div class="hidden " id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
            <form class="grid grid-cols-1 md:grid-cols-5 lg:grid-cols-10 gap-2 bg-white p-6 rounded-lg shadow-md mb-8" id="frmAnexarPieza" method="POST" action="" class="mb-4">
                <div class="md:col-span-2 lg:col-span-4" mb-2>
                    <label for="pieza_sala" class="block mb-2  md:text-left  text-sm font-medium text-gray-900">Pieza<span class="text-red-500">(*)</span> </label>
                    <input type="text" id="pieza_sala" name="pieza_sala" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Buscar pieza">
                </div>

                <div class="md:col-span-2 lg:col-span-4 mb-2">
                    <label for="cantidad" class="block mb-2 text-sm md:text-left font-medium text-gray-900">Cantidad<span class="text-red-500">(*)</span> </label>
                    <input type="text" id="cantidad" name="cantidad" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese la cantidad de piezas">
                </div>

                <div class="md:col-span-1 lg:col-span-2 mb-2">
                    <label for="comentario" class="block mb-2 text-sm font-medium text-gray-900"> </label>
                    <button id="btnGuardarPieza" type="submit" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-2xl w-full px-5 py-2.5 text-center m-1">
                        +
                    </button>
                </div>

            </form>

            <div class="text-left mt-4 mb-2 bg-white p-6 rounded-lg shadow-md mb-8">
                <div id="conformacion_piezas" class="overflow-y-auto max-h-60">
                    <table class="table-auto w-lg sm:w-full p-3">
                        <thead>
                            <tr>
                                <th class="px-2 py-2">Pieza</th>
                                <th class="px-2 py-2">Cantidad</th>
                                <th class="px-2 py-2"></th>
                            </tr>
                        </thead>
                        <tbody id="piezas_table_body">
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="hidden " id="settings" role="tabpanel" aria-labelledby="settings-tab">
            <p>Finalizar</p>
        </div>
    </div>

</div>
@endsection
    
@section('script')
    @vite(['resources/js/movimientos/mvNotaPieza.js'])
@endsection