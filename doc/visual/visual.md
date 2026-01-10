---

### ¿Por qué hacerlo así?

1.  **Industrial Glassmorphism**: El vidrio puro (muy transparente) es difícil de leer en entornos industriales donde hay muchos datos. Al usar `bg-white/70` o `bg-slate-900/70`, mantenemos la estética "premium" pero con el contraste necesario para leer tablas de producción o KPIs.
2.  **Sincronización con Volt**: Al definir que la IA debe separar la lógica `@php` del template, aseguras que el archivo `.blade.php` esté organizado según el estándar de **Livewire Volt (Class API)**.
3.  **Consistencia de Marca**: El color `#E8491B` es muy fuerte, por lo que las reglas limitan su uso a botones de acción y estados activos para no cansar la vista.

 solo asegúrate de que en tu archivo `tailwind.config.js` tengas configurada la paleta de colores de `slate` (que es la que mejor combina con el naranja industrial) y que hayas instalado el plugin de `@tailwindcss/forms` para que los inputs se vean limpios.


Estos ejemplos siguen estrictamente las reglas de Livewire Volt (Class API), Tailwind CSS con efecto Glassmorphism y la identidad de Gadium.
1. Componente: Sidebar (Navegación Lateral)
Este componente aplica el efecto de vidrio en el lateral y gestiona el estado activo con el Naranja Gadium.
resources/views/livewire/layout/sidebar.blade.php
code
PHP
<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $activeRoute = 'dashboard';

    public function navigateTo($route)
    {
        $this->activeRoute = $route;
        // Aquí podrías usar $this->redirectRoute($route);
    }
}; ?>

<aside class="fixed left-0 top-0 h-screen w-64 z-40 transition-transform -translate-x-full sm:translate-x-0">
    <!-- Contenedor Glassmorphism -->
    <div class="h-full px-4 py-6 overflow-y-auto bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border-r border-slate-200/50 dark:border-slate-700/50 shadow-2xl flex flex-col">
        
        <!-- Logo Gadium -->
        <div class="flex items-center mb-10 px-2">
            <div class="w-8 h-8 bg-[#E8491B] rounded-lg flex items-center justify-center shadow-lg shadow-orange-500/40">
                <span class="text-white font-bold text-xl">G</span>
            </div>
            <span class="ml-3 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                Gadium<span class="text-[#E8491B]">.</span>
            </span>
        </div>

        <!-- Menú -->
        <nav class="space-y-2 flex-1">
            <button wire:click="navigateTo('dashboard')" 
                @class([
                    'w-full flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 group',
                    'bg-[#E8491B] text-white shadow-lg shadow-orange-500/30' => $activeRoute === 'dashboard',
                    'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-[#E8491B]' => $activeRoute !== 'dashboard',
                ])>
                <x-heroicon-o-squares-2x2 class="w-5 h-5 transition duration-75" />
                <span class="ml-3 font-semibold">Dashboard</span>
            </button>

            <button wire:click="navigateTo('produccion')" 
                @class([
                    'w-full flex items-center p-3 text-sm font-medium rounded-xl transition-all duration-200 group',
                    'bg-[#E8491B] text-white shadow-lg shadow-orange-500/30' => $activeRoute === 'produccion',
                    'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-[#E8491B]' => $activeRoute !== 'produccion',
                ])>
                <x-heroicon-o-cog-6-tooth class="w-5 h-5 transition duration-75" />
                <span class="ml-3 font-semibold">Producción</span>
            </button>
        </nav>

        <!-- Footer del Sidebar (Usuario) -->
        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center p-2 rounded-lg bg-slate-100/50 dark:bg-slate-800/50">
                <div class="w-8 h-8 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                <div class="ml-3 overflow-hidden">
                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">Operador Alpha</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">Turno Mañana</p>
                </div>
            </div>
        </div>
    </div>
</aside>
2. Componente: Stat Card (Tarjeta de Indicador)
Este componente muestra cómo aplicar el vidrio a una tarjeta de datos pequeña, integrando colores funcionales (éxito/error).
resources/views/livewire/components/stat-card.blade.php
code
PHP
<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $title = 'Rendimiento';
    public string $value = '0.00';
    public string $trend = 'up'; // up, down
    public string $percentage = '0%';
}; ?>

<div class="relative group">
    <!-- Glow Efecto (Opcional para resaltar el cristal) -->
    <div class="absolute -inset-0.5 bg-gradient-to-r from-[#E8491B] to-orange-600 rounded-2xl blur opacity-0 group-hover:opacity-10 transition duration-500"></div>
    
    <!-- Card Glassmorphism -->
    <div class="relative bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 p-5 rounded-2xl shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-[#E8491B]" />
            </div>
            
            <!-- Badge de Tendencia -->
            <span @class([
                'text-[10px] font-bold px-2 py-1 rounded-full flex items-center uppercase tracking-wider',
                'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' => $trend === 'up',
                'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' => $trend === 'down',
            ])>
                @if($trend === 'up') <x-heroicon-m-arrow-trending-up class="w-3 h-3 mr-1" /> @else <x-heroicon-m-arrow-trending-down class="w-3 h-3 mr-1" /> @endif
                {{ $percentage }}
            </span>
        </div>

        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $title }}</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $value }}</h3>
        </div>

        <!-- Botón de Acción Rápida (Estilo Gadium) -->
        <button class="mt-4 w-full py-2 text-xs font-bold text-[#E8491B] border border-orange-500/20 rounded-lg hover:bg-[#E8491B] hover:text-white transition-all duration-300">
            Ver detalles
        </button>
    </div>
</div>