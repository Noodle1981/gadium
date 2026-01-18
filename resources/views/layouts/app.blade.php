<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50" 
          x-data="{ 
              sidebarOpen: false, 
              sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
              toggleSidebar() {
                  this.sidebarCollapsed = !this.sidebarCollapsed;
                  localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
              }
          }">
        <div class="flex flex-col lg:flex-row min-h-screen">
            <!-- Sidebar -->
            <livewire:layout.sidebar />

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col transition-all duration-300"
                 :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'">
                
                <!-- Page Heading (Optional, can be used for extra info) -->
                @if (isset($header))
                    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-20 border-b border-gray-200 lg:h-20 flex items-center mt-16 lg:mt-0">
                        <div class="w-full max-w-7xl mx-auto py-4 px-6 sm:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="flex-1 p-6 lg:p-8 mt-16 lg:mt-0">
                    {{ $slot }}
                </main>

                <!-- Footer (Optional) -->
                <footer class="py-6 px-8 text-center text-xs text-gray-400">
                    &copy; {{ date('Y') }} Gadium Industrial SaaS. Todos los derechos reservados.
                </footer>
            </div>
        </div>
    </body>
</html>
