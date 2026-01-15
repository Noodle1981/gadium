<?php

use Livewire\Volt\Component;

new class extends Component
{
    //
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Satisfacción de Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 bg-gradient-to-r from-pink-600 to-pink-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="px-8 py-12">
                     <h1 class="text-4xl font-bold text-white mb-2">Satisfacción de Clientes</h1>
                     <p class="text-pink-100 text-lg">Feedback y fidelización</p>
                </div>
            </div>
        </div>
    </div>
</div>
