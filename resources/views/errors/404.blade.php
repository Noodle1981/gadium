<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>404 - Página No Encontrada | Gadium</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900 dark:text-gray-100 bg-slate-900 h-screen overflow-hidden relative">
        
        <!-- Fondo con Gradiente -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-black -z-10"></div>
        
        <!-- Elemento de Decoración (Círculo difuso) -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-500/20 rounded-full blur-[100px] -z-10"></div>

        <div class="min-h-screen flex flex-col items-center justify-center p-6 text-center">
            
            <!-- Icono / Número -->
            <div class="relative mb-8">
                <h1 class="text-9xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400 opacity-20 select-none">
                    404
                </h1>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-24 h-24 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>

            <!-- Texto Principal -->
            <h2 class="text-3xl font-bold text-white mb-4 tracking-tight">
                Página bloqueada o inexistente
            </h2>
            
            <p class="text-slate-400 text-lg max-w-md mb-8 leading-relaxed">
                Parece que esta ruta no existe o fue movida.
                <br>
            </p>

            <!-- Botón de Acción -->
            <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-500 border border-transparent rounded-lg font-semibold text-white tracking-wide transition-all duration-300 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al Inicio
            </a>

            <!-- Footer sutil -->
            <div class="mt-12 text-slate-600 text-sm">
                Error Code: 404 | Gadium System v1.0.0
            </div>
        </div>
    </body>
</html>
