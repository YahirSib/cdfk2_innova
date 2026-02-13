@extends('layouts.app')

@section('title', 'Reporte por Cacastero')

@section('content')

    <div class="container mx-auto w-full p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Reporte por Cacastero</h1>
                <p class="text-gray-500 mt-1">Visualización de movimientos por cacastero.</p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">

            <form id="formReporteCacastero" action="{{ route('reporte-cacastero.pdf') }}" target="_blank" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">

                    <fieldset class="border-l-4 border-blue-500 pl-4 py-2 bg-gray-50 rounded-r-lg">
                        <legend class="text-lg font-semibold text-gray-700 mb-4">Cacastero <span
                                class="text-red-500">*</span></legend>

                        <div class="grid grid-cols-1 gap-3">
                            <label for="id_trabajador" class="block mb-2 text-sm font-medium text-gray-900">Seleccione un
                                Cacastero</label>
                            <select id="id_trabajador" name="id_trabajador"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">-- Seleccione --</option>
                                @foreach($cacasteros as $c)
                                    <option value="{{ $c->id_trabajador }}">{{ $c->nombre1 }} {{ $c->apellido1 }}
                                        ({{ $c->dui }})</option>
                                @endforeach
                            </select>
                            @error('id_trabajador')
                                <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </fieldset>

                    <fieldset class="border-l-4 border-green-500 pl-4 py-2 bg-gray-50 rounded-r-lg">
                        <legend class="text-lg font-semibold text-gray-700 mb-4">Rango de Fechas <span
                                class="text-gray-400 text-sm">(Opcional)</span></legend>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="fecha_inicio" class="block mb-2 text-sm font-medium text-gray-900">Desde</label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                            <div>
                                <label for="fecha_fin" class="block mb-2 text-sm font-medium text-gray-900">Hasta</label>
                                <input type="date" id="fecha_fin" name="fecha_fin"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" id="btnGenerarReporte"
                        class="text-white bg-gray-800 hover:bg-gray-900 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-8 py-3 focus:outline-none transition-all shadow-lg hover:shadow-xl flex items-center mr-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Reporte General
                    </button>

                    <button type="submit" formaction="{{ route('reporte-cacastero.detallado.pdf') }}"
                        class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-8 py-3 focus:outline-none transition-all shadow-lg hover:shadow-xl flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                        Reporte Detallado
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection