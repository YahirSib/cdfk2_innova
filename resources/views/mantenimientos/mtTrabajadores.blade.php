@extends('layouts.app')

@section('title', 'Gestión de Trabajadores')

@section('content')
<!-- Meta tags para las rutas -->
<meta name="datatable" content="{{ route('trabajadores.datatable') }}">
<meta name="edit" content="{{ route('trabajadores.edit', ['id' => '__ID__']) }}">
<meta name="delete" content="{{ route('trabajadores.delete', ['id' => '__ID__']) }}">
<meta name="store" content="{{ route('trabajadores.store') }}">
<meta name="update" content="{{ route('trabajadores.update')}}">

<div class="container w-full p-4">
    <div class="w-full mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Mantenimiento de Trabajadores</h1>
        <p class="text-gray-600">Configuración de trabajadores</p>
    </div>
    <!-- Formulario -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form id="frmTrabajadores" action="{{ route('trabajadores.store') }}" method="POST">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                <!-- Nombre del empleado-->
                <div>
                    <label for="nombre1" class="block mb-2 text-sm font-medium text-gray-900">Primer Nombre <span class="text-red-500">(*)</span></label>
                    <input type="text" id="nombre1" name="nombre1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el primer nombre">
                </div>
                <div>
                    <label for="nombre2" class="block mb-2 text-sm font-medium text-gray-900">Segundo Nombre</label>
                    <input type="text" id="nombre2" name="nombre2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el segundo nombre">
                </div>
                <div>
                    <label for="apellido1" class="block mb-2 text-sm font-medium text-gray-900">Primer apellido <span class="text-red-500">(*)</span> </label>
                    <input type="text" id="apellido1" name="apellido1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el primer apellido">
                </div>
                <div>
                    <label for="apellido2" class="block mb-2 text-sm font-medium text-gray-900">Segundo apellido  </label>
                    <input type="text" id="apellido2" name="apellido2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el segundo apellido">
                </div>

                <div>
                    <label for="edad" class="block mb-2 text-sm font-medium text-gray-900">Edad <span class="text-red-500">(*)</span> </label>
                    <input type="number" id="edad" name="edad" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese la edad">
                </div>
                
                <!-- Tipo -->
                <div>
                    <label for="tipo" class="block mb-2 text-sm font-medium text-gray-900">Tipo <span class="text-red-500">(*)</span> </label>
                    <select id="tipo" name="tipo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="0"> Seleccione una opción </option>
                        <option value="1">Tapicero</option>
                        <option value="2">Carpintero</option>
                    </select>
                </div>
                
                <!-- Dui -->
                <div>
                    <label for="dui" class="block mb-2 text-sm font-medium text-gray-900">Dui</label>
                    <input type="text" id="dui" name="dui" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el dui">
                </div>

                <div>
                    <label for="telefono" class="block mb-2 text-sm font-medium text-gray-900">Telefono</label>
                    <input type="text" id="telefono" name="telefono" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el telefono">
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
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Listado de Trabajadores</h2>
        <div class="relative overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700" id="tblTrabajadores">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">N°</th>
                        <th class="px-4 py-2 text-left">Nombre completo</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection
    
@section('script')
    @vite(['resources/js/mantenimientos/mtTrabajadores.js'])
@endsection