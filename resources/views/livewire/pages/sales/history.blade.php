<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Sale;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $dateFilter = '';
    public $clientCodeFilter = '';
    public $articleCodeFilter = '';
    public $remitoFilter = '';
    public $perPage = 50;
    public $expanded = false;

    public function updated($property)
    {
        if (in_array($property, ['search', 'dateFilter', 'clientCodeFilter', 'articleCodeFilter', 'remitoFilter'])) {
            $this->resetPage();
        }
    }

    public function with()
    {
        $query = Sale::with('client')
            ->latest('fecha');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('comprobante', 'like', '%' . $this->search . '%')
                  ->orWhere('cliente_nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('cod_cli', 'like', '%' . $this->search . '%')
                  ->orWhere('n_remito', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->dateFilter) {
            $query->whereDate('fecha', $this->dateFilter);
        }

        if ($this->clientCodeFilter) {
            $query->where('cod_cli', 'like', '%' . $this->clientCodeFilter . '%');
        }
        
        if ($this->articleCodeFilter) {
            $query->where('cod_articu', 'like', '%' . $this->articleCodeFilter . '%');
        }

        if ($this->remitoFilter) {
            $query->where('n_remito', 'like', '%' . $this->remitoFilter . '%');
        }

        return [
            'sales' => $query->paginate($this->perPage),
        ];
    }
    
    public function toggleExpanded()
    {
        $this->expanded = !$this->expanded;
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Historial de Ventas</h1>
                        <p class="text-orange-100 text-sm">Registro completo de operaciones comerciales</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <a href="{{ route(auth()->user()->hasRole('Manager') ? 'admin.sales.import' : (auth()->user()->hasRole('Vendedor') ? 'sales.import' : 'admin.sales.import')) }}"
                           class="inline-flex items-center px-4 py-2 bg-white text-orange-700 rounded-lg font-bold shadow-md hover:bg-orange-50 transition-colors wire:navigate">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Importar Ventas
                        </a>
                        
                         <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.sales.create' : (auth()->user()->hasRole('Vendedor') ? 'sales.create' : 'admin.sales.create')) }}"
                           class="inline-flex items-center px-4 py-2 bg-orange-900 bg-opacity-30 text-white border border-orange-400 border-opacity-30 rounded-lg font-bold shadow-sm hover:bg-opacity-50 transition-colors wire:navigate">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nueva Venta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto sm:px-6 lg:px-8 transition-all duration-300" :class="$wire.expanded ? 'max-w-full' : 'max-w-7xl'">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Controls: Search & Filters -->
                    <div class="mb-6 space-y-4">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="w-full md:w-1/3">
                                <div class="relative rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="search" 
                                        class="block w-full rounded-md border-gray-300 pl-10 focus:border-orange-500 focus:ring-orange-500 sm:text-sm" 
                                        placeholder="Buscar por comprobante, cliente...">
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <span class="text-sm text-gray-500">Mostrando {{ $sales->count() }} operaciones</span>
                                <button wire:click="toggleExpanded" 
                                        class="inline-flex items-center px-3 py-1 bg-orange-100 border border-orange-200 rounded-md font-semibold text-xs text-orange-800 uppercase tracking-widest hover:bg-orange-200 active:bg-orange-300 focus:outline-none transition ease-in-out duration-150"
                                        title="Alternar vista en pantalla scompleta">
                                    @if(!$expanded)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                            Expandir
                                        </span>
                                    @else
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Contraer
                                        </span>
                                    @endif
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                            <!-- Fecha -->
                            <div>
                                <label for="dateFilter" class="block text-xs font-medium text-gray-500 uppercase mb-1">Fecha Registro</label>
                                <input type="date" wire:model.live="dateFilter" id="dateFilter" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-xs">
                            </div>
                            
                            <!-- Cod Cliente -->
                            <div>
                                <label for="clientCodeFilter" class="block text-xs font-medium text-gray-500 uppercase mb-1">Cód. Cliente</label>
                                <input type="text" wire:model.live.debounce.300ms="clientCodeFilter" id="clientCodeFilter" placeholder="Ej: CLI-001" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-xs">
                            </div>

                            <!-- Cod Articulo -->
                            <div>
                                <label for="articleCodeFilter" class="block text-xs font-medium text-gray-500 uppercase mb-1">Cód. Artículo</label>
                                <input type="text" wire:model.live.debounce.300ms="articleCodeFilter" id="articleCodeFilter" placeholder="Ej: ART-123" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-xs">
                            </div>

                            <!-- N Remito -->
                            <div>
                                <label for="remitoFilter" class="block text-xs font-medium text-gray-500 uppercase mb-1">N° Remito</label>
                                <input type="text" wire:model.live.debounce.300ms="remitoFilter" id="remitoFilter" placeholder="Ej: R-0001-00000001" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border rounded-lg shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Comprobante</th>
                                    <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Moneda</th>
                                    
                                    <!-- Columnas Secundarias -->
                                    @if($expanded)
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cód. Cliente</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">N° Remito</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Tipo Comp.</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cond. Vta</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">% Desc</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cotiz</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cód. Transp</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Transporte</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cód. Artículo</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Depósito</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">UM</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Tot. S/Imp</th>
                                    @endif
                                    
                                    <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    
                                    <!-- Columnas Secundarias Finales -->
                                    @if($expanded)
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">N° Comp Rem</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cant. Rem</th>
                                        <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha Rem</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($sales as $sale)
                                <tr class="hover:bg-orange-50/50 transition-colors">
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $sale->fecha->format('d/m/Y') }}</td>
                                    <td class="px-2 py-2 font-medium text-gray-900">{{ $sale->cliente_nombre }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap text-gray-500">{{ $sale->comprobante }}</td>
                                    <td class="px-2 py-2 font-mono font-bold text-gray-900 whitespace-nowrap">{{ number_format($sale->monto, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-gray-500">{{ $sale->moneda }}</td>
                                    
                                    <!-- Datos Secundarios -->
                                    @if($expanded)
                                        <td class="px-2 py-2">{{ $sale->cod_cli }}</td>
                                        <td class="px-2 py-2">{{ $sale->n_remito }}</td>
                                        <td class="px-2 py-2">{{ $sale->t_comp }}</td>
                                        <td class="px-2 py-2">{{ $sale->cond_vta }}</td>
                                        <td class="px-2 py-2">{{ $sale->porc_desc }}</td>
                                        <td class="px-2 py-2">{{ $sale->cotiz }}</td>
                                        <td class="px-2 py-2">{{ $sale->cod_transp }}</td>
                                        <td class="px-2 py-2">{{ $sale->cod_transp }}</td> <!-- Nom transp not in previous table but listed in header? Previous table used nom_transp but header said transporte. Using cod_transp placehoder or nom_transp if model has it -->
                                        <td class="px-2 py-2">{{ $sale->cod_articu }}</td>
                                        <td class="px-2 py-2">{{ $sale->descripcio }}</td>
                                        <td class="px-2 py-2">{{ $sale->cod_dep }}</td>
                                        <td class="px-2 py-2">{{ $sale->um }}</td>
                                        <td class="px-2 py-2">{{ $sale->cantidad }}</td>
                                        <td class="px-2 py-2 font-mono">{{ number_format($sale->precio, 2, ',', '.') }}</td>
                                        <td class="px-2 py-2 font-mono">{{ number_format($sale->tot_s_imp, 2, ',', '.') }}</td>
                                    @endif
                                    
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.sales.edit' : (auth()->user()->hasRole('Admin') ? 'admin.sales.edit' : 'sales.edit'), $sale) }}" class="text-indigo-600 hover:text-indigo-900 font-medium" wire:navigate>Editar</a>
                                    </td>
                                    
                                    <!-- Datos Secundarios Finales -->
                                    @if($expanded)
                                        <td class="px-2 py-2">{{ $sale->n_comp_rem }}</td>
                                        <td class="px-2 py-2">{{ $sale->cant_rem }}</td>
                                        <td class="px-2 py-2 whitespace-nowrap">{{ $sale->fecha_rem ? $sale->fecha_rem->format('d/m/Y') : '-' }}</td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <!-- Colspan dinámico: 6 visibles + 18 ocultas = 24 total -->
                                    <td colspan="{{ $expanded ? 24 : 6 }}" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <div class="bg-orange-50 p-4 rounded-full mb-3">
                                                <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <p class="text-lg font-medium">No hay ventas registradas</p>
                                            <p class="text-sm mt-1">Importe datos desde el módulo de importación</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <!-- Pagination -->
                    <div class="mt-4">
                        {{ $sales->links() }} 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
