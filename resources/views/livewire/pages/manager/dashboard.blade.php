<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Sale;
use App\Models\Client;

new #[Layout('layouts.app')] class extends Component {
    public int $saleCount = 0;
    public int $clientCount = 0;
    public float $monthlyWeightedHours = 0;
    public float $targetHours = 3394.0;
    public float $efficiencyPercentage = 0;

    public function mount() {
        $this->saleCount = Sale::count();
        $this->clientCount = Client::count();
        
        // Calcular horas ponderadas del mes actual
        $this->monthlyWeightedHours = \App\Models\ManufacturingLog::whereMonth('recorded_at', now()->month)
            ->whereYear('recorded_at', now()->year)
            ->sum('hours_weighted');
            
        if ($this->targetHours > 0) {
            $this->efficiencyPercentage = ($this->monthlyWeightedHours / $this->targetHours) * 100;
        }
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Panel de Gestión') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Ventas -->
            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-green-50 text-green-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Histórico</span>
                </div>
                <p class="text-3xl font-extrabold text-gray-900">{{ number_format($saleCount) }}</p>
                <p class="text-sm text-gray-500 mt-1 font-medium italic">Facturas Procesadas</p>
            </div>

            <!-- Clientes -->
            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cartera</span>
                </div>
                <p class="text-3xl font-extrabold text-gray-900">{{ number_format($clientCount) }}</p>
                <p class="text-sm text-gray-500 mt-1 font-medium italic">Clientes Únicos</p>
            </div>

            <!-- Horas Ponderadas -->
            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-orange-50 text-orange-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Actividad</span>
                </div>
                <p class="text-3xl font-extrabold text-gray-900">{{ number_format($monthlyWeightedHours, 1) }}h</p>
                <p class="text-sm text-gray-500 mt-1 font-medium italic">H. Ponderadas este Mes</p>
            </div>

            <!-- Eficiencia -->
            <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Performance</span>
                </div>
                <p class="text-3xl font-extrabold text-gray-900">{{ number_format($efficiencyPercentage, 1) }}%</p>
                <div class="mt-2 w-full bg-gray-100 rounded-full h-2 overflow-hidden shadow-inner">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-1000" style="width: {{ min(100, $efficiencyPercentage) }}%"></div>
                </div>
            </div>
        </div>
    
        <!-- Catálogos Maestros -->
        <div class="mt-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Gestión de Catálogos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Proyectos -->
                <a href="{{ route('manager.catalogs.projects.index') }}" class="block bg-white p-6 rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-blue-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900">Proyectos</h4>
                    <p class="text-sm text-gray-500 mt-1">Obras y Servicios activos</p>
                </a>

                <!-- Clientes -->
                <a href="{{ route('manager.catalogs.clients.index') }}" class="block bg-white p-6 rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-orange-50 text-orange-600 rounded-xl group-hover:bg-orange-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900">Clientes</h4>
                    <p class="text-sm text-gray-500 mt-1">Cartera de Clientes</p>
                </a>

                <!-- Proveedores -->
                <a href="{{ route('manager.catalogs.suppliers.index') }}" class="block bg-white p-6 rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl group-hover:bg-indigo-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900">Proveedores</h4>
                    <p class="text-sm text-gray-500 mt-1">Gestión de Compras</p>
                </a>

                <!-- Personal -->
                <a href="{{ route('manager.catalogs.employees.index') }}" class="block bg-white p-6 rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-orange-50 text-orange-600 rounded-xl group-hover:bg-orange-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900">Personal</h4>
                    <p class="text-sm text-gray-500 mt-1">Alias y Funciones</p>
                </a>
            </div>
        </div>

        <!-- Administración de Accesos -->
        <div class="mt-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Administración de Accesos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Usuarios -->
                <a href="{{ route('manager.users.index') }}" class="block bg-white p-6 rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gray-50 text-gray-600 rounded-xl group-hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900">Usuarios</h4>
                    <p class="text-sm text-gray-500 mt-1">Altas, bajas y perfiles</p>
                </a>

                <!-- Roles y Permisos -->
                <a href="{{ route('manager.roles.index') }}" class="block bg-white p-6 rounded-3xl border border-gray-200 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gray-50 text-gray-600 rounded-xl group-hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900">Roles y Permisos</h4>
                    <p class="text-sm text-gray-500 mt-1">Configurar vistas y accesos</p>
                </a>
            </div>
        </div>

        <!-- Call to Action Section -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-8 flex flex-col md:flex-row items-center justify-between mt-8">
            <div class="mb-6 md:mb-0">
                <h3 class="text-xl font-bold text-gray-900 mb-2">¿Necesitas analizar más a fondo?</h3>
                <p class="text-gray-500">Accede a los tableros avanzados de Grafana para ver correlaciones y tendencias.</p>
            </div>
            <a href="{{ route('manager.boards.dashboard') }}" class="inline-flex items-center px-6 py-3 bg-gray-900 text-white font-bold rounded-2xl hover:bg-gray-800 transition-all shadow-sm">
                Ir a Inteligencia
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>
</div>
