<?php

use Livewire\Volt\Component;
use App\Models\Sale;
use App\Models\Client;
use App\Services\ClientNormalizationService;

new class extends Component {
    public $cliente_nombre = '';
    public $fecha = '';
    public $monto = '';
    public $comprobante = '';
    public $moneda = 'USD';
    
    public $searchResults = [];
    public $selectedClientId = null;
    public $showClientSearch = false;

    public function rules()
    {
        return [
            'cliente_nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'comprobante' => 'required|string|max:255',
            'moneda' => 'required|in:USD,ARS,EUR',
        ];
    }

    public function updatedClienteNombre($value)
    {
        if (strlen($value) >= 2) {
            $service = app(ClientNormalizationService::class);
            $this->searchResults = $service->findSimilarClients($value)->take(5)->toArray();
            $this->showClientSearch = !empty($this->searchResults);
        } else {
            $this->searchResults = [];
            $this->showClientSearch = false;
        }
    }

    public function selectClient($clientId, $clientName)
    {
        $this->selectedClientId = $clientId;
        $this->cliente_nombre = $clientName;
        $this->showClientSearch = false;
        $this->searchResults = [];
    }

    public function save()
    {
        $this->validate();

        // Resolve or create client
        $service = app(ClientNormalizationService::class);
        $client = $service->resolveClientByAlias($this->cliente_nombre);

        if (!$client) {
            $client = Client::create(['nombre' => $this->cliente_nombre]);
        }

        // Generate hash to prevent duplicates
        $hash = Sale::generateHash($this->fecha, $client->nombre, $this->comprobante, $this->monto);

        if (Sale::existsByHash($hash)) {
            $this->addError('comprobante', 'Esta venta ya existe en el sistema (duplicado detectado).');
            return;
        }

        // Create sale
        Sale::create([
            'fecha' => $this->fecha,
            'client_id' => $client->id,
            'cliente_nombre' => $client->nombre,
            'monto' => $this->monto,
            'moneda' => $this->moneda,
            'comprobante' => $this->comprobante,
            'hash' => $hash,
        ]);

        session()->flash('success', '¡Venta creada exitosamente!');
        
        // Reset form
        $this->reset(['cliente_nombre', 'fecha', 'monto', 'comprobante', 'selectedClientId']);
        $this->moneda = 'USD';
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Crear Venta Manual') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                @if (session()->has('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form wire:submit="save" class="space-y-6">
                    
                    <!-- Cliente -->
                    <div>
                        <label for="cliente_nombre" class="block text-sm font-medium text-gray-700">Cliente *</label>
                        <div class="relative mt-1">
                            <input 
                                wire:model.live.debounce.300ms="cliente_nombre" 
                                type="text" 
                                id="cliente_nombre"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                placeholder="Nombre del cliente"
                            >
                            
                            @if($showClientSearch && !empty($searchResults))
                                <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                    @foreach($searchResults as $result)
                                        <div 
                                            wire:click="selectClient({{ $result['id'] }}, '{{ $result['nombre'] }}')"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-orange-50"
                                        >
                                            <span class="block truncate">{{ $result['nombre'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @error('cliente_nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fecha -->
                    <div>
                        <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha *</label>
                        <input 
                            wire:model="fecha" 
                            type="date" 
                            id="fecha"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                        >
                        @error('fecha') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Monto -->
                    <div>
                        <label for="monto" class="block text-sm font-medium text-gray-700">Monto *</label>
                        <input 
                            wire:model="monto" 
                            type="number" 
                            step="0.01"
                            id="monto"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                            placeholder="0.00"
                        >
                        @error('monto') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Moneda -->
                    <div>
                        <label for="moneda" class="block text-sm font-medium text-gray-700">Moneda *</label>
                        <select 
                            wire:model="moneda" 
                            id="moneda"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                        >
                            <option value="USD">USD</option>
                            <option value="ARS">ARS</option>
                            <option value="EUR">EUR</option>
                        </select>
                        @error('moneda') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Comprobante -->
                    <div>
                        <label for="comprobante" class="block text-sm font-medium text-gray-700">Nº Comprobante *</label>
                        <input 
                            wire:model="comprobante" 
                            type="text" 
                            id="comprobante"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                            placeholder="Ej: FAC-001-00001234"
                        >
                        @error('comprobante') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('sales.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            Cancelar
                        </a>
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none"
                        >
                            Guardar Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
