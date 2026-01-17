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
            {{ __('Historial de Importación - Satisfacción Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
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
