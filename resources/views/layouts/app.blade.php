<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- CSS y scripts comunes -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</head>
<body class="bg-neutral-100">
    <div class="container">
        <!-- Menú de navegación -->
        <x-navbar :menu="$menu"/>
        <!-- Contenido dinámico -->
        <main class="p-4 sm:ml-64 mt-14 w-full min-h-[90vh] sm:w-[calc(99vw-17rem)] bg-neutral-100">
            @yield('content')
        </main>
    </div>
</body>
@yield('script')
</html>