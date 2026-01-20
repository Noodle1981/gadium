<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Client;
use App\Models\ClientSatisfactionResponse;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    // Filters
    public $search = '';
    public $dateFrom;
    public $dateTo;
    public $clientId = '';
    
    // Details Modal
    public $selectedResponse = null;
    public $showModal = false;

    // Edit Modal
    public $editingResponse = null;
    public $showEditModal = false;
    public $editData = [
        'pregunta_1' => 5,
        'pregunta_2' => 5,
        'pregunta_3' => 5,
        'pregunta_4' => 5,
    ];
    
    public function mount()
    {
        // Default to show all history
        $this->dateFrom = null;
        $this->dateTo = null;
    }

    public function updated($prop)
    {
        if (in_array($prop, ['search', 'dateFrom', 'dateTo', 'clientId'])) {
            $this->resetPage();
        }
    }
    
    public function search()
    {
        $this->resetPage();
    }
    
    public function clearFilters()
    {
        $this->search = '';
        $this->clientId = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
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
            session()->flash('message', 'Registro eliminado.');
        }
    }

    public function showDetails($id)
    {
        $this->selectedResponse = ClientSatisfactionResponse::with('client')->find($id);
        $this->showModal = true;
    }

    public function closeDetails()
    {
        $this->showModal = false;
        $this->selectedResponse = null;
    }

    public function editResponse($id)
    {
        $this->editingResponse = ClientSatisfactionResponse::find($id);
        if ($this->editingResponse) {
            $this->editData = [
                'pregunta_1' => $this->editingResponse->pregunta_1,
                'pregunta_2' => $this->editingResponse->pregunta_2,
                'pregunta_3' => $this->editingResponse->pregunta_3,
                'pregunta_4' => $this->editingResponse->pregunta_4,
            ];
            $this->showEditModal = true;
        }
    }

    public function updateResponse()
    {
        if ($this->editingResponse) {
            $this->editingResponse->update($this->editData);
            $this->showEditModal = false;
            $this->editingResponse = null;
            session()->flash('message', 'Registro actualizado correctamente.');
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingResponse = null;
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
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1 tracking-tight">Satisfacción de Clientes</h1>
                        <p class="text-orange-100 text-sm">Registro histórico y gestión de encuestas de calidad</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <a href="{{ route('client-satisfaction.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-white text-orange-700 rounded-lg font-bold shadow-md hover:bg-orange-50 transition-colors wire:navigate">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nueva Encuesta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filters -->
            <div class="bg-white p-6 shadow-sm rounded-3xl border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Buscar</label>
                        <input type="text" wire:model.live="search" placeholder="Cliente o Proyecto..." class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Cliente</label>
                        <select wire:model.live="clientId" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Fecha Desde</label>
                        <input type="date" wire:model.live="dateFrom" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Fecha Hasta</label>
                        <input type="date" wire:model.live="dateTo" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-12 md:col-span-2 flex space-x-2">
                        <button wire:click="clearFilters" class="w-full inline-flex justify-center py-2 px-4 border border-gray-200 shadow-sm text-sm font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-all">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 rounded-3xl">
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 uppercase tracking-widest text-[10px]">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left font-bold text-gray-500">ID</th>
                                            <th scope="col" class="px-6 py-3 text-left font-bold text-gray-500">Fecha</th>
                                            <th scope="col" class="px-6 py-3 text-left font-bold text-gray-500">Cliente</th>
                                            <th scope="col" class="px-6 py-3 text-left font-bold text-gray-500">Proyecto</th>
                                            <th scope="col" class="px-4 py-3 text-center font-bold text-gray-500">Obra</th>
                                            <th scope="col" class="px-4 py-3 text-center font-bold text-gray-500">Técnico</th>
                                            <th scope="col" class="px-4 py-3 text-center font-bold text-gray-500">Necesid.</th>
                                            <th scope="col" class="px-4 py-3 text-center font-bold text-gray-500">Plazo</th>
                                            <th scope="col" class="px-6 py-3 text-center font-bold text-gray-500">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($responses as $record)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">{{ $record->id }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->fecha->format('d/m/Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold capitalize">{{ $record->cliente_nombre }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-xs">{{ $record->proyecto }}</td>

                                                <!-- Color Coded Cells -->
                                                @foreach(['pregunta_1', 'pregunta_2', 'pregunta_3', 'pregunta_4'] as $p)
                                                    @php
                                                        $val = $record->$p;
                                                        $color = $val >= 4 ? 'bg-green-100 text-green-700' : ($val == 3 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700');
                                                    @endphp
                                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-black {{ $color }}">
                                                            {{ $val }}
                                                        </span>
                                                    </td>
                                                @endforeach

                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                                    <button wire:click="showDetails({{ $record->id }})" title="Ver detalle" class="text-indigo-400 hover:text-indigo-600 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    </button>
                                                    <button wire:click="editResponse({{ $record->id }})" title="Editar" class="text-amber-400 hover:text-amber-600 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </button>
                                                    <button wire:click="delete({{ $record->id }})" wire:confirm="¿Seguro que desea eliminar este registro?" title="Eliminar" class="text-red-400 hover:text-red-600 transition-colors">
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
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-3xl">
                    {{ $responses->links() }}
                </div>
            </div>
            
        </div>
    </div>

    <!-- Modal Detalle -->
    @if($showModal && $selectedResponse)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeDetails"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <div class="bg-indigo-900 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Detalle de Encuesta</h3>
                    <button wire:click="closeDetails" class="text-white hover:text-gray-200"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <div class="px-8 py-6">
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Cliente</label>
                            <p class="text-sm font-bold text-gray-900 capitalize">{{ $selectedResponse->cliente_nombre }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Proyecto</label>
                            <p class="text-sm font-bold text-gray-900">{{ $selectedResponse->proyecto }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @php
                            $questions = [
                                'pregunta_1' => 'Satisfacción Obra/Producto',
                                'pregunta_2' => 'Desempeño Técnico',
                                'pregunta_3' => 'Respuestas a Necesidades',
                                'pregunta_4' => 'Plazo de Ejecución',
                            ];
                        @endphp
                        @foreach($questions as $field => $label)
                            <div class="bg-gray-50 p-4 rounded-2xl flex items-center justify-between border border-gray-100">
                                <span class="text-sm font-bold text-gray-600">{{ $label }}</span>
                                <div class="flex items-center space-x-2">
                                    <div class="flex text-amber-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-5 h-5 {{ $i <= $selectedResponse->$field ? 'fill-current' : 'text-gray-200 fill-none uppercase' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                        @endfor
                                    </div>
                                    <span class="ml-2 font-black text-lg text-gray-900">{{ $selectedResponse->$field }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="px-8 py-4 bg-gray-50 text-right">
                    <button wire:click="closeDetails" class="px-6 py-2 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-all">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Edición -->
    @if($showEditModal && $editingResponse)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeEditModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <div class="bg-amber-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold">Editar Calificaciones</h3>
                    <button wire:click="closeEditModal"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <div class="px-8 py-6">
                    <div class="mb-6 p-4 bg-amber-50 rounded-2xl border border-amber-100">
                        <p class="text-xs text-amber-800 font-bold uppercase tracking-widest mb-1">Record</p>
                        <p class="text-sm font-bold text-amber-900">{{ $editingResponse->cliente_nombre }} - {{ $editingResponse->proyecto }}</p>
                    </div>
                    <div class="space-y-6">
                        @php
                            $questions = [
                                'pregunta_1' => 'Satisfacción Obra/Producto',
                                'pregunta_2' => 'Desempeño Técnico',
                                'pregunta_3' => 'Respuestas a Necesidades',
                                'pregunta_4' => 'Plazo de Ejecución',
                            ];
                        @endphp
                        @foreach($questions as $field => $label)
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ $label }}</label>
                                <div class="flex justify-between items-center gap-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button wire:click="$set('editData.{{ $field }}', {{ $i }})" 
                                            class="flex-1 py-3 text-sm font-black rounded-xl border-2 transition-all 
                                            {{ $editData[$field] == $i 
                                                ? 'bg-amber-600 border-amber-600 text-white shadow-lg scale-105' 
                                                : 'bg-white border-gray-100 text-gray-400 hover:border-amber-200 hover:text-amber-500' }}">
                                            {{ $i }}
                                        </button>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="px-8 py-6 bg-gray-50 flex justify-end space-x-3">
                    <button wire:click="closeEditModal" class="px-6 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Cancelar</button>
                    <button wire:click="updateResponse" class="px-8 py-2 bg-amber-600 text-white text-sm font-bold rounded-xl hover:bg-amber-700 shadow-sm transition-all">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
