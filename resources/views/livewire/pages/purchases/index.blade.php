<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    //
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Compras de Materiales') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Placeholder Content -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-3xl p-12 border border-blue-200 shadow-sm">
            <div class="text-center max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-500 text-white rounded-full mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-black text-gray-900 mb-4">Módulo en Desarrollo</h3>
                <p class="text-gray-600 text-lg mb-6">
                    Este módulo permitirá gestionar las compras de materiales. 
                    Podrás importar datos, realizar entradas manuales y alimentar los dashboards de Grafana.
                </p>
                <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-sm border border-blue-200">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Contenido próximamente</span>
                </div>
            </div>
        </div>
    </div>
</div>
