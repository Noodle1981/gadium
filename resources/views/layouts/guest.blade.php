<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gadium') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900 dark:text-white bg-slate-900 relative">
        <!-- Fondo Industrial (Patrón o Gradiente sutil) -->
        <div class="fixed inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 -z-10"></div>


        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- El logo se moverá dentro de la tarjeta de login para un diseño más integrado -->
            {{ $slot }}
        </div>
    </body>
</html>

