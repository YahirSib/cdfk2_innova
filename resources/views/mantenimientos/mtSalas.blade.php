@extends('layouts.app')

@section('title', 'Gestión de Salas')

@section('content')
<!-- Meta tags para las rutas -->
<meta name="datatable" content="{{ route('salas.datatable') }}">
<meta name="edit" content="{{ route('salas.edit', ['id' => '__ID__']) }}">
<meta name="delete" content="{{ route('salas.delete', ['id' => '__ID__']) }}">
<meta name="store" content="{{ route('salas.store') }}">
<meta name="update" content="{{ route('salas.update')}}">
<meta name="piezas" content="{{ route('salas.getPiezas') }}">
<meta name="store_pieza" content="{{ route('salas.savePiezas') }}">
<meta name="piezas_sala" content="{{ route('salas.getPiezasBySala') }}">
<meta name="delete_pieza" content="{{ route('salas.deletePiezaBySala', ['id' => '__ID__']) }}">
<meta name="update_pieza" content="{{ route('salas.updatePiezaBySala')}}">

<div class="container w-full p-4">
    <div class="w-full mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Mantenimiento de Salas</h1>
        <p class="text-gray-600">Configuración de salas</p>
    </div>
    <!-- Formulario -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form id="frmSalas" action="{{ route('salas.store') }}" method="POST">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                <!-- Nombre del empleado-->
                <div>
                    <label for="codigo" class="block mb-2 text-sm font-medium text-gray-900">Codigo<span class="text-red-500">(*)</span></label>
                    <input type="text" id="codigo" name="codigo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Codigo de la sala">
                </div>
                <div>
                    <label for="nombre" class="block mb-2 text-sm font-medium text-gray-900">Nombre <span class="text-red-500">(*)</span> </label>
                    <input type="text" id="nombre" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Nombre de la sala">
                </div>
                
                <div>
                    <label for="estado" class="block mb-2 text-sm font-medium text-gray-900">Estado <span class="text-red-500">(*)</span></label>
                    <select id="estado" name="estado" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="0"> Seleccione una opción </option>
                        <option value="1">Activo</option>
                        <option value="2">Inactivo</option>
                    </select>
                </div>

                <div>
                    <label for="existencia" class="block mb-2 text-sm font-medium text-gray-900">Existencia </label>
                    <input type="text" readonly id="existencia" name="existencia" class="bg-red-50 border border-red-300 text-red-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5" placeholder="Información de existencia">
                </div>
                
                <!-- Tipo -->
                <div>
                    <label for="costo_cacastero" class="block mb-2 text-sm font-medium text-gray-900">Costo Cacastero<span class="text-red-500">(*)</span> </label>
                    <input type="text" id="costo_cacastero" name="costo_cacastero" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el costo por sala">
                </div>
                
                <!-- Dui -->
                <div>
                    <label for="costo_tapicero" class="block mb-2 text-sm font-medium text-gray-900">Costo Tapicero<span class="text-red-500">(*)</span> </label>
                    <input type="text" id="costo_tapicero" name="costo_tapicero" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el costo por sala">
                </div>
                <div class=" col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-2">
                    <label for="descripcion" class="block mb-2 text-sm font-medium text-gray-900">Descipción de la sala  </label>
                    <textarea type="text" id="descripcion" name="descripcion" class="bg-gray-50 h-auto border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el primer apellido"></textarea>
                </div>
            </div>
            
            <!-- Botón Guardar -->
            <div class="flex justify-evenly items-center wrap ">
                <button id="btnForm" type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center m-1">
                    Guardar
                </button>

                <button id="btnCrear" type="submit" class="hidden text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center m-1">
                    Modo Creación
                </button>

            </div>
        </form>
    </div>

    <!-- DataTable -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Listado de salas</h2>
        <div class="relative overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700" id="tblSalas">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">N°</th>
                        <th class="px-4 py-2 text-left">Codigo</th>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Estado</th>
                        <th class="px-4 py-2 text-left">Existencia</th>
                        <th class="px-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection
    
@section('script')
    @vite(['resources/js/mantenimientos/mtSalas.js'])
@endsection