<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    // Lógica para viewer
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Viewer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-600 italic">Bienvenido al visor de reportes. Aquí podrás visualizar las métricas y el avance de los proyectos una vez que se generen datos transaccionales.</p>
                
                <div class="mt-8 p-6 bg-gray-50 border border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                    <span class="text-gray-400">Próximamente: Gráficos de Producción y Calidad</span>
                </div>
            </div>
        </div>
    </div>
</div>
