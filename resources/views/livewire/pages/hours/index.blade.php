<?php

use App\Models\HourDetail;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function with()
    {
        return [
            'hours' => HourDetail::query()
                ->when($this->search, function ($query) {
                    $query->where('personal', 'like', '%' . $this->search . '%')
                        ->orWhere('funcion', 'like', '%' . $this->search . '%')
                        ->orWhere('proyecto', 'like', '%' . $this->search . '%')
                        ->orWhere('tarea', 'like', '%' . $this->search . '%')
                        ->orWhere('ot', 'like', '%' . $this->search . '%');
                })
                ->orderBy('fecha', 'desc')
                ->paginate(50),
        ];
    }
}; ?>

<div class="py-12">
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="mb-4 md:mb-0">
                        <h1 class="text-3xl font-bold text-white mb-2 tracking-tight">Historial de Horas</h1>
                        <p class="text-orange-100 text-lg font-medium opacity-90">Registro completo y auditoría de tiempos</p>
                    </div>
                    <div class="flex space-x-3">
                         @php
                            $createRoute = auth()->user()->hasRole('Manager') ? 'manager.hours.create' : (auth()->user()->hasRole('Gestor de Horas') ? 'hours.create' : 'admin.hours.create');
                            $importRoute = auth()->user()->hasRole('Manager') ? 'manager.hours.import' : (auth()->user()->hasRole('Gestor de Horas') ? 'hours.import' : 'admin.hours.import');
                        @endphp

                         <!-- Botón Manual -->
                         <a href="{{ route($createRoute) }}" 
                           class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-opacity-30 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-orange-600 transition ease-in-out duration-150 backdrop-blur-sm shadow-sm group">
                            <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Cargar Manualmente
                        </a>
                        
                        <!-- Botón Importar -->
                        <a href="{{ route($importRoute) }}" 
                           class="inline-flex items-center px-4 py-2 bg-white text-orange-600 rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-orange-600 transition ease-in-out duration-150 shadow-lg transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Importación Automática
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                
                <!-- Controls Bar -->
                <div class="mb-4 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                    <div class="w-full sm:w-1/3">
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="block w-full rounded-md border-gray-300 pl-10 focus:border-orange-500 focus:ring-orange-500 sm:text-sm" placeholder="Buscar por personal, función, proyecto...">
                        </div>
                    </div>
                    <div>
                         @if($hours->total() > 0)
                            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                                Mostrando {{ $hours->firstItem() }} - {{ $hours->lastItem() }} de {{ $hours->total() }} registros
                            </span>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto border rounded-xl shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Día</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Personal</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Función</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Proyecto</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Detalle</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Hs</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Pond.</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Hs Pond.</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $editRouteName = auth()->user()->hasRole('Manager') ? 'manager.hours.edit' : (auth()->user()->hasRole('Gestor de Horas') ? 'hours.edit' : 'admin.hours.edit');
                            @endphp

                            @forelse($hours as $hour)
                            <tr class="hover:bg-orange-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">{{ $hour->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $hour->dia }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ $hour->personal }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $hour->funcion }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                        {{ $hour->proyecto }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 max-w-xs truncate" title="{{ $hour->tarea }} / {{ $hour->ot }}">{{ $hour->tarea }} {{ $hour->ot ? '('.$hour->ot.')' : '' }}</td>
                                <td class="px-4 py-3 font-bold text-gray-900">{{ number_format($hour->hs, 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format($hour->ponderador, 4) }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ number_format($hour->horas_ponderadas, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route($editRouteName, $hour) }}" class="text-orange-600 hover:text-orange-900 font-bold transition-colors flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Editar
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <div class="bg-gray-100 p-4 rounded-full mb-3">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">No hay registros de horas</h3>
                                        <p class="text-sm text-gray-500 max-w-sm mx-auto mt-1">Intente ajustar los filtros o importe un nuevo archivo.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $hours->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
