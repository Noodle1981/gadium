<?php

use Livewire\Volt\Component;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $selectedYear;
    public $selectedMonth;

    public function mount()
    {
        $this->selectedYear = Carbon::now()->year;
        $this->selectedMonth = 'all'; 
    }

    public function with()
    {
        // Query base
        $query = Budget::query();

        // Filtro de Año
        $query->whereYear('fecha', $this->selectedYear);

        // Filtro de Mes
        if ($this->selectedMonth !== 'all') {
            $query->whereMonth('fecha', $this->selectedMonth);
        }

        // Métricas Generales
        $totalPresupuestos = (clone $query)->count();
        $montoTotal = (clone $query)->sum('monto');
        $promedio = $totalPresupuestos > 0 ? $montoTotal / $totalPresupuestos : 0;
        
        $cantidadClientes = (clone $query)->distinct('cliente_nombre')->count('cliente_nombre');

        // Top Cliente
        $topCliente = (clone $query)
            ->select('cliente_nombre', DB::raw('SUM(monto) as total_presupuestado'))
            ->groupBy('cliente_nombre')
            ->orderByDesc('total_presupuestado')
            ->first();

        // Desglose de Estados
        $estados = (clone $query)
            ->select('estado', DB::raw('COUNT(*) as cantidad'))
            ->whereNotNull('estado')
            ->where('estado', '!=', '')
            ->groupBy('estado')
            ->orderByDesc('cantidad')
            ->get();

        // Años disponibles
        $añosDisponibles = Budget::selectRaw('strftime("%Y", fecha) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();
            
        // Meses para el select
        $meses = [];
        for ($i = 1; $i <= 12; $i++) {
            $meses[$i] = Carbon::create(null, $i, 1)->locale('es')->monthName;
        }

        return [
            'totalPresupuestos' => $totalPresupuestos,
            'montoTotal' => $montoTotal,
            'promedio' => $promedio,
            'cantidadClientes' => $cantidadClientes,
            'topCliente' => $topCliente,
            'estados' => $estados,
            'añosDisponibles' => $añosDisponibles,
            'nombresMeses' => $meses,
        ];
    }
}; ?>

<div class="h-[calc(100vh-65px)] overflow-hidden bg-gray-50 flex flex-col p-6">
    <!-- Header y Filtros -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Panel de Presupuestos</h1>
            <p class="text-gray-500 mt-1">Gestión y control de estimaciones</p>
        </div>
        
        <div class="flex gap-4 bg-white p-2 rounded-xl shadow-sm border border-gray-100">
            <!-- Selector de Año -->
            <select wire:model.live="selectedYear" 
                    class="bg-gray-50 border-0 rounded-lg text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-green-500 py-2 pl-3 pr-8 cursor-pointer hover:bg-gray-100 transition-colors">
                @foreach($añosDisponibles as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>

            <!-- Selector de Mes -->
            <select wire:model.live="selectedMonth" 
                    class="bg-gray-50 border-0 rounded-lg text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-green-500 py-2 pl-3 pr-8 cursor-pointer hover:bg-gray-100 transition-colors">
                <option value="all">Todo el año</option>
                @foreach($nombresMeses as $num => $nombre)
                    <option value="{{ $num }}">{{ ucfirst($nombre) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Grid de KPIs Principales -->
    <div class="grid grid-cols-4 gap-6 mb-8">
        <!-- Total Presupuestos -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Presupuestos</p>
            <h3 class="text-4xl font-bold text-gray-900">{{ number_format($totalPresupuestos, 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center text-sm text-green-600 font-medium bg-green-50 w-fit px-2 py-1 rounded-full">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                Emitidos
            </div>
        </div>

        <!-- Monto Total Presupuestado (Green/Emerald Theme) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Monto Presupuestado</p>
            <h3 class="text-4xl font-bold text-gray-900">${{ number_format($montoTotal / 1000, 1, ',', '.') }}K</h3>
            <div class="mt-4 flex items-center text-sm text-gray-500 font-medium w-fit px-2 py-1">
                Moneda base (USD)
            </div>
        </div>

        <!-- Promedio (Teal Theme) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 3.666V14m-3-3.666L15 7M6 6h12l-1.5 13.5a1.5 1.5 0 01-1.463 1.258h-6.074a1.5 1.5 0 01-1.463-1.258L6 6z"></path></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Monto Promedio</p>
            <h3 class="text-4xl font-bold text-gray-900">${{ number_format($promedio, 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center text-sm text-teal-600 font-medium bg-teal-50 w-fit px-2 py-1 rounded-full">
                <span class="w-2 h-2 bg-teal-500 rounded-full mr-2"></span>
                Por presupuesto
            </div>
        </div>

        <!-- Clientes (Blue Theme) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Clientes Potenciales</p>
            <h3 class="text-4xl font-bold text-gray-900">{{ number_format($cantidadClientes, 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center text-sm text-blue-600 font-medium bg-blue-50 w-fit px-2 py-1 rounded-full">
                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                Cotizados
            </div>
        </div>
    </div>

    <!-- Sección Inferior: Estados y Top Cliente -->
    <div class="grid grid-cols-2 gap-6 h-full min-h-0">
        <!-- Tarjeta de Estados -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Estado de Presupuestos
            </h3>
            <div class="space-y-4 overflow-y-auto pr-2 custom-scrollbar">
                @foreach($estados as $estado)
                    <div class="group p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors border border-transparent hover:border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-700">{{ $estado->estado }}</span>
                            <span class="font-bold text-gray-900">{{ $estado->cantidad }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-green-500 h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ $totalPresupuestos > 0 ? ($estado->cantidad / $totalPresupuestos) * 100 : 0 }}%"></div>
                        </div>
                        <div class="flex justify-end mt-1">
                            <span class="text-xs text-gray-500 font-medium">{{ $totalPresupuestos > 0 ? number_format(($estado->cantidad / $totalPresupuestos) * 100, 1) : 0 }}% del total</span>
                        </div>
                    </div>
                @endforeach
                
                @if($estados->isEmpty())
                    <div class="text-center py-10 text-gray-400">
                        No hay datos de estados para este periodo
                    </div>
                @endif
            </div>
        </div>

        <!-- Tarjeta de Cliente Top -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-yellow-400 to-green-500"></div>
            
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Cliente Destacado</h3>
            
            @if($topCliente)
                <div class="mb-4 relative">
                    <div class="w-20 h-20 bg-gradient-to-br from-yellow-100 to-green-100 rounded-full flex items-center justify-center mx-auto mb-1 shadow-inner">
                         <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    </div>
                    <div class="absolute -bottom-1 -right-1 bg-yellow-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-md">#1</div>
                </div>
                
                <h2 class="text-2xl font-black text-gray-900 mb-1 max-w-sm leading-tight">{{ $topCliente->cliente_nombre }}</h2>
                <p class="text-sm text-gray-500 font-medium mb-4">Mayor volumen cotizado</p>
                
                <div class="bg-gray-50 px-4 py-3 rounded-xl border border-gray-100 w-full max-w-xs mx-auto">
                    <p class="text-[10px] text-gray-400 font-bold uppercase mb-0.5">Total Presupuestado</p>
                    <p class="text-lg sm:text-xl font-mono font-bold text-gray-900 truncate" title="${{ number_format($topCliente->total_presupuestado, 0, ',', '.') }}">
                        ${{ number_format($topCliente->total_presupuestado, 0, ',', '.') }}
                    </p>
                </div>
            @else
                <div class="text-gray-400 text-sm">
                    No hay datos de clientes para este periodo
                </div>
            @endif
        </div>
    </div>
</div>
