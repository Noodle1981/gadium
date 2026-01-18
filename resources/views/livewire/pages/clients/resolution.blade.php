<?php

use function Livewire\Volt\{state, on, mount};
use App\Models\Client;
use App\Services\ClientNormalizationService;
use Illuminate\Support\Collection;

state([
    'clientName' => '',
    'similarClients' => [],
    'successMessage' => null,
    'errorMessage' => null,
]);

mount(function ($client_name = '') {
    $this->clientName = $client_name ?: request()->query('client_name', '');
    $this->loadSimilarClients();
});

$loadSimilarClients = function () {
    if (empty($this->clientName)) {
        $this->similarClients = collect();
        return;
    }

    $service = new ClientNormalizationService();
    $this->similarClients = $service->findSimilarClients($this->clientName);
};

$resolve = function ($action, $clientId = null) {
    try {
        $service = new ClientNormalizationService();
        
        if ($action === 'link') {
            if (!$clientId) {
                $this->errorMessage = 'Debe seleccionar un cliente para vincular.';
                return;
            }
            
            $service->createAlias($clientId, $this->clientName);
            $this->successMessage = "Cliente vinculado exitosamente. '{$this->clientName}' ahora es un alias.";
        } else {
            $client = Client::create([
                'nombre' => $this->clientName,
            ]);
            $this->successMessage = "Nuevo cliente creado: {$client->nombre}";
        }

        // Limpiar después de resolver
        $this->clientName = '';
        $this->similarClients = collect();

    } catch (\Exception $e) {
        $this->errorMessage = 'Error al resolver: ' . $e->getMessage();
    }
};

?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Resolución de Clientes</h1>
                        <p class="text-orange-100 text-sm">Normalización inteligente de nombres de clientes</p>
                    </div>
                    <div class="hidden md:block">
                        <svg class="w-12 h-12 text-orange-300 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @if ($successMessage)
        <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900/30 dark:text-green-400">
            <p class="text-sm font-medium">{{ $successMessage }}</p>
        </div>
    @endif

    @if ($errorMessage)
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 dark:bg-red-900/30 dark:text-red-400">
            <p class="text-sm font-medium">{{ $errorMessage }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
        <div class="p-6">
            @if(empty($clientName))
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No hay clientes pendientes de resolución</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Todos los nombres han sido normalizados o vinculados.</p>
                </div>
            @else
                <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl">
                    <p class="text-sm text-yellow-800 dark:text-yellow-300">
                        <strong class="font-bold">Nombre del cliente detectado:</strong> {{ $clientName }}
                    </p>
                    <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-2">
                        Se han encontrado {{ count($similarClients) }} clientes con nombres similares en la base de datos.
                    </p>
                </div>

                @if(count($similarClients) > 0)
                    <div class="space-y-4 mb-8">
                        <h4 class="font-semibold text-gray-700 dark:text-gray-300 uppercase text-xs tracking-wider">Sugerencias de vinculación:</h4>
                        
                        @foreach($similarClients as $item)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl hover:shadow-md transition">
                                <div>
                                    <div class="flex items-center">
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $item['client']->nombre }}</p>
                                        <span class="ml-3 px-2 py-1 text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                                            {{ $item['similarity'] }}% similitud
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        ID: {{ $item['client']->id }} | Registrado hace: {{ $item['client']->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <button 
                                    wire:click="resolve('link', {{ $item['client']->id }})"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm"
                                >
                                    Vincular
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mb-8 p-6 bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 rounded-xl text-center">
                        <p class="text-gray-500 dark:text-gray-400 italic">No se encontraron clientes similares suficientes para sugerir una vinculación automática.</p>
                    </div>
                @endif

                <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h4 class="font-bold text-gray-800 dark:text-white">¿Es un cliente nuevo?</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Si ninguna de las sugerencias es correcta, puede crear un registro nuevo.</p>
                        </div>
                        <button 
                            wire:click="resolve('create')"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-bold shadow-sm"
                        >
                            Crear Nuevo Cliente
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
