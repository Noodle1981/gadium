<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    //
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Tableros de Control') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Placeholder Content -->
        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-3xl p-12 border border-indigo-200 shadow-sm">
            <div class="text-center max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-500 text-white rounded-full mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-black text-gray-900 mb-4">Módulo en Desarrollo</h3>
                <p class="text-gray-600 text-lg mb-6">
                    Este módulo permitirá visualizar tableros de control y dashboards personalizados. 
                    Integración con Grafana para visualización de métricas en tiempo real.
                </p>
                <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-sm border border-indigo-200">
                    <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Contenido próximamente</span>
                </div>
            </div>
        </div>
    </div>
</div>
