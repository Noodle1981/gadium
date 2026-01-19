<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    //
}; ?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Satisfacción de Clientes</h1>
                        <p class="text-orange-100 text-sm">Gestión y análisis de encuestas de satisfacción.</p>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8 py-12">
        <!-- Placeholder Content -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-3xl p-12 border border-orange-200 shadow-sm">
            <div class="text-center max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-500 text-white rounded-full mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-black text-gray-900 mb-4">Módulo en Desarrollo</h3>
                <p class="text-gray-600 text-lg mb-6">
                    Este módulo permitirá gestionar encuestas de satisfacción de clientes. 
                    Podrás completar encuestas y los resultados alimentarán los dashboards de Grafana.
                </p>
                <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-sm border border-orange-200">
                    <svg class="w-5 h-5 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Contenido próximamente</span>
                </div>
            </div>
        </div>
    </div>
</div>
