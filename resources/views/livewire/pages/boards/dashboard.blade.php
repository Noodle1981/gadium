<?php

use Livewire\Volt\Component;
use App\Models\BoardDetail;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public function with(): array
    {
        $currentYear = date('Y');
        
        // Totales del año actual
        $totals = BoardDetail::where('ano', $currentYear)
            ->selectRaw('
                COUNT(*) as total_records,
                SUM(gabinetes) as total_gabinetes,
                SUM(columnas) as total_columnas
            ')
            ->first();
            
        // Top 5 Clientes por cantidad de tableros (gabinetes)
        $topClients = BoardDetail::where('ano', $currentYear)
            ->select('cliente', DB::raw('SUM(gabinetes) as total'))
            ->groupBy('cliente')
            ->orderByDesc('total')
            ->take(5)
            ->get();
            
        // Actividad reciente
        $recentActivity = BoardDetail::latest()->take(5)->get();

        return [
            'totals' => $totals,
            'topClients' => $topClients,
            'recentActivity' => $recentActivity,
            'currentYear' => $currentYear
        ];
    }
}; ?>

<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800 dark:text-white">
            Tableros de Control <span class="text-slate-400 font-normal text-2xl">| {{ $currentYear }}</span>
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Gestión de fabricación de tableros y columnas</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Gabinetes -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <span class="text-sm font-medium text-slate-400">Total Gabinetes</span>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-bold text-slate-800 dark:text-white">
                    {{ $totals->total_gabinetes ?? 0 }}
                </h3>
            </div>
        </div>

        <!-- Columnas -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                </div>
                <span class="text-sm font-medium text-slate-400">Total Columnas</span>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-bold text-slate-800 dark:text-white">
                    {{ $totals->total_columnas ?? 0 }}
                </h3>
            </div>
        </div>

        <!-- Registros -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-violet-50 dark:bg-violet-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="text-sm font-medium text-slate-400">Registros Totales</span>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-bold text-slate-800 dark:text-white">
                    {{ $totals->total_records ?? 0 }}
                </h3>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Clientes -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-6">Mayores Clientes (Gabinetes)</h3>
            <div class="space-y-4">
                @foreach($topClients as $client)
                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm">
                                {{ substr($client->cliente, 0, 2) }}
                            </div>
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $client->cliente }}</span>
                        </div>
                        <span class="font-semibold text-slate-800 dark:text-white">{{ $client->total }} Gabinetes</span>
                    </div>
                @endforeach
                @if($topClients->isEmpty())
                    <p class="text-slate-500 text-center py-4">No hay datos disponibles</p>
                @endif
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-6">Actividad Reciente</h3>
            <div class="relative pl-6 border-l-2 border-slate-200 dark:border-slate-700 space-y-8">
                @foreach($recentActivity as $activity)
                    <div class="relative">
                        <span class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600"></span>
                        <div class="flex flex-col">
                            <span class="text-sm text-slate-400 mb-1">{{ $activity->created_at->diffForHumans() }}</span>
                            <span class="font-medium text-slate-800 dark:text-white">{{ $activity->proyecto_numero }} - {{ Str::limit($activity->descripcion_proyecto, 40) }}</span>
                            <span class="text-sm text-slate-500">{{ $activity->cliente }} • G:{{ $activity->gabinetes }} C:{{ $activity->columnas }}</span>
                        </div>
                    </div>
                @endforeach
                @if($recentActivity->isEmpty())
                    <p class="text-slate-500 text-sm">No hay actividad reciente</p>
                @endif
            </div>
        </div>
    </div>
</div>
