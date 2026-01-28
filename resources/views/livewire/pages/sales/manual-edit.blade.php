<?php

use Livewire\Volt\Component;
use App\Models\Sale;
use App\Models\Client;
use App\Services\ClientNormalizationService;

new class extends Component {
    public Sale $sale;

    // 1. Datos Principales (Obligatorios)
    public $cliente_nombre = '';
    public $fecha = '';
    public $monto = '';
    public $comprobante = '';
    public $moneda = 'USD';
    
    // 2. Datos Tango (Opcionales)
    public $cod_cli = '';
    public $t_comp = '';
    public $cond_vta = '';
    public $porc_desc = '';
    public $cotiz = '';
    public $tot_s_imp = '';
    public $cod_dep = '';

    // 3. Datos de Remito (Opcionales)
    public $n_remito = '';
    public $n_comp_rem = '';
    public $cant_rem = '';
    public $fecha_rem = '';

    // 4. Datos de Artículo (Opcionales)
    public $cod_articu = '';
    public $descripcio = '';
    public $um = '';
    public $cantidad = '';
    public $precio = '';

    // 5. Datos de Transporte (Opcionales)
    public $cod_transp = '';
    public $nom_transp = '';
    
    // UI Logic
    public $searchResults = [];
    public $showClientSearch = false;
    public $showMoreDetails = true; // Show details by default on edit

    public function mount(Sale $sale)
    {
        $this->sale = $sale;
        
        // Cargar datos principales
        $this->cliente_nombre = $sale->cliente_nombre;
        $this->fecha = $sale->fecha ? $sale->fecha->format('Y-m-d') : '';
        $this->monto = $sale->monto;
        $this->moneda = $sale->moneda;
        $this->comprobante = $sale->comprobante;
        
        // Cargar opcionales (manejar nulos)
        $this->cod_cli = $sale->cod_cli ?? '';
        $this->t_comp = $sale->t_comp ?? '';
        $this->cond_vta = $sale->cond_vta ?? '';
        $this->porc_desc = $sale->porc_desc ?? '';
        $this->cotiz = $sale->cotiz ?? '';
        $this->tot_s_imp = $sale->tot_s_imp ?? '';
        $this->cod_dep = $sale->cod_dep ?? '';
        
        $this->n_remito = $sale->n_remito ?? '';
        $this->n_comp_rem = $sale->n_comp_rem ?? '';
        $this->cant_rem = $sale->cant_rem ?? '';
        $this->fecha_rem = $sale->fecha_rem ? $sale->fecha_rem->format('Y-m-d') : '';
        
        $this->cod_articu = $sale->cod_articu ?? '';
        $this->descripcio = $sale->descripcio ?? '';
        $this->um = $sale->um ?? '';
        $this->cantidad = $sale->cantidad ?? '';
        $this->precio = $sale->precio ?? '';
        
        $this->cod_transp = $sale->cod_transp ?? '';
        $this->nom_transp = $sale->nom_transp ?? '';
    }

    public function rules()
    {
        return [
            // Obligatorios
            'cliente_nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'comprobante' => 'required|string|max:255',
            'moneda' => 'required|in:USD,ARS,EUR',
            
            // Opcionales - Tango
            'cod_cli' => 'nullable|string|max:50',
            't_comp' => 'nullable|string|max:10',
            'cond_vta' => 'nullable|string|max:100',
            'porc_desc' => 'nullable|numeric|min:0|max:100',
            'cotiz' => 'nullable|numeric|min:0',
            'tot_s_imp' => 'nullable|numeric',
            'cod_dep' => 'nullable|string|max:20',
            
            // Opcionales - Remito
            'n_remito' => 'nullable|string|max:50',
            'n_comp_rem' => 'nullable|string|max:50',
            'cant_rem' => 'nullable|numeric',
            'fecha_rem' => 'nullable|date',
            
            // Opcionales - Artículo
            'cod_articu' => 'nullable|string|max:50',
            'descripcio' => 'nullable|string|max:255',
            'um' => 'nullable|string|max:10',
            'cantidad' => 'nullable|numeric',
            'precio' => 'nullable|numeric',
            
            // Opcionales - Transporte
            'cod_transp' => 'nullable|string|max:50',
            'nom_transp' => 'nullable|string|max:255',
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

    public function selectClient($clientName)
    {
        $this->cliente_nombre = $clientName;
        $this->showClientSearch = false;
        $this->searchResults = [];
    }
    
    public function toggleDetails()
    {
        $this->showMoreDetails = !$this->showMoreDetails;
    }

    public function save()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('show-error-modal', message: 'Hay campos obligatorios incompletos o con errores. Revise el formulario.');
            throw $e;
        }

        // Resolve or create client
        $service = app(ClientNormalizationService::class);
        $client = $service->resolveClientByAlias($this->cliente_nombre);

        if (!$client) {
            $client = Client::create(['nombre' => $this->cliente_nombre]);
        }

        // Check if comprobante changed and if it already exists (Strict uniqueness)
        if ($this->comprobante !== $this->sale->comprobante && Sale::where('comprobante', $this->comprobante)->where('id', '!=', $this->sale->id)->exists()) {
            $this->addError('comprobante', 'Este número de comprobante ya está asignado a otra venta.');
             $this->dispatch('show-error-modal', message: 'El número de comprobante ya está asignado a otra venta.');
            return;
        }

        // Check if hash changes (if critical fields change)
        $newHash = Sale::generateHash($this->fecha, $client->nombre, $this->comprobante, $this->monto);
        
        // If hash changed, check if it already exists in ANOTHER record
        if ($newHash !== $this->sale->hash && Sale::where('hash', $newHash)->where('id', '!=', $this->sale->id)->exists()) {
            $this->addError('comprobante', 'Esta combinación de datos genera un conflicto con otra venta existente.');
            $this->dispatch('show-error-modal', message: 'Se detectó un conflicto con otra venta existente (datos duplicados).');
            return;
        }

        // Update sale
        $this->sale->update([
            // Principales
            'fecha' => $this->fecha,
            'client_id' => $client->id,
            'cliente_nombre' => $client->nombre,
            'monto' => $this->monto,
            'moneda' => $this->moneda,
            'comprobante' => $this->comprobante,
            'hash' => $newHash,
            
            // Detalles Tango
            'cod_cli' => $this->cod_cli,
            't_comp' => $this->t_comp,
            'cond_vta' => $this->cond_vta,
            'porc_desc' => $this->porc_desc ?: 0,
            'cotiz' => $this->cotiz ?: 1,
            'tot_s_imp' => $this->tot_s_imp ?: 0,
            'cod_dep' => $this->cod_dep,
            
            // Remito
            'n_remito' => $this->n_remito,
            'n_comp_rem' => $this->n_comp_rem,
            'cant_rem' => $this->cant_rem ?: 0,
            'fecha_rem' => $this->fecha_rem ?: null,
            
            // Artículo
            'cod_articu' => $this->cod_articu,
            'descripcio' => $this->descripcio,
            'um' => $this->um,
            'cantidad' => $this->cantidad ?: 0,
            'precio' => $this->precio ?: 0,
            
            // Transporte
            'cod_transp' => $this->cod_transp,
            'nom_transp' => $this->nom_transp,
        ]);

        $this->dispatch('sale-updated');
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Editar Venta <span class="text-orange-200 text-xl">#{{ $sale->id }}</span></h1>
                        <p class="text-orange-100 text-sm">Modificación de registro comercial</p>
                    </div>
                    <div class="hidden md:block">
                        <svg class="w-12 h-12 text-orange-300 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <!-- Success Modal -->
                <div x-data="{ showModal: false }" 
                     @sale-updated.window="showModal = true"
                     x-show="showModal"
                     style="display: none;"
                     class="fixed inset-0 z-50 overflow-y-auto"
                     aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    
                    <!-- Backdrop -->
                    <div x-show="showModal" 
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                         aria-hidden="true"></div>

                    <!-- Modal Panel -->
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div x-show="showModal"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
                            
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            ¡Cambio Realizado!
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                La venta ha sido actualizada exitosamente.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <a href="{{ route('app.sales.history') }}"
                                   class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Aceptar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Modal -->
                <div x-data="{ show: false, message: '' }" 
                     wire:ignore
                     @show-error-modal.window="show = true; message = $event.detail.message || 'Revise los campos del formulario para más información.'"
                     x-show="show"
                     style="display: none;"
                     class="fixed inset-0 z-50 overflow-y-auto"
                     aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    
                    <div x-show="show" 
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                         aria-hidden="true"></div>

                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div x-show="show"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                            
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            ¡Atención!
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500" x-text="message"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" 
                                        @click="show = false"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Entendido
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <form wire:submit="save" class="space-y-8">
                    
                    <!-- 1. CAMPOS PRINCIPALES (OBLIGATORIOS) -->
                    <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Cliente -->
                            <div class="col-span-1 md:col-span-2">
                                <label for="cliente_nombre" class="block text-sm font-medium text-gray-700">Cliente *</label>
                                <div class="relative mt-1">
                                    <input 
                                        wire:model.live.debounce.300ms="cliente_nombre" 
                                        type="text" 
                                        id="cliente_nombre"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                    
                                    @if($showClientSearch && !empty($searchResults))
                                        <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                            @foreach($searchResults as $result)
                                                <div 
                                                    wire:click="selectClient('{{ $result['nombre'] }}')"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50"
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('fecha') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Comprobante -->
                            <div>
                                <label for="comprobante" class="block text-sm font-medium text-gray-700">Nº Comprobante *</label>
                                <input 
                                    wire:model="comprobante" 
                                    type="text" 
                                    id="comprobante"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                @error('comprobante') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Monto -->
                            <div>
                                <label for="monto" class="block text-sm font-medium text-gray-700">Monto Total *</label>
                                <input 
                                    wire:model="monto" 
                                    type="number" 
                                    step="0.01"
                                    id="monto"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-bold"
                                >
                                @error('monto') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Moneda -->
                            <div>
                                <label for="moneda" class="block text-sm font-medium text-gray-700">Moneda *</label>
                                <select 
                                    wire:model="moneda" 
                                    id="moneda"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="USD">USD</option>
                                    <option value="ARS">ARS</option>
                                    <option value="EUR">EUR</option>
                                </select>
                                @error('moneda') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- SECCIONES AVANZADAS -->
                    <div class="space-y-8 mt-6">
                        
                        <!-- 2. DATOS CONTABLES / TANGO -->
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium leading-6 text-gray-700 mb-4 border-b pb-2">Datos Contables</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Cód. Cliente</label>
                                    <input wire:model="cod_cli" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Tipo Comprobante</label>
                                    <input wire:model="t_comp" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Condición Venta</label>
                                    <input wire:model="cond_vta" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Total S/Imp</label>
                                    <input wire:model="tot_s_imp" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">% Descuento</label>
                                    <input wire:model="porc_desc" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Cotización</label>
                                    <input wire:model="cotiz" type="number" step="0.0001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Depósito</label>
                                    <input wire:model="cod_dep" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- 3. DATOS DE ARTÍCULO -->
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium leading-6 text-gray-700 mb-4 border-b pb-2">Detalle Artículo</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                                <div class="col-span-1">
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Cód. Artículo</label>
                                    <input wire:model="cod_articu" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Descripción</label>
                                    <input wire:model="descripcio" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">UM</label>
                                    <input wire:model="um" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Cantidad</label>
                                    <input wire:model="cantidad" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- 4. DATOS DE REMITO Y TRANSPORTE -->
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium leading-6 text-gray-700 mb-4 border-b pb-2">Logística y Remitos</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Nº Remito</label>
                                    <input wire:model="n_remito" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Fecha Remito</label>
                                    <input wire:model="fecha_rem" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Transporte</label>
                                    <input wire:model="nom_transp" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <a href="{{ route('app.sales.history') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            Cancelar
                        </a>
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Actualizar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
