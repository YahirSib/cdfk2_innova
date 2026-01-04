@extends('layouts.app')

@section('title', 'Reporte Disponibilidad')

@section('content')

<div class="container mx-auto w-full p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Reporte Disponibilidad</h1>
            <p class="text-gray-500 mt-1">Generación de reporte de inventario por múltiples criterios.</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        
        <form id="formReporteDisponibilidad" action="{{ route('reporte-disponibilidad.pdf') }}" target="_blank" method="POST">
            @csrf <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                
                <fieldset class="border-l-4 border-blue-500 pl-4 py-2 bg-gray-50 rounded-r-lg">
                    <div class="flex justify-between items-center mb-3">
                        <legend class="text-lg font-semibold text-gray-700">Tipo de Producto <span class="text-red-500">*</span></legend>
                        <button type="button" class=" p-2 text-xs text-blue-600 hover:underline btn-toggle-all" data-target="tipo">Marcar todos</button>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-3">
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-white hover:border-blue-300 transition-colors bg-white">
                            <input type="checkbox" name="tipo[]" value="2" class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 chk-tipo">
                            <span class="ml-3 text-sm font-medium text-gray-700">Pieza Individual</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-white hover:border-blue-300 transition-colors bg-white">
                            <input type="checkbox" name="tipo[]" value="3" class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 chk-tipo">
                            <span class="ml-3 text-sm font-medium text-gray-700">Pieza No Individual</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-white hover:border-blue-300 transition-colors bg-white">
                            <input type="checkbox" name="tipo[]" value="4" class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 chk-tipo">
                            <span class="ml-3 text-sm font-medium text-gray-700">Salas</span>
                        </label>
                    </div>
                    <p class="text-xs text-red-500 mt-2 hidden" id="error-tipo">Seleccione al menos un tipo.</p>
                </fieldset>

                <fieldset class="border-l-4 border-indigo-500 pl-4 py-2 bg-gray-50 rounded-r-lg">
                    <div class="flex justify-between items-center mb-3">
                        <legend class="text-lg font-semibold text-gray-700">Bodegas <span class="text-red-500">*</span></legend>
                        <button type="button" class="p-2 text-xs text-indigo-600 hover:underline btn-toggle-all" data-target="bodega">Marcar todos</button>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-white hover:border-indigo-300 transition-colors bg-white">
                            <input type="checkbox" name="estado[]" value="1" class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2 chk-bodega">
                            <span class="ml-3 text-sm font-medium text-gray-700">Cacastes</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-white hover:border-indigo-300 transition-colors bg-white">
                            <input type="checkbox" name="estado[]" value="2" class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2 chk-bodega">
                            <span class="ml-3 text-sm font-medium text-gray-700">Traslado</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-white hover:border-indigo-300 transition-colors bg-white">
                            <input type="checkbox" name="estado[]" value="3" class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2 chk-bodega">
                            <span class="ml-3 text-sm font-medium text-gray-700">General</span>
                        </label>
                    </div>
                    <p class="text-xs text-red-500 mt-2 hidden" id="error-bodega">Seleccione al menos una bodega.</p>
                </fieldset>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" id="btnGenerarReporte" class="text-white bg-gray-800 hover:bg-gray-900 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-8 py-3 focus:outline-none transition-all shadow-lg hover:shadow-xl flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Generar Reporte PDF
                </button>
            </div>
            
        </form>
    </div>
</div>
@endsection

@section('script')
    {{-- Asumiendo que jQuery ya está cargado globalmente o vía Vite, si no, importarlo aquí --}}
    @vite(['resources/js/reportes/repDisponibilidad.js'])
@endsection