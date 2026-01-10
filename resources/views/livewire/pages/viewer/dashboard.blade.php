<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    // Lógica para viewer
}; ?>

<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Inteligencia de Negocios') }}
            </h2>
            <div class="flex space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    API Online
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Hero Section / Grafana Access -->
        <div class="bg-gray-950 rounded-3xl overflow-hidden shadow-2xl relative">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-600/20 to-transparent"></div>
            <div class="p-8 lg:p-12 relative z-10 flex flex-col lg:flex-row items-center justify-between">
                <div class="max-w-2xl text-center lg:text-left mb-8 lg:mb-0">
                    <h3 class="text-3xl font-extrabold text-white mb-4 leading-tight">Torre de Control Grafana</h3>
                    <p class="text-gray-400 text-lg mb-8 uppercase tracking-widest font-medium">Visualización en tiempo real de KPIs industriales</p>
                    
                    <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                        <a href="#" class="inline-flex items-center px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-xl shadow-orange-900/40">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Abrir Tablero de Ventas
                        </a>
                        <a href="#" class="inline-flex items-center px-8 py-4 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-2xl transition-all duration-300 transform hover:scale-105 border border-gray-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Tablero de Producción
                        </a>
                    </div>
                </div>

                <div class="bg-gray-900/80 backdrop-blur-md p-6 rounded-3xl border border-gray-800 w-full lg:w-96 shadow-2xl">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-orange-600/20 rounded-lg text-orange-500 mr-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <h4 class="text-white font-bold">API de Integración</h4>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="bg-black/50 p-4 rounded-xl border border-gray-800">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Token de Acceso (Viewer)</p>
                            <code class="text-orange-400 text-xs break-all">1|IWUuRUTrTR6j0b4sKTK5YR1TXMH...</code>
                        </div>
                        <p class="text-sm text-gray-400 leading-relaxed italic">Usa este token en el plugin Infinity de Grafana para conectar con el motor de Gadium.</p>
                        
                        <div class="pt-4 border-t border-gray-800">
                            <a href="{{ url('/api/v1/metrics/sales-concentration') }}" target="_blank" class="text-orange-500 hover:text-orange-400 text-sm font-bold flex items-center transition-colors">
                                Ver Documentación API
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-900 mb-2">Algoritmo Pareto</h4>
                <p class="text-sm text-gray-500 leading-relaxed">Concentración de ventas calculada diariamente para mitigar riesgos de dependencia excesiva.</p>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-900 mb-2">Eficiencia Industrial</h4>
                <p class="text-sm text-gray-500 leading-relaxed">Seguimiento de horas ponderadas y cumplimiento de objetivos de manufactura en tiempo real.</p>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-900 mb-2">Gobierno de Datos</h4>
                <p class="text-sm text-gray-500 leading-relaxed">Hash SHA-256 para integridad de datos y normalización inteligente de clientes para reportes precisos.</p>
            </div>
        </div>
    </div>
</div>
