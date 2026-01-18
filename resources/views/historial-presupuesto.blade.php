@php
    $isManager = auth()->user()->hasRole('Manager');
    $ventasRoute = $isManager ? 'manager.historial.ventas' : 'admin.historial.ventas';
    $importRoute = 'admin.sales.import';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Historial de Presupuestos</h1>
                        <p class="text-orange-100 text-sm">Gestión completa de estimaciones y seguimiento</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('budget.create') }}" class="inline-flex items-center px-4 py-2 bg-white text-orange-700 rounded-lg font-bold shadow-md hover:bg-orange-50 transition-colors wire:navigate">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Crear Presupuesto
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
                        <h3 class="text-lg font-bold text-gray-800">Últimos 50 Presupuestos Registrados</h3>
                        <span class="text-sm text-gray-500">Mostrando {{ $budgets->count() }} registros recientes</span>
                    </div>

                    <div class="overflow-x-auto border rounded-xl shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Fecha</th>
                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Cliente</th>
                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Orden Pedido</th>
                                    <th class="px-3 py-3 text-right font-bold text-orange-800 uppercase tracking-wider">Monto</th>
                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider">Moneda</th>
                                    <th class="px-3 py-3 text-left font-bold text-orange-800 uppercase tracking-wider hidden md:table-cell">Proyecto</th>
                                    <th class="px-3 py-3 text-center font-bold text-orange-800 uppercase tracking-wider">Estado</th>
                                    <th class="px-3 py-3 text-center font-bold text-orange-800 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($budgets as $budget)
                                <tr class="hover:bg-orange-50/50 transition-colors">
                                    <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-900">{{ $budget->fecha->format('d/m/Y') }}</td>
                                    <td class="px-3 py-3 font-medium">{{ $budget->cliente_nombre }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-500">{{ $budget->comprobante }}</td>
                                    <td class="px-3 py-3 text-right font-mono font-bold text-gray-900">{{ number_format($budget->monto, 2, ',', '.') }}</td>
                                    <td class="px-3 py-3 text-gray-500">{{ $budget->moneda }}</td>
                                    <td class="px-3 py-3 text-gray-500 hidden md:table-cell truncate max-w-xs" title="{{ $budget->nombre_proyecto }}">{{ $budget->nombre_proyecto ?? '-' }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="px-2 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full uppercase tracking-wide
                                            {{ $budget->estado === 'Finalizado' ? 'bg-green-100 text-green-800' : 
                                               ($budget->estado === 'Cancelado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $budget->estado ?? 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <a href="{{ route('budget.edit', $budget) }}" class="text-orange-600 hover:text-orange-900 font-bold hover:underline" wire:navigate>Editar</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <div class="bg-orange-50 p-4 rounded-full mb-3">
                                                <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </div>
                                            <p class="text-lg font-medium text-gray-900">No hay presupuestos registrados</p>
                                            <p class="text-sm mt-1">Comienza importando un archivo o creando uno manualmente</p>
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
