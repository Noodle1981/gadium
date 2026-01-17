<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Client;
use App\Models\ClientSatisfactionResponse;
use App\Models\ClientSatisfactionAnalysis;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    // Filters
    public $search = '';
    public $dateFrom;
    public $dateTo;
    public $clientId = '';
    
    // KPI Data
    public $kpiTotal = 0;
    public $kpiP1 = 0;
    public $kpiP2 = 0;
    public $kpiP3 = 0;
    public $kpiP4 = 0;
    
    public function mount()
    {
        // Default to current month/year view? or all time?
        // User design shows dates but not explicitly set. Let's default empty (All time) or current year.
        // Let's set defaults for better UX
        // Default to show all history
        $this->dateFrom = null; 
        $this->dateTo = null;
        
        $this->calculateKPIs();
    }

    public function updated($prop)
    {
        if (in_array($prop, ['search', 'dateFrom', 'dateTo', 'clientId'])) {
            $this->resetPage();
            $this->calculateKPIs();
        }
    }
    
    public function search()
    {
        $this->resetPage();
        $this->calculateKPIs();
    }
    
    public function clearFilters()
    {
        $this->search = '';
        $this->clientId = '';
        $this->dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfYear()->format('Y-m-d');
        $this->resetPage();
        $this->calculateKPIs();
    }

    public function calculateKPIs()
    {
        $query = ClientSatisfactionResponse::query();
        $this->applyFilters($query);
        
        // Optimize: Calculate KPIs from the filtered query
        // This calculates "Promedio" (Average) of the ratings (1-5)
        // OR does it calculate the Percentage (Tabla 3)? 
        // The dashboard image shows "3.27", "3.2", "3.53". These look like averages (1-5 scale).
        // BUT the Task Analysis said "Table 3" percentages.
        // Let's stick to the visual design which usually implies Average Rating (1-5 point scale).
        // 3.27 on a 5 point scale is clear. 65% is also clear.
        // Given the icons and standard satisfaction metris, Average Rating is most common for cards.
        // Percentages are better for "Goal Achievement".
        // Let's go with Average Rating (1-5) as shown in the image (3.27).
        
        $this->kpiTotal = $query->count();
        $this->kpiP1 = $this->kpiTotal > 0 ? round($query->avg('pregunta_1'), 2) : 0;
        $this->kpiP2 = $this->kpiTotal > 0 ? round($query->avg('pregunta_2'), 2) : 0;
        $this->kpiP3 = $this->kpiTotal > 0 ? round($query->avg('pregunta_3'), 2) : 0;
        $this->kpiP4 = $this->kpiTotal > 0 ? round($query->avg('pregunta_4'), 2) : 0;
    }

    protected function applyFilters($query)
    {
        if ($this->clientId) {
            $query->where('client_id', $this->clientId);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('cliente_nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('proyecto', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('fecha', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('fecha', '<=', $this->dateTo);
        }
    }

    public function delete($id)
    {
        $response = ClientSatisfactionResponse::find($id);
        if ($response) {
            $response->delete();
            $this->calculateKPIs(); // Recalcular KPIs tras borrar
            // Trigger background recalc?
            // For now simple delete.
            session()->flash('message', 'Registro eliminado.');
        }
    }

    public function with()
    {
        $query = ClientSatisfactionResponse::query();
        $this->applyFilters($query);

        return [
            'responses' => $query->orderBy('fecha', 'desc')->paginate(10),
            'clients' => Client::orderBy('nombre')->get(),
        ];
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Ver Datos de Satisfacción') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Total -->
                <div class="bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <div class="px-4 py-3 flex items-center justify-between">
                         <div>
                            <dt class="text-xs font-medium text-gray-500 truncate uppercase tracking-wider">Total Registros</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $kpiTotal }}</dd>
                        </div>
                        <div class="p-2 bg-gray-100 rounded-full">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Satisfacción Obra -->
                <div class="bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <div class="px-4 py-3 flex items-center justify-between">
                         <div>
                            <dt class="text-xs font-medium text-gray-500 truncate uppercase tracking-wider">Prom. Satisf. Obra</dt>
                            <dd class="mt-1 text-3xl font-semibold text-orange-600">{{ $kpiP1 }}</dd>
                        </div>
                        <div class="p-2 bg-orange-100 rounded-full">
                            <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Calif Técnica -->
                <div class="bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <div class="px-4 py-3 flex items-center justify-between">
                         <div>
                            <dt class="text-xs font-medium text-gray-500 truncate uppercase tracking-wider">Prom. Calif. Técnica</dt>
                            <dd class="mt-1 text-3xl font-semibold text-orange-600">{{ $kpiP2 }}</dd>
                        </div>
                        <div class="p-2 bg-orange-100 rounded-full">
                            <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                    </div>
                </div>
                
                <!-- Resp Necesidades -->
                <div class="bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <div class="px-4 py-3 flex items-center justify-between">
                         <div>
                            <dt class="text-xs font-medium text-gray-500 truncate uppercase tracking-wider">Prom. Resp. Necesidades</dt>
                            <dd class="mt-1 text-3xl font-semibold text-orange-600">{{ $kpiP3 }}</dd>
                        </div>
                        <div class="p-2 bg-orange-100 rounded-full">
                            <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Calif Plazo -->
                <div class="bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <div class="px-4 py-3 flex items-center justify-between">
                         <div>
                            <dt class="text-xs font-medium text-gray-500 truncate uppercase tracking-wider">Prom. Calif. Plazo</dt>
                            <dd class="mt-1 text-3xl font-semibold text-orange-600">{{ $kpiP4 }}</dd>
                        </div>
                        <div class="p-2 bg-orange-100 rounded-full">
                            <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>
            
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 shadow rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-1">Buscar</label>
                        <input type="text" wire:model="search" placeholder="Cliente o Proyecto..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-1">Cliente</label>
                        <select wire:model="clientId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-1">Fecha Desde</label>
                        <input type="date" wire:model="dateFrom" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-1">Fecha Hasta</label>
                        <input type="date" wire:model="dateTo" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-12 md:col-span-2 flex space-x-2">
                        <button wire:click="search" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none">
                            Buscar
                        </button>
                        <button wire:click="clearFilters" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-indigo-900 text-white">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cliente</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Proyecto</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Satisf. Obra</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Calif. Técnica</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Resp. Necesidades</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Calif. Plazo</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($responses as $record)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $record->id }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->fecha->format('d/m/Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $record->cliente_nombre }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $record->proyecto }}</td>
                                                
                                                <!-- Color Coded Cells -->
                                                @foreach(['pregunta_1', 'pregunta_2', 'pregunta_3', 'pregunta_4'] as $p)
                                                    @php
                                                        $val = $record->$p;
                                                        $color = $val >= 4 ? 'bg-green-100 text-green-800' : ($val == 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                                    @endphp
                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold {{ $color }}">
                                                        {{ $val }}
                                                    </td>
                                                @endforeach

                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <button wire:click="delete({{ $record->id }})" wire:confirm="¿Seguro que desea eliminar este registro?" class="text-red-600 hover:text-red-900">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pagination -->
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
                    {{ $responses->links() }}
                </div>
            </div>


        </div>
    </div>
</div>
