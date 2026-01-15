@php
    $role = '';
    if (auth()->user()->hasRole('Manager')) {
        $importRoute = 'manager.hours.import';
        $editRoutePrefix = 'manager.hours.edit';
    } elseif (auth()->user()->hasRole('Gestor de Horas')) {
        $importRoute = 'hours.import';
        $editRoutePrefix = 'hours.edit';
    } else {
        $importRoute = 'admin.hours.import';
        $editRoutePrefix = 'admin.hours.edit';
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('📊 Historial de Detalles de Horas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-bold">Últimos Registros</h3>
                        <span class="text-sm text-gray-500">Mostrando últimos 50 registros</span>
                    </div>

                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase">Día</th>
                                    <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase">Personal</th>
                                    <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase">Función</th>
                                    <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase">Proyecto</th>
                                    <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase">Hs</th>
                                    <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase">Pond.</th>
                                    <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase">Hs Pond.</th>
                                    <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($hours as $hour)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $hour->fecha->format('d/m/Y') }}</td>
                                    <td class="px-3 py-3">{{ $hour->dia }}</td>
                                    <td class="px-3 py-3 font-medium">{{ $hour->personal }}</td>
                                    <td class="px-3 py-3">{{ $hour->funcion }}</td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $hour->proyecto }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 font-bold">{{ number_format($hour->hs, 2) }}</td>
                                    <td class="px-3 py-3">{{ number_format($hour->ponderador, 4) }}</td>
                                    <td class="px-3 py-3">{{ number_format($hour->horas_ponderadas, 2) }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <a href="{{ route($editRoutePrefix, $hour) }}" class="text-indigo-600 hover:text-indigo-900 font-medium transition-colors">
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-lg font-medium">No hay registros de horas</p>
                                            <p class="text-sm">Importe datos o cargue manualmente</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-between items-center">
                        <a href="{{ route($importRoute) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Importar Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
