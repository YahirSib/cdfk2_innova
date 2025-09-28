@extends('layouts.app')

@section('title', 'Gestión de Piezas')

@section('content')

<style>

    .switch-container {
        width: 70px;
        height: 34px;
    }

    .switch-slider{
        height: 100%;
    }

    .switch-input:checked + .switch-slider {
        background-color: #4ade80; /* Tailwind green-400 */
    }

    .switch-slider::before {
        content: "OFF";
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280; /* Tailwind gray-500 */
        font-size: 12px;
        font-weight: bold;
        position: absolute;
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        border-radius: 50%;
        transition: 0.4s;
    }

    .switch-input:checked + .switch-slider::before {
        transform: translateX(36px);
        content: "ON";
        color: #22c55e; /* Tailwind green-500 */
    }
</style>
<!-- Meta tags para las rutas -->
<meta name="datatable" content="{{ route('piezas.datatable') }}">
<meta name="edit" content="{{ route('piezas.edit', ['id' => '__ID__']) }}">
<meta name="delete" content="{{ route('piezas.delete', ['id' => '__ID__']) }}">
<meta name="store" content="{{ route('piezas.store') }}">
<meta name="update" content="{{ route('piezas.update')}}">

<div class="container w-full p-4">
    <div class="w-full mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Mantenimiento de piezas</h1>
        <p class="text-gray-600">Configuración de piezas para salas</p>
    </div>
    <!-- Formulario -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form id="frmPiezas" action="{{ route('piezas.store') }}" method="POST">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4 mb-6">
                <!-- Nombre del empleado-->
                <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-2">
                    <label for="codigo" class="block mb-2 text-sm font-medium text-gray-900">Codigo<span class="text-red-500">(*)</span></label>
                    <input type="text" id="codigo" name="codigo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Codigo de la pieza">
                </div>
                <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-2">
                    <label for="nombre" class="block mb-2 text-sm font-medium text-gray-900">Nombre <span class="text-red-500">(*)</span> </label>
                    <input type="text" id="nombre" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Nombre de la pieza">
                </div>

                <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-2">
                    <label for="estado" class="block mb-2 text-sm font-medium text-gray-900">Estado <span class="text-red-500">(*)</span></label>
                    <select id="estado" name="estado" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="0"> Seleccione una opción </option>
                        <option value="1">Activo</option>
                        <option value="2">Inactivo</option>
                    </select>
                </div>

                <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-1">
                    <label for="existencia" class="block mb-2 text-sm font-medium text-gray-900">Existencia </label>
                    <input type="text" readonly id="existencia" name="existencia" class="bg-red-50 border border-red-300 text-red-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5" placeholder="0">
                </div>

                <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-1">
                    <label for="individual" class="block mb-2 text-sm font-medium text-gray-900">Individual</label>
                    <label class="relative inline-block switch-container">
                        <input type="checkbox" id="individual" name="individual" class="switch-input hidden">
                        <span class="switch-slider block rounded-full bg-gray-500 transition-all duration-300 ease-in-out"></span>
                    </label>
                </div>
                <!-- Tipo -->
                <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-2">
                    <label for="costo_cacastero" class="block mb-2 text-sm font-medium text-gray-900">Costo Cacastero<span class="text-red-500">(*)</span> </label>
                    <input type="text" id="costo_cacastero" name="costo_cacastero" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el costo por pieza">
                </div>
                
                <!-- Dui -->
                <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-2">
                    <label for="costo_tapicero" class="block mb-2 text-sm font-medium text-gray-900">Costo Tapicero<span class="text-red-500">(*)</span> </label>
                    <input type="text" id="costo_tapicero" name="costo_tapicero" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el costo por pieza">
                </div>
                <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4">
                    <label for="descripcion" class="block mb-2 text-sm font-medium text-gray-900">Descipción de la pieza  </label>
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
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Listado de piezas</h2>
        <div class="relative overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700" id="tblPiezas">
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
    @vite(['resources/js/mantenimientos/mtPiezas.js'])
@endsection