<?php

use Livewire\Volt\Component;
use App\Models\Budget;
use App\Models\Client;
use App\Services\ClientNormalizationService;

new class extends Component {
    // 1. Datos Principales (Obligatorios)
    public $cliente_nombre = '';
    public $fecha = '';
    public $monto = '';
    public $comprobante = ''; // Orden de Pedido
    public $moneda = 'USD'; // Fixed to USD as per requirement
    
    // 2. Detalles del Proyecto
    public $nombre_proyecto = '';
    public $centro_costo = '';
    public $fecha_oc = '';
    public $fecha_estimada_culminacion = '';
    public $fecha_culminacion_real = '';
    public $estado_proyecto_dias = '';
    public $estado = '';

    // 3. Facturación y Saldos
    public $enviado_facturar = ''; // Yes/No or Status
    public $nro_factura = '';
    public $porc_facturacion = '';
    public $saldo = '';
    public $horas_ponderadas = '';

    // UI Logic
    public $searchResults = [];
    public $selectedClientId = null;
    public $showClientSearch = false;
    
    // Sections state
    public $showMoreDetails = false;

    public function mount()
    {
        $this->fecha = date('Y-m-d');
    }

    public function rules()
    {
        return [
            // Obligatorios
            'cliente_nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'comprobante' => 'required|string|max:255',
            
            // Detalles Proyecto
            'nombre_proyecto' => 'nullable|string|max:255',
            'centro_costo' => 'nullable|string|max:50',
            'fecha_oc' => 'nullable|date',
            'fecha_estimada_culminacion' => 'nullable|date',
            'fecha_culminacion_real' => 'nullable|date',
            'estado_proyecto_dias' => 'nullable|integer',
            'estado' => 'nullable|string|max:50',
            
            // Facturación
            'enviado_facturar' => 'nullable|string|max:50',
            'nro_factura' => 'nullable|string|max:50',
            'porc_facturacion' => 'nullable|string|max:20',
            'saldo' => 'nullable|numeric',
            'horas_ponderadas' => 'nullable|numeric',
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

        // Generate hash to prevent duplicates
        $hash = Budget::generateHash($this->fecha, $client->nombre, $this->comprobante, $this->monto);

        if (Budget::existsByHash($hash)) {
            $this->addError('comprobante', 'Este presupuesto ya existe en el sistema (duplicado detectado).');
            return;
        }

        // Create budget
        Budget::create([
            // Principales
            'fecha' => $this->fecha,
            'client_id' => $client->id,
            'cliente_nombre' => $client->nombre,
            'monto' => $this->monto,
            'moneda' => 'USD', // Always USD for budgets
            'comprobante' => $this->comprobante,
            'hash' => $hash,
            
            // Detalles Proyecto
            'nombre_proyecto' => $this->nombre_proyecto,
            'centro_costo' => $this->centro_costo,
            'fecha_oc' => $this->fecha_oc ?: null,
            'fecha_estimada_culminacion' => $this->fecha_estimada_culminacion ?: null,
            'fecha_culminacion_real' => $this->fecha_culminacion_real ?: null,
            'estado_proyecto_dias' => $this->estado_proyecto_dias ?: null,
            'estado' => $this->estado,
            
            // Facturación
            'enviado_facturar' => $this->enviado_facturar,
            'nro_factura' => $this->nro_factura,
            'porc_facturacion' => $this->porc_facturacion,
            'saldo' => $this->saldo ?: 0,
            'horas_ponderadas' => $this->horas_ponderadas ?: null,
        ]);

        session()->flash('success', '¡Presupuesto creado exitosamente!');
        
        // Reset form
        $this->reset([
            'cliente_nombre', 'fecha', 'monto', 'comprobante', 'selectedClientId',
            'nombre_proyecto', 'centro_costo', 'fecha_oc', 'fecha_estimada_culminacion',
            'fecha_culminacion_real', 'estado_proyecto_dias', 'estado',
            'enviado_facturar', 'nro_factura', 'porc_facturacion', 'saldo', 'horas_ponderadas'
        ]);
        $this->fecha = date('Y-m-d');
        $this->showMoreDetails = false;
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Importación Manual de Presupuestos</h1>
                        <p class="text-orange-100 text-sm">Registro individual de estimaciones</p>
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
                    <div class="mb-4 bg-orange-50 border-l-4 border-orange-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-orange-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- CTA Importación Masiva -->
                <div class="mb-8 flex justify-end">
                    <a href="{{ route('budget.import') }}" 
                       class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Importación Automática Excel
                    </a>
                </div>

                <form wire:submit="save" class="space-y-8">
                    
                    <!-- 1. CAMPOS PRINCIPALES (OBLIGATORIOS) -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 border-b pb-2">Datos Principales (Obligatorios)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Cliente -->
                            <div class="col-span-1 md:col-span-2">
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

                            <!-- Orden de Pedido / Comprobante -->
                            <div>
                                <label for="comprobante" class="block text-sm font-medium text-gray-700">Orden de Pedido *</label>
                                <input 
                                    wire:model="comprobante" 
                                    type="text" 
                                    id="comprobante"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                    placeholder="Ej: OP-2023-001"
                                >
                                @error('comprobante') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Monto -->
                            <div>
                                <label for="monto" class="block text-sm font-medium text-gray-700">Monto Total (USD) *</label>
                                <input 
                                    wire:model="monto" 
                                    type="number" 
                                    step="0.01"
                                    id="monto"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm font-bold"
                                    placeholder="0.00"
                                >
                                @error('monto') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Moneda (Fija) -->
                            <div class="hidden">
                                <input type="hidden" wire:model="moneda" value="USD">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón para mostrar/ocultar detalles -->
                    <div class="flex justify-center">
                        <button type="button" wire:click="toggleDetails" class="text-sm flex items-center text-gray-500 hover:text-orange-600 focus:outline-none">
                            @if($showMoreDetails)
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                Ocultar Detalles Avanzados
                            @else
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                Mostrar Detalles (Proyecto, Fechas, Facturación)
                            @endif
                        </button>
                    </div>

                    <!-- SECCIONES AVANZADAS -->
                    <div x-show="$wire.showMoreDetails" x-transition class="space-y-8">
                        
                        <!-- 2. DETALLES DEL PROYECTO -->
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium leading-6 text-gray-700 mb-4 border-b pb-2">Información del Proyecto</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Nombre Proyecto</label>
                                    <input wire:model="nombre_proyecto" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Centro de Costo</label>
                                    <input wire:model="centro_costo" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Fecha OC</label>
                                    <input wire:model="fecha_oc" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Fecha Est. Culminación</label>
                                    <input wire:model="fecha_estimada_culminacion" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Fecha Culm. Real</label>
                                    <input wire:model="fecha_culminacion_real" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Estado Proyecto (Días)</label>
                                    <input wire:model="estado_proyecto_dias" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Estado</label>
                                    <select wire:model="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        <option value="En proceso">En proceso</option>
                                        <option value="Finalizado">Finalizado</option>
                                        <option value="Cancelado">Cancelado</option>
                                        <option value="Pendiente">Pendiente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 3. FACTURACIÓN Y SALDOS -->
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium leading-6 text-gray-700 mb-4 border-b pb-2">Facturación y Saldos</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Enviado a Facturar</label>
                                    <input wire:model="enviado_facturar" type="text" placeholder="Si/No" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Nº Factura</label>
                                    <input wire:model="nro_factura" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">% Facturación</label>
                                    <input wire:model="porc_facturacion" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Saldo ($)</label>
                                    <input wire:model="saldo" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Horas Ponderadas</label>
                                    <input wire:model="horas_ponderadas" type="number" step="0.1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <a href="{{ route('budget.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            Cancelar
                        </a>
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Guardar Presupuesto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
