<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    //
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Satisfacción del Personal') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Placeholder Content -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-3xl p-12 border border-green-200 shadow-sm">
            <div class="text-center max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-500 text-white rounded-full mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-black text-gray-900 mb-4">Módulo en Desarrollo</h3>
                <p class="text-gray-600 text-lg mb-6">
                    Este módulo permitirá gestionar encuestas de satisfacción del personal. 
                    Los empleados podrán completar encuestas y los resultados alimentarán los dashboards de Grafana.
                </p>
                <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-sm border border-green-200">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Contenido próximamente</span>
                </div>
            </div>
        </div>
    </div>
</div>
