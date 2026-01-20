<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Budget;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $perPage = 50;
    public $expanded = false;

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
        $query = Budget::with('client')
            ->latest('fecha');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('comprobante', 'like', '%' . $this->search . '%')
                  ->orWhere('cliente_nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('nombre_proyecto', 'like', '%' . $this->search . '%');
            });
        }

        return [
            'budgets' => $query->paginate($this->perPage),
        ];
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Historial de Presupuestos</h1>
                        <p class="text-orange-100 text-sm">Gestión completa de estimaciones y seguimiento</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.budget.import' : (auth()->user()->hasRole('Admin') ? 'admin.budget.import' : 'budget.import')) }}" 
                           class="inline-flex items-center px-4 py-2 bg-white text-orange-700 rounded-lg font-bold shadow-md hover:bg-orange-50 transition-colors wire:navigate">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Importar Presupuesto
                        </a>

                        <a href="{{ route('budget.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-orange-900 bg-opacity-30 text-white border border-orange-400 border-opacity-30 rounded-lg font-bold shadow-sm hover:bg-opacity-50 transition-colors wire:navigate">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Crear Presupuesto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Controls: Search & Count -->
                    <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
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
                             <span class="text-sm text-gray-500">Mostrando {{ $budgets->count() }} registros</span>
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

                    <div class="overflow-x-auto border rounded-xl shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Fecha</th>
                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Cliente</th>
                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Orden Pedido</th>
                                    <th class="px-3 py-3 text-right font-bold text-orange-800 uppercase tracking-wider">Monto</th>
                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Moneda</th>
                                    
                                    @if($expanded)
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Centro Costo</th>
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Fecha OC</th>
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">F. Est. Culmin.</th>
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">F. Real Culmin.</th>
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Días Proy.</th>
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Env. Facturar</th>
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">N° Factura</th>
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">% Fact.</th>
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Saldo</th>
                                        <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">H. Ponderadas</th>
                                    @endif

                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider hidden md:table-cell">Proyecto</th>
                                    <th class="px-3 py-3 text-center font-bold text-orange-800 uppercase tracking-wider">Estado</th>
                                    <th class="px-3 py-3 text-center font-bold text-orange-800 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($budgets as $budget)
                                <tr class="hover:bg-orange-50/50 transition-colors">
                                    <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-900">{{ $budget->fecha->format('d/m/Y') }}</td>
                                    <td class="px-3 py-3 font-medium">{{ $budget->cliente_nombre }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-500">{{ $budget->comprobante }}</td>
                                    <td class="px-3 py-3 text-right font-mono font-bold text-gray-900">{{ number_format($budget->monto, 2, ',', '.') }}</td>
                                    <td class="px-3 py-3 text-gray-500">{{ $budget->moneda }}</td>

                                    @if($expanded)
                                        <td class="px-3 py-3 text-gray-500">{{ $budget->centro_costo ?? '-' }}</td>
                                        <td class="px-3 py-3 text-gray-500 whitespace-nowrap">{{ $budget->fecha_oc ? $budget->fecha_oc->format('d/m/Y') : '-' }}</td>
                                        <td class="px-3 py-3 text-gray-500 whitespace-nowrap">{{ $budget->fecha_estimada_culminacion ? $budget->fecha_estimada_culminacion->format('d/m/Y') : '-' }}</td>
                                        <td class="px-3 py-3 text-gray-500 whitespace-nowrap">{{ $budget->fecha_culminacion_real ? $budget->fecha_culminacion_real->format('d/m/Y') : '-' }}</td>
                                        <td class="px-3 py-3 text-gray-500">{{ $budget->estado_proyecto_dias ?? '-' }}</td>
                                        <td class="px-3 py-3 text-gray-500">{{ $budget->enviado_facturar ?? '-' }}</td>
                                        <td class="px-3 py-3 text-gray-500">{{ $budget->nro_factura ?? '-' }}</td>
                                        <td class="px-3 py-3 text-gray-500">{{ $budget->porc_facturacion ?? '-' }}</td>
                                        <td class="px-3 py-3 text-gray-500 font-mono">{{ $budget->saldo ? number_format($budget->saldo, 2, ',', '.') : '-' }}</td>
                                        <td class="px-3 py-3 text-gray-500">{{ $budget->horas_ponderadas ?? '-' }}</td>
                                    @endif
                                    <td class="px-3 py-3 text-gray-500 hidden md:table-cell truncate max-w-xs" title="{{ $budget->nombre_proyecto }}">{{ $budget->nombre_proyecto ?? '-' }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="px-2 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full uppercase tracking-wide
                                            {{ $budget->estado === 'Finalizado' ? 'bg-green-100 text-green-800' : 
                                               ($budget->estado === 'Cancelado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $budget->estado ?? 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <a href="{{ route('budget.edit', $budget) }}" class="text-orange-600 hover:text-orange-900 font-bold hover:underline" wire:navigate>Editar</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $expanded ? 18 : 8 }}" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <div class="bg-orange-50 p-4 rounded-full mb-3">
                                                <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </div>
                                            <p class="text-lg font-medium text-gray-900">No hay presupuestos registrados</p>
                                            <p class="text-sm mt-1">Comienza importando un archivo o creando uno manualmente</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $budgets->links() }} 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
