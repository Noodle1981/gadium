<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>403 - Acceso Denegado | Gadium</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900 dark:text-gray-100 bg-slate-900 h-screen overflow-hidden relative">
        
        <!-- Fondo con Gradiente -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-black -z-10"></div>
        
        <!-- Elemento de Decoración (Círculo difuso - Color Rojo/Carmesí para indicar prohibición) -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-red-500/20 rounded-full blur-[100px] -z-10"></div>

        <div class="min-h-screen flex flex-col items-center justify-center p-6 text-center">
            
            <!-- Icono / Número -->
            <div class="relative mb-8">
                <h1 class="text-9xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-orange-400 opacity-20 select-none">
                    403
                </h1>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-24 h-24 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>

            <!-- Texto Principal -->
            <h2 class="text-3xl font-bold text-white mb-4 tracking-tight">
                Acceso Denegado
            </h2>
            
            <p class="text-slate-400 text-lg max-w-md mb-8 leading-relaxed">
                Lo sentimos, no tienes permisos suficientes para acceder a esta sección.
                <br>
                Si crees que es un error, contacta al administrador.
            </p>

            <!-- Botón de Acción -->
            <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-500 border border-transparent rounded-lg font-semibold text-white tracking-wide transition-all duration-300 shadow-lg shadow-red-500/30 hover:shadow-red-500/50 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al Inicio
            </a>

            <!-- Footer sutil -->
            <div class="mt-12 text-slate-600 text-sm">
                Error Code: 403 | Gadium System v1.0.0
            </div>
        </div>
    </body>
</html>
