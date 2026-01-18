@php
    $role = '';
    if (auth()->user()->hasRole('Manager')) {
        $importRoute = 'manager.hours.import';
        $createRoute = 'manager.hours.create';
        $editRoutePrefix = 'manager.hours.edit';
    } elseif (auth()->user()->hasRole('Gestor de Horas')) {
        $importRoute = 'hours.import';
        $createRoute = 'hours.create';
        $editRoutePrefix = 'hours.edit';
    } else {
        $importRoute = 'admin.hours.import';
        $createRoute = 'admin.hours.create';
        $editRoutePrefix = 'admin.hours.edit';
    }
@endphp

<x-app-layout>

    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="mb-4 md:mb-0">
                        <h1 class="text-3xl font-bold text-white mb-2 tracking-tight">Historial de Horas</h1>
                        <p class="text-orange-100 text-lg font-medium opacity-90">Registro completo y auditoría de tiempos</p>
                    </div>
                    <div class="flex space-x-3">
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

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800">Últimos Registros</h3>
                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Mostrando últimos 50 registros</span>
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
                                    <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Hs</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Pond.</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Hs Pond.</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
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
                                    <td class="px-4 py-3 font-bold text-gray-900">{{ number_format($hour->hs, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ number_format($hour->ponderador, 4) }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ number_format($hour->horas_ponderadas, 2) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route($editRoutePrefix, $hour) }}" class="text-orange-600 hover:text-orange-900 font-bold transition-colors flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <div class="bg-gray-100 p-4 rounded-full mb-3">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900">No hay registros de horas</h3>
                                            <p class="text-sm text-gray-500 max-w-sm mx-auto mt-1">Comience importando un archivo Excel o cargando las horas manualmente.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
