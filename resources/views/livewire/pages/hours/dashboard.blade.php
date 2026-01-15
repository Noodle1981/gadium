<?php

use Livewire\Volt\Component;
use App\Models\HourDetail;
use Carbon\Carbon;

new class extends Component
{
    public $totalHoursMonth = 0;
    public $totalHoursLastMonth = 0;
    public $topProjects = [];
    public $recentActivity = [];

    public function mount()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $lastMonthDate = Carbon::now()->subMonth();
        $lastMonth = $lastMonthDate->month;
        $lastMonthYear = $lastMonthDate->year;

        // Total hours current month
        $this->totalHoursMonth = HourDetail::where('mes', $currentMonth)
            ->where('ano', $currentYear)
            ->sum('hs');

        // Total hours last month
        $this->totalHoursLastMonth = HourDetail::where('mes', $lastMonth)
            ->where('ano', $lastMonthYear)
            ->sum('hs');

        // Top projects by hours (all time)
        $this->topProjects = HourDetail::selectRaw('proyecto, sum(hs) as total_hs')
            ->groupBy('proyecto')
            ->orderByDesc('total_hs')
            ->take(5)
            ->get();
            
        // Recent activity
        $this->recentActivity = HourDetail::latest()
            ->take(5)
            ->get();
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard de Horas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-xl overflow-hidden">
                <div class="px-8 py-10 text-white">
                    <h1 class="text-3xl font-bold mb-2">Gestión de Detalles de Horas</h1>
                    <p class="text-blue-100 text-lg opacity-90">Administra registros horarios, importaciones y reportes de productividad.</p>
                </div>
            </div>



            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Estadísticas -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Resumen Mensual</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-gray-500 text-sm">Este Mes</span>
                            <div class="text-2xl font-bold text-indigo-600">{{ number_format($totalHoursMonth, 2) }} hs</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <span class="text-gray-500 text-sm">Mes Anterior</span>
                            <div class="text-2xl font-bold text-gray-600">{{ number_format($totalHoursLastMonth, 2) }} hs</div>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 mt-6 mb-4">Top Proyectos</h3>
                    <div class="space-y-3">
                        @forelse($topProjects as $project)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-2 h-8 bg-indigo-500 rounded-l mr-3"></div>
                                    <span class="font-medium text-gray-700">{{ $project->proyecto }}</span>
                                </div>
                                <span class="font-bold text-gray-800">{{ number_format($project->total_hs, 2) }} hs</span>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-4">No hay datos disponibles</div>
                        @endforelse
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Actividad Reciente</h3>
                    <div class="space-y-4">
                        @forelse($recentActivity as $activity)
                            <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-medium text-sm text-gray-800">{{ $activity->personal }}</span>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $activity->fecha->format('d/m') }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mb-1">{{ $activity->proyecto }}</div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-400">{{ $activity->funcion }}</span>
                                    <span class="font-bold text-indigo-600">{{ number_format($activity->hs, 2) }} hs</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-4">No hay actividad reciente</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
