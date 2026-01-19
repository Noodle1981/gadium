@php
    $createRoute = '';
    $editRoutePrefix = '';

    if (auth()->user()->hasRole('Manager')) {
        $createRoute = 'manager.purchases.create';
        $editRoutePrefix = 'manager.purchases.edit';
    } elseif (auth()->user()->hasRole('Gestor de Compras')) {
        $createRoute = 'purchases.create';
        $editRoutePrefix = 'purchases.edit';
    } else {
        $createRoute = 'admin.purchases.create';
        $editRoutePrefix = 'admin.purchases.edit';
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Historial de Compras</h1>
                        <p class="text-orange-100 text-sm">Registro histórico y seguimiento de ejecución presupuestaria.</p>
                    </div>
                    
                    <a href="{{ route($createRoute) }}" 
                        class="inline-flex items-center px-5 py-2.5 bg-white text-orange-700 rounded-xl font-bold shadow-md hover:bg-orange-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuevo Registro
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="p-6">
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">CC / Año</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Empresa</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th scope="col" class="px-3 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Presupuesto</th>
                                    <th scope="col" class="px-3 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Comprado</th>
                                    <th scope="col" class="px-3 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Resto</th>
                                    <th scope="col" class="px-3 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">% Fact</th>
                                    <th scope="col" class="px-3 py-2 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($purchases as $item)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $item->cc }} <span class="text-gray-400 mx-1">/</span> {{ $item->ano }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">
                                            {{ Str::limit($item->empresa, 20) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                            {{ Str::limit($item->descripcion, 30) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-right text-gray-700 font-medium">
                                            {{ $item->moneda }} {{ number_format($item->materiales_presupuestados, 2) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-right text-gray-700 font-medium">
                                            {{ $item->moneda }} {{ number_format($item->materiales_comprados, 2) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-black {{ $item->resto_valor < 0 ? 'text-red-500' : 'text-green-500' }}">
                                            {{ number_format($item->resto_porcentaje, 1) }}%
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-right text-gray-500">
                                            {{ number_format($item->porcentaje_facturacion, 1) }}%
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-center text-sm font-medium">
                                            <a href="{{ route($editRoutePrefix, $item->id) }}" class="text-orange-600 hover:text-orange-900 font-bold hover:underline">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="text-gray-500 font-medium">No hay registros de compras disponibles.</span>
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
