@extends('layouts.app')

@section('title', 'Gestión de Trabajadores')

@section('content')


<div class="container w-full p-4">
    <div class="w-full mb-4 flex justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Nota de Pieza</h1>
            <p id="p-indicador" class="text-gray-600">Creación de documento</p>
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
    <div id="default-tab-content" class="p-4 bg-white rounded-lg shadow-md">
        <div class="hidden" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <form id="frmCrear"  method="POST">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Nombre del empleado-->
                    <div>
                        <label for="fecha" class="block mb-2 text-sm font-medium text-gray-900">Fecha Documento</label>
                        <input type="date" id="fecha" name="fecha" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="{{ date('Y-m-d') }}" readonly>
                    </div>
                    <div>
                        <label for="correlativo" class="block mb-2 text-sm font-medium text-gray-900">N° Documento </label>
                        <input type="text" id="correlativo" name="correlativo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    <div class="col-span-1 sm:col-span-2  lg:col-span-2">
                        <label for="cacastero" class="block mb-2 text-sm font-medium text-gray-900">Cacastero <span class="text-red-500">(*)</span></label>
                        <input type="text" id="cacastero" name="cacastero" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Buscar cacastero resposable de la pieza">
                    </div>
                    <div class=" col-span-1 sm:col-span-2  lg:col-span-4">
                        <label for="comentario" class="block mb-2 text-sm font-medium text-gray-900">Comentario </label>
                        <textarea type="text" id="comentario" name="comentario" class="bg-gray-50 h-auto border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el comentario"></textarea>
                    </div>
                </div>
                
                <!-- Botón Guardar -->
                <div class="flex justify-evenly items-center wrap ">
                    <button id="btnForm" type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center m-1">
                        Crear
                    </button>
                </div>
            </form>
        </div>
        <div class="hidden " id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
            <p>Detalles</p>
        </div>
        <div class="hidden " id="settings" role="tabpanel" aria-labelledby="settings-tab">
            <p>Finalizar</p>
        </div>
    </div>

</div>
@endsection
    
@section('script')
    @vite(['resources/js/mantenimientos/mtTrabajadores.js'])
@endsection