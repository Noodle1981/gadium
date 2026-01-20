<?php

use Livewire\Volt\Component;
use App\Models\PurchaseDetail;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    public string $moneda = 'USD';
    public string $cc = '';
    public int $ano;
    public string $empresa = '';
    public string $descripcion = '';
    public float $materiales_presupuestados = 0;
    public float $materiales_comprados = 0;
    public float $porcentaje_facturacion = 0;

    // Autocomplete State
    public $searchResults = [];
    public $showCompanySearch = false;
    public $isNewCompany = false;
    public $selectedCompanyHash = null; // Fake ID for UI logic

    public function updatedEmpresa($value)
    {
        $this->isNewCompany = false;
        $this->selectedCompanyHash = null;

        if (strlen($value) >= 2) {
            // Fetch distinct companies that match the search
            // Using a simple LIKE for efficiency on large tables
            $companies = PurchaseDetail::select('empresa')
                ->distinct()
                ->where('empresa', 'like', '%' . $value . '%')
                ->limit(10)
                ->pluck('empresa');
            
            $this->searchResults = $companies->map(function($name) {
                return [
                    'id' => md5($name), // Generate a fake ID for consistent UI handling
                    'nombre' => $name,
                ];
            })->toArray();
            
            $this->showCompanySearch = !empty($this->searchResults);
        } else {
            $this->searchResults = [];
            $this->showCompanySearch = false;
        }
    }

    public function selectCompany($name)
    {
        $this->empresa = $name;
        $this->selectedCompanyHash = md5($name);
        $this->showCompanySearch = false;
        $this->searchResults = [];
        $this->isNewCompany = false;
    }

    public function markAsNewCompany()
    {
        $this->selectedCompanyHash = null;
        $this->isNewCompany = true;
        $this->showCompanySearch = false;
        $this->searchResults = [];
    }

    public function mount()
    {
        $this->ano = date('Y');
    }

    // Computed properties for calculated fields
    public function with()
    {
        $resto_valor = $this->materiales_presupuestados - $this->materiales_comprados;
        $resto_porcentaje = $this->materiales_presupuestados > 0 
            ? ($resto_valor / $this->materiales_presupuestados) * 100 
            : 0;

        return [
            'resto_valor' => $resto_valor,
            'resto_porcentaje' => $resto_porcentaje
        ];
    }

    public function save()
    {
        $this->validate([
            'moneda' => 'required|string',
            'cc' => 'required|string',
            'ano' => 'required|integer|min:2000|max:2099',
            'empresa' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'materiales_presupuestados' => 'required|numeric|min:0',
            'materiales_comprados' => 'required|numeric|min:0',
            'porcentaje_facturacion' => 'required|numeric|min:0|max:100',
        ]);

        // Calculate final values
        $resto_valor = $this->materiales_presupuestados - $this->materiales_comprados;
        $resto_porcentaje = $this->materiales_presupuestados > 0 
            ? ($resto_valor / $this->materiales_presupuestados) * 100 
            : 0;

        // Generate Hash
        $hash = PurchaseDetail::generateHash($this->cc, (string)$this->ano, $this->empresa, $this->descripcion);

        if (PurchaseDetail::existsByHash($hash)) {
            $this->addError('duplicate', 'Ya existe un registro con estos datos (CC, Año, Empresa, Descripción).');
            return;
        }

        PurchaseDetail::create([
            'moneda' => $this->moneda,
            'cc' => $this->cc,
            'ano' => $this->ano,
            'empresa' => $this->empresa,
            'descripcion' => $this->descripcion,
            'materiales_presupuestados' => $this->materiales_presupuestados,
            'materiales_comprados' => $this->materiales_comprados,
            'resto_valor' => $resto_valor,
            'resto_porcentaje' => $resto_porcentaje,
            'porcentaje_facturacion' => $this->porcentaje_facturacion,
            'hash' => $hash,
        ]);

        $redirectRoute = auth()->user()->hasRole('Manager') ? 'manager.historial.compras' : (auth()->user()->hasRole('Gestor de Compras') ? 'purchases.historial.importacion' : 'admin.historial.compras');
        return redirect()->route($redirectRoute)->with('status', 'Compra registrada correctamente.');
    }
}; ?>

<x-slot name="header">
    <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
        <div class="px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">Registrar Compra de Materiales</h1>
                    <p class="text-orange-100 text-sm">Ingrese los detalles de la compra manualmente</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-12 h-12 text-orange-300 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- CTA Importación Masiva -->
                <div class="mb-8 flex justify-end">
                    <a href="{{ route('purchases.import') }}" 
                       class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Importación Automática Excel
                    </a>
                </div>

                @if($errors->has('duplicate'))
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        {{ $errors->first('duplicate') }}
                    </div>
                @endif

                <form wire:submit="save" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Columna 1 -->
                        <div class="space-y-4">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Moneda</label>
                                <select wire:model="moneda" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                    <option value="USD">USD - Dólar Estadounidense</option>
                                    <option value="ARS">ARS - Peso Argentino</option>
                                    <option value="EUR">EUR - Euro</option>
                                </select>
                                @error('moneda') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Centro de Costo (CC)</label>
                                <input type="text" wire:model="cc" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                @error('cc') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Año</label>
                                <input type="number" wire:model="ano" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                @error('ano') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Empresa (Proveedor)</label>
                                <div class="relative mt-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.300ms="empresa" 
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1 {{ $selectedCompanyHash ? 'bg-green-50 border-green-300 text-green-800' : '' }}"
                                            placeholder="Buscar Empresa..."
                                        >
                                        
                                        <!-- Badge: Existente -->
                                        @if($selectedCompanyHash)
                                            <span class="absolute right-2 top-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded font-medium mt-1">
                                                ✓ Existente
                                            </span>
                                        @endif
                                        
                                        <!-- Badge: Nuevo -->
                                        @if($isNewCompany)
                                            <span class="absolute right-2 top-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded font-medium mt-1">
                                                ⊕ Nuevo
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($showCompanySearch)
                                        <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                            @if(!empty($searchResults))
                                                @foreach($searchResults as $result)
                                                    <div 
                                                        wire:click="selectCompany('{{ $result['nombre'] }}')"
                                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-orange-50 flex justify-between items-center"
                                                    >
                                                        <span class="block truncate font-medium">{{ $result['nombre'] }}</span>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="py-3 px-4 text-sm text-gray-500 text-center">
                                                    No se encontraron coincidencias
                                                </div>
                                            @endif
                                            
                                            <!-- Opción: Crear nuevo -->
                                            <div 
                                                wire:click="markAsNewCompany"
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 bg-blue-50 hover:bg-blue-100 border-t border-blue-200 text-blue-700 font-medium"
                                            >
                                                <span class="block">⊕ Crear nuevo: "{{ $empresa }}"</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @error('empresa') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Descripción</label>
                                <input type="text" wire:model="descripcion" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Columna 2: Financiera -->
                        <div class="space-y-4 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-gray-700 mb-2 border-b pb-2">Datos Financieros</h3>
                            
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Materiales Presupuestados</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" step="0.01" wire:model.live="materiales_presupuestados" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                @error('materiales_presupuestados') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Materiales Comprados</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" step="0.01" wire:model.live="materiales_comprados" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                @error('materiales_comprados') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium text-sm text-gray-500">Resto (Valor)</label>
                                    <div class="mt-1 p-2 bg-gray-200 rounded text-right font-mono {{ $resto_valor < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($resto_valor, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-500">Resto (%)</label>
                                    <div class="mt-1 p-2 bg-gray-200 rounded text-right font-mono {{ $resto_porcentaje < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($resto_porcentaje, 1) }}%
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">% de Facturación</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <input type="number" step="0.01" wire:model="porcentaje_facturacion" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-8">
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                                @error('porcentaje_facturacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-6 pt-6 border-t border-gray-200">
                        <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.historial.compras' : (auth()->user()->hasRole('Gestor de Compras') ? 'purchases.historial.importacion' : 'admin.historial.compras')) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Guardar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
