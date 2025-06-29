@extends('layouts.app')

@section('title', 'Gestión de Perfiles')

@section('content')
<!-- Meta tags para las rutas -->
<meta name="datatable" content="{{ route('perfil.datatable') }}">
<meta name="edit" content="{{ route('perfil.edit', ['id' => '__ID__']) }}">
<meta name="delete" content="{{ route('perfil.delete', ['id' => '__ID__']) }}">
<meta name="store" content="{{ route('perfil.store') }}">
<meta name="update" content="{{ route('perfil.update')}}">

<div class="container w-full p-4">
    <div class="w-full mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Mantenimiento de perfiles</h1>
        <p class="text-gray-600">Configuración de perfiles</p>
    </div>
    <!-- Formulario -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form id="frmPerfil" action="{{ route('perfil.store') }}" method="POST">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Nombre del empleado-->
                <div class="col-span-2">
                    <label for="nombre" class="block mb-2 text-sm font-medium text-gray-900">Nombre<span class="text-red-500">(*)</span></label>
                    <input type="text" id="nombre" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Nombre del perfil">
                </div>
                
                <div class="col-span-2">
                    <label for="estado" class="block mb-2 text-sm font-medium text-gray-900">Estado <span class="text-red-500">(*)</span></label>
                    <select id="estado" name="estado" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="0"> Seleccione una opción </option>
                        <option value="1">Activo</option>
                        <option value="2">Inactivo</option>
                    </select>
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
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Listado de perfiles</h2>
        <div class="relative overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700" id="tblPerfil">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Id</th>
                        <th class="px-4 py-2 text-left">Nombre</th>
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
    @vite(['resources/js/mantenimientos/mtPerfil.js'])
@endsection