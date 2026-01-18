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
        $this->validate();

        // Resolve or create client
        $service = app(ClientNormalizationService::class);
        $client = $service->resolveClientByAlias($this->cliente_nombre);

        if (!$client) {
            $client = Client::create(['nombre' => $this->cliente_nombre]);
        }

        // Check if hash changes (if critical fields change)
        $newHash = Sale::generateHash($this->fecha, $client->nombre, $this->comprobante, $this->monto);
        
        // If hash changed, check if it already exists in ANOTHER record
        if ($newHash !== $this->sale->hash && Sale::where('hash', $newHash)->where('id', '!=', $this->sale->id)->exists()) {
            $this->addError('comprobante', 'Esta combinación de datos genera un conflicto con otra venta existente.');
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

        session()->flash('success', '¡Venta actualizada exitosamente!');
        
        // Redirect to sales history to show the updated sale
        return $this->redirect(route('sales.historial.ventas'), navigate: true);
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

                <form wire:submit="save" class="space-y-8">
                    
                    <!-- 1. CAMPOS PRINCIPALES (OBLIGATORIOS) -->
                    <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 border-b border-indigo-200 pb-2">Datos Principales</h3>
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
                    
                    <!-- Botón para mostrar/ocultar detalles -->
                    <div class="flex justify-center">
                        <button type="button" wire:click="toggleDetails" class="text-sm flex items-center text-gray-500 hover:text-indigo-600 focus:outline-none">
                            @if($showMoreDetails)
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                Ocultar Detalles Avanzados
                            @else
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                Mostrar Detalles Avanzados
                            @endif
                        </button>
                    </div>

                    <!-- SECCIONES AVANZADAS -->
                    <div x-show="$wire.showMoreDetails" x-transition class="space-y-8">
                        
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
                        <a href="{{ route('sales.historial.ventas') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
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
