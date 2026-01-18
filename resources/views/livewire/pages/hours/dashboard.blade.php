<?php

use Livewire\Volt\Component;
use App\Models\HourDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $selectedYear;
    public $selectedMonth;

    public function mount()
    {
        // Default to the latest year with data, or current year if no data
        $latestYear = HourDetail::max('ano');
        $this->selectedYear = $latestYear ?: Carbon::now()->year;
        $this->selectedMonth = 'all'; 
    }

    public function with()
    {
        // Query base
        $query = HourDetail::query();

        // Filtro de Año
        $query->where('ano', $this->selectedYear);

        // Filtro de Mes
        if ($this->selectedMonth !== 'all') {
            $query->where('mes', $this->selectedMonth);
        }

        // KPIs
        $totalHoras = (clone $query)->sum('hs');
        $totalRegistros = (clone $query)->count();
        $promedioDiario = $totalRegistros > 0 ? $totalHoras / $totalRegistros : 0;
        
        $personalActivo = (clone $query)->distinct('personal')->count('personal');
        $proyectosActivos = (clone $query)->distinct('proyecto')->count('proyecto');

        // Top Personal (Ranking Top 10)
        $rankingPersonal = (clone $query)
            ->select('personal', DB::raw('SUM(hs) as total_hs'))
            ->groupBy('personal')
            ->orderByDesc('total_hs')
            ->limit(10)
            ->get();

        // Desglose por Proyecto
        $proyectos = (clone $query)
            ->select('proyecto', DB::raw('SUM(hs) as total_hs'))
            ->groupBy('proyecto')
            ->orderByDesc('total_hs')
            ->limit(10)
            ->get();

        // Años disponibles
        $añosDisponibles = HourDetail::select('ano')
            ->distinct()
            ->orderBy('ano', 'desc')
            ->pluck('ano')
            ->values();
            
        // Meses para el select
        $meses = [];
        for ($i = 1; $i <= 12; $i++) {
            $meses[$i] = Carbon::create(null, $i, 1)->locale('es')->monthName;
        }

        return [
            'totalHoras' => $totalHoras,
            'personalActivo' => $personalActivo,
            'proyectosActivos' => $proyectosActivos,
            'promedioDiario' => $promedioDiario,
            'rankingPersonal' => $rankingPersonal,
            'proyectos' => $proyectos,
            'añosDisponibles' => $añosDisponibles,
            'nombresMeses' => $meses,
        ];
    }
}; ?>

