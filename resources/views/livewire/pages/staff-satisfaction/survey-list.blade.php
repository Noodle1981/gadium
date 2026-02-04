<?php

use Livewire\Volt\Component;
use App\Models\StaffSatisfactionResponse;
use Carbon\Carbon;

new class extends Component {
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $expandedDates = [];
    
    public function mount()
    {
        // Default to last 30 days
        $this->fechaHasta = now()->format('Y-m-d');
        $this->fechaDesde = now()->subDays(30)->format('Y-m-d');
    }
    
    public function clearFilters()
    {
        $this->fechaDesde = now()->subDays(30)->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
    }
    
    public function toggleDate($date)
    {
        if (in_array($date, $this->expandedDates)) {
            $this->expandedDates = array_diff($this->expandedDates, [$date]);
        } else {
            $this->expandedDates[] = $date;
        }
    }
    
    public function getSurveysGroupedByDateProperty()
    {
        $query = StaffSatisfactionResponse::query();
        
        if ($this->fechaDesde) {
            $query->whereDate('fecha', '>=', $this->fechaDesde);
        }
        
        if ($this->fechaHasta) {
            $query->whereDate('fecha', '<=', $this->fechaHasta);
        }
        
        $responses = $query->orderBy('fecha', 'desc')->get();
        
        // Group by date
        return $responses->groupBy(function($item) {
            return $item->fecha->format('Y-m-d');
        });
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 via-orange-500 to-amber-500 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 bg-orange-900/10 rounded-full blur-3xl"></div>
                
                <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white text-xs font-medium backdrop-blur-md mb-2 border border-white/20">
                            <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                            Encuestas
                        </div>
                        <h1 class="text-2xl font-bold text-white mb-1">Encuestas de Satisfacción Personal</h1>
                        <p class="text-orange-100 text-sm">Historial de encuestas agrupadas por fecha</p>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('app.staff-satisfaction.create') }}" class="inline-flex items-center px-4 py-2 bg-white/20 border border-white/30 text-white font-bold rounded-lg hover:bg-white/30 transition-all backdrop-blur-sm shadow-sm text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Cargar Manualmente
                        </a>
                        <a href="{{ route('app.staff-satisfaction.import') }}" class="inline-flex items-center px-4 py-2 bg-white text-orange-600 font-bold rounded-lg hover:bg-orange-50 transition-all shadow-md active:scale-95 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Importación Automática
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filtros -->
            <div class="mb-6 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Fecha Desde -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 ml-1">Desde</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-orange-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <input type="date" wire:model.live="fechaDesde" class="pl-11 block w-full bg-gray-50 border-gray-200 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm transition-all py-2.5">
                            </div>
                        </div>

                        <!-- Fecha Hasta -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 ml-1">Hasta</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-orange-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <input type="date" wire:model.live="fechaHasta" class="pl-11 block w-full bg-gray-50 border-gray-200 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm transition-all py-2.5">
                            </div>
                        </div>

                        <!-- Botón Limpiar -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 ml-1 invisible md:visible">Acción</label>
                            <button wire:click="clearFilters" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-all">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Encuestas Agrupadas -->
            @if($this->surveysGroupedByDate->isEmpty())
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay encuestas</h3>
                    <p class="text-gray-500">No se encontraron encuestas en el rango de fechas seleccionado.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($this->surveysGroupedByDate as $date => $surveys)
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transition-all hover:shadow-xl">
                            <!-- Header de Fecha -->
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 cursor-pointer" wire:click="toggleDate('{{ $date }}')">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0 p-3 bg-orange-100 rounded-xl">
                                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
                                            <p class="text-sm text-gray-600">{{ $surveys->count() }} {{ $surveys->count() === 1 ? 'encuesta' : 'encuestas' }} registrada{{ $surveys->count() === 1 ? '' : 's' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            {{ $surveys->count() }} respuestas
                                        </span>
                                        <svg class="h-5 w-5 text-gray-400 transition-transform {{ in_array($date, $expandedDates) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalles Expandibles -->
                            @if(in_array($date, $expandedDates))
                                <div class="p-6 bg-white border-t border-gray-200">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operario</th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trato Jefe</th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trato Comp.</th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Clima</th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Comodidad</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($surveys as $survey)
                                                    <tr class="hover:bg-gray-50 transition-colors">
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $survey->personal }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                                            @if($survey->p1_bien)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Bien</span>
                                                            @elseif($survey->p1_normal)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Normal</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Mal</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                                            @if($survey->p2_bien)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Bien</span>
                                                            @elseif($survey->p2_normal)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Normal</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Mal</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                                            @if($survey->p3_bien)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Bien</span>
                                                            @elseif($survey->p3_normal)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Normal</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Mal</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                                            @if($survey->p4_bien)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Bien</span>
                                                            @elseif($survey->p4_normal)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Normal</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Mal</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
