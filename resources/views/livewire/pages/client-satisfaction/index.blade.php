<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    //
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Satisfacción de Clientes') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Placeholder Content -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-3xl p-12 border border-purple-200 shadow-sm">
            <div class="text-center max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-purple-500 text-white rounded-full mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-black text-gray-900 mb-4">Módulo en Desarrollo</h3>
                <p class="text-gray-600 text-lg mb-6">
                    Este módulo permitirá gestionar encuestas de satisfacción de clientes. 
                    Podrás completar encuestas y los resultados alimentarán los dashboards de Grafana.
                </p>
                <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-sm border border-purple-200">
                    <svg class="w-5 h-5 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Contenido próximamente</span>
                </div>
            </div>
        </div>
    </div>
</div>
