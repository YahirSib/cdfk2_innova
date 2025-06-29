<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</head>
<body class="bg-gray-800 flex justify-center items-center h-screen p-6">
    <div class="w-full max-w-sm bg-white rounded-lg shadow-md p-6">
        <div class="my-2 w-full justify-center items-center flex">
            <img src="{{ asset('images/innova_color.png') }}" alt="" width="130px">
        </div>
        
        <div id="alert-container"></div>
        <form id="loginForm" action="{{ route('login.submit') }}" class="mt-3">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input type="email" name="email" id="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-gray-800 text-white py-2 px-4 rounded hover:bg-gray-700 cursor-pointer">Ingresar</button>
        </form>
    </div>

     @vite(['resources/js/auth/auth.js'])

</body>
</html>