<div class="h-[calc(100vh-65px)] overflow-hidden bg-gray-50 flex flex-col p-6">
    <!-- Header y Filtros -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Panel de Horas</h1>
            <p class="text-gray-500 mt-1">Control de productividad y tiempos</p>
        </div>
        
        <div class="flex gap-4 bg-white p-2 rounded-xl shadow-sm border border-gray-100">
            <!-- Selector de Año -->
            <select wire:model.live="selectedYear" 
                    class="bg-gray-50 border-0 rounded-lg text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-orange-500 py-2 pl-3 pr-8 cursor-pointer hover:bg-gray-100 transition-colors">
                @foreach($añosDisponibles as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>

            <!-- Selector de Mes -->
            <select wire:model.live="selectedMonth" 
                    class="bg-gray-50 border-0 rounded-lg text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-orange-500 py-2 pl-3 pr-8 cursor-pointer hover:bg-gray-100 transition-colors">
                <option value="all">Todo el año</option>
                @foreach($nombresMeses as $num => $nombre)
                    <option value="{{ $num }}">{{ ucfirst($nombre) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Grid de KPIs Principales -->
    <div class="grid grid-cols-4 gap-6 mb-8">
        <!-- Total Horas (Orange) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Horas</p>
            <h3 class="text-4xl font-bold text-gray-900">{{ number_format($totalHoras, 1, ',', '.') }}</h3>
            <div class="mt-4 flex items-center text-sm text-orange-600 font-medium bg-orange-50 w-fit px-2 py-1 rounded-full">
                <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                Registradas
            </div>
        </div>

        <!-- Personal Activo (Blue) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Personal Activo</p>
            <h3 class="text-4xl font-bold text-gray-900">{{ $personalActivo }}</h3>
            <div class="mt-4 flex items-center text-sm text-blue-600 font-medium bg-blue-50 w-fit px-2 py-1 rounded-full">
                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                Colaboradores
            </div>
        </div>

        <!-- Proyectos Activos (Purple) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Proyectos Activos</p>
            <h3 class="text-4xl font-bold text-gray-900">{{ $proyectosActivos }}</h3>
            <div class="mt-4 flex items-center text-sm text-purple-600 font-medium bg-purple-50 w-fit px-2 py-1 rounded-full">
                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span>
                Obras/Servicios
            </div>
        </div>

        <!-- Promedio horas (Emerald) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Promedio por Registro</p>
            <h3 class="text-4xl font-bold text-gray-900">{{ number_format($promedioDiario, 1, ',', '.') }}</h3>
            <div class="mt-4 flex items-center text-sm text-emerald-600 font-medium bg-emerald-50 w-fit px-2 py-1 rounded-full">
                <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span>
                Horas / Entrada
            </div>
        </div>
    </div>

    <!-- Sección Inferior: Proyectos y Ranking Personal -->
    <div class="grid grid-cols-2 gap-6 h-full min-h-0">
        <!-- Tarjeta de Proyectos -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Horas por Proyecto
            </h3>
            <div class="space-y-4 overflow-y-auto pr-2 custom-scrollbar">
                @foreach($proyectos as $proy)
                    <div class="group p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors border border-transparent hover:border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-700">{{ $proy->proyecto }}</span>
                            <span class="font-bold text-gray-900">{{ number_format($proy->total_hs, 1) }} hs</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-purple-500 h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ $totalHoras > 0 ? ($proy->total_hs / $totalHoras) * 100 : 0 }}%"></div>
                        </div>
                        <div class="flex justify-end mt-1">
                            <span class="text-xs text-gray-500 font-medium">{{ $totalHoras > 0 ? number_format(($proy->total_hs / $totalHoras) * 100, 1) : 0 }}%</span>
                        </div>
                    </div>
                @endforeach
                
                @if($proyectos->isEmpty())
                    <div class="text-center py-10 text-gray-400">
                        No hay datos de proyectos para este periodo
                    </div>
                @endif
            </div>
        </div>

        <!-- Tarjeta de Ranking Personal -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                Ranking de Productividad (Top 10)
            </h3>
            <div class="space-y-0 overflow-y-auto custom-scrollbar">
                @foreach($rankingPersonal as $index => $persona)
                    <div class="flex items-center p-3 {{ $index < 3 ? 'bg-orange-50 mb-2 rounded-xl border border-orange-100' : 'border-b border-gray-50 hover:bg-gray-50' }}">
                        
                        <!-- Ranking Position -->
                        <div class="flex-shrink-0 w-8 flex justify-center">
                            @if($index == 0)
                                <span class="bg-yellow-100 text-yellow-700 font-bold w-6 h-6 rounded-full flex items-center justify-center text-xs">1</span>
                            @elseif($index == 1)
                                <span class="bg-gray-100 text-gray-700 font-bold w-6 h-6 rounded-full flex items-center justify-center text-xs">2</span>
                            @elseif($index == 2)
                                <span class="bg-orange-100 text-orange-700 font-bold w-6 h-6 rounded-full flex items-center justify-center text-xs">3</span>
                            @else
                                <span class="text-gray-400 font-bold text-xs">{{ $index + 1 }}</span>
                            @endif
                        </div>

                        <!-- Name -->
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $persona->personal }}</p>
                        </div>

                        <!-- Hours -->
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white text-gray-800 border {{ $index < 3 ? 'border-orange-200' : 'border-gray-200' }}">
                                {{ number_format($persona->total_hs, 1, ',', '.') }} hs
                            </span>
                        </div>
                    </div>
                @endforeach
                
                @if($rankingPersonal->isEmpty())
                    <div class="text-center py-10 text-gray-400">
                        No hay datos de personal para este periodo
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
