<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;


new #[Layout('layouts.app')] class extends Component {

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Panel de Gestión') }}
        </h2>
    </x-slot>

    <div class="space-y-8">

    
    
        {{-- Catálogos Maestros - Comentado temporalmente, las rutas siguen activas para otros roles --}}
        {{-- 
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
        --}}

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
            <a href="{{ route('manager.historial.tableros') }}" class="inline-flex items-center px-6 py-3 bg-gray-900 text-white font-bold rounded-2xl hover:bg-gray-800 transition-all shadow-sm">
                Ir a Inteligencia
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>
</div>
