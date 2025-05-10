@extends('layouts.app')

@section('title', 'Gestión de Perfiles')

@section('content')
<div class="container w-full p-4">
    <div class="w-full mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Mantenimiento de Perfiles</h1>
        <p class="text-gray-600">Creación y asignación de permisos a perfiles</p>
    </div>
    <!-- Formulario -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Perfil -->
                <div>
                    <label for="perfil" class="block mb-2 text-sm font-medium text-gray-900">Perfil</label>
                    <input type="text" id="perfil" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese el perfil">
                </div>
                
                <!-- Estado -->
                <div>
                    <label for="estado" class="block mb-2 text-sm font-medium text-gray-900">Estado</label>
                    <select id="estado" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option selected>Activo</option>
                        <option>Inactivo</option>
                    </select>
                </div>
                
                <!-- Permisos -->
                <div>
                    <label for="permisos" class="block mb-2 text-sm font-medium text-gray-900">Permisos</label>
                    <input type="text" id="permisos" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese los permisos">
                </div>
            </div>
            
            <!-- Botón Guardar -->
            <div class="flex justify-center">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                    Guardar
                </button>
            </div>
        </form>
    </div>

    <!-- DataTable -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Listado de Usuarios</h2>
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Perfil
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Permisos
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white border-b">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            Administrador
                        </th>
                        <td class="px-6 py-4">
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Activo</span>
                        </td>
                        <td class="px-6 py-4">
                            Todos
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="font-medium text-blue-600 hover:underline me-2">Editar</a>
                            <a href="#" class="font-medium text-red-600 hover:underline">Eliminar</a>
                        </td>
                    </tr>
                    <tr class="bg-white border-b">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            Usuario
                        </th>
                        <td class="px-6 py-4">
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Activo</span>
                        </td>
                        <td class="px-6 py-4">
                            Lectura
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="font-medium text-blue-600 hover:underline me-2">Editar</a>
                            <a href="#" class="font-medium text-red-600 hover:underline">Eliminar</a>
                        </td>
                    </tr>
                    <tr class="bg-white">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            Invitado
                        </th>
                        <td class="px-6 py-4">
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Inactivo</span>
                        </td>
                        <td class="px-6 py-4">
                            Limitado
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="font-medium text-blue-600 hover:underline me-2">Editar</a>
                            <a href="#" class="font-medium text-red-600 hover:underline">Eliminar</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection