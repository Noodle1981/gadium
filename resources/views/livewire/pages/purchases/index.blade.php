<?php

use App\Models\PurchaseDetail;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $expanded = false;
    public $filterYear = '';

    public function toggleExpanded()
    {
        $this->expanded = !$this->expanded;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with()
    {
        return [
            'purchases' => PurchaseDetail::query()
                ->when($this->search, function ($query) {
                    $query->where('empresa', 'like', '%' . $this->search . '%')
                        ->orWhere('cc', 'like', '%' . $this->search . '%')
                        ->orWhere('descripcion', 'like', '%' . $this->search . '%');
                })
                ->when($this->filterYear, function ($query) {
                    $query->where('ano', $this->filterYear);
                })
                ->orderBy('ano', 'desc')
                ->orderBy('empresa', 'asc')
                ->paginate(50),
            'years' => PurchaseDetail::select('ano')->distinct()->orderBy('ano', 'desc')->pluck('ano'),
        ];
    }
}; ?>

<div class="py-12">
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="mb-4 md:mb-0">
                        <h1 class="text-3xl font-bold text-white mb-2 tracking-tight">Historial de Compras</h1>
                        <p class="text-orange-100 text-lg font-medium opacity-90">Registro completo de compras de materiales</p>
                    </div>
                    <div class="flex space-x-3">
                         <!-- Boton Manual -->
                         <a href="{{ route('app.purchases.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-opacity-30 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-orange-600 transition ease-in-out duration-150 backdrop-blur-sm shadow-sm group">
                            <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Cargar Manualmente
                        </a>

                        <!-- Boton Importar -->
                        <a href="{{ route('app.purchases.import') }}"
                           class="inline-flex items-center px-4 py-2 bg-white text-orange-600 rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-orange-600 transition ease-in-out duration-150 shadow-lg transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Importacion Automatica
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto sm:px-6 lg:px-8 transition-all duration-300" :class="$wire.expanded ? 'max-w-full' : 'max-w-7xl'">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 text-gray-900">

                <div class="mb-4 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <div class="w-full sm:w-64">
                            <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full rounded-md border-gray-300 pl-10 focus:border-orange-500 focus:ring-orange-500 sm:text-sm" placeholder="Buscar por empresa, CC, descripcion...">
                            </div>
                        </div>
                        <div class="w-32">
                            <select wire:model.live="filterYear" class="block w-full rounded-md border-gray-300 focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                <option value="">Todos los anios</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <button wire:click="toggleExpanded"
                                class="inline-flex items-center px-3 py-1 bg-orange-100 border border-orange-200 rounded-md font-semibold text-xs text-orange-800 uppercase tracking-widest hover:bg-orange-200 active:bg-orange-300 focus:outline-none transition ease-in-out duration-150"
                                title="Alternar vista en pantalla completa">
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

                        @if($purchases->total() > 0)
                            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                                Mostrando {{ $purchases->firstItem() }} - {{ $purchases->lastItem() }} de {{ $purchases->total() }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto border rounded-xl shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Anio</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">CC</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap min-w-[200px]">Empresa</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap min-w-[200px]">Descripcion</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Mat. Presup.</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Mat. Comprado</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Resto ($)</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Resto (%)</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">% Fact.</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Moneda</th>
                                <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap sticky right-0 bg-gray-50 z-10 shadow-l">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($purchases as $purchase)
                            <tr class="hover:bg-orange-50 transition-colors group">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">{{ $purchase->ano }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        {{ $purchase->cc }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ $purchase->empresa }}</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="{{ $purchase->descripcion }}">{{ $purchase->descripcion }}</td>
                                <td class="px-4 py-3 text-right font-mono text-gray-900">{{ number_format($purchase->materiales_presupuestados, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono text-gray-900">{{ number_format($purchase->materiales_comprados, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono {{ $purchase->resto_valor < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($purchase->resto_valor, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono {{ $purchase->resto_porcentaje < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($purchase->resto_porcentaje, 1) }}%
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-orange-600 font-bold">{{ number_format($purchase->porcentaje_facturacion, 1) }}%</td>
                                <td class="px-4 py-3 text-gray-500">{{ $purchase->moneda }}</td>
                                <td class="px-4 py-3 whitespace-nowrap sticky right-0 bg-white group-hover:bg-orange-50 z-10 shadow-l border-l border-gray-100">
                                    <a href="{{ route('app.purchases.edit', $purchase) }}" class="text-orange-600 hover:text-orange-900 font-bold transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Editar
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <div class="bg-gray-100 p-4 rounded-full mb-3">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">No hay registros de compras</h3>
                                        <p class="text-sm text-gray-500 max-w-sm mx-auto mt-1">Intente ajustar los filtros o importe un nuevo archivo.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
