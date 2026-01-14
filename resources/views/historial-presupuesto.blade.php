@php
    $isManager = auth()->user()->hasRole('Manager');
    $ventasRoute = $isManager ? 'manager.historial.ventas' : 'admin.historial.ventas';
    $importRoute = $isManager ? 'manager.sales.import' : 'admin.sales.import';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('📑 Historial de Presupuestos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-bold">Últimos 50 Presupuestos Registrados</h3>
                        <span class="text-sm text-gray-500">Total: {{ $budgets->count() }} registros</span>
                    </div>

                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Orden de Pedido</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Monto</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Moneda</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Centro Costo</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Nombre Proyecto</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Fecha OC</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Fecha Est. Culm.</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Estado Días</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Fecha Culm. Real</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Env. Facturar</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Nº Factura</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">% Facturación</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Saldo</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Hrs. Ponderadas</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($budgets as $budget)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $budget->fecha->format('d/m/Y') }}</td>
                                    <td class="px-2 py-2">{{ $budget->cliente_nombre }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $budget->comprobante }}</td>
                                    <td class="px-2 py-2 font-mono whitespace-nowrap">{{ number_format($budget->monto, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2">{{ $budget->moneda }}</td>
                                    <td class="px-2 py-2">{{ $budget->centro_costo }}</td>
                                    <td class="px-2 py-2">{{ $budget->nombre_proyecto }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $budget->fecha_oc ? $budget->fecha_oc->format('d/m/Y') : '-' }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $budget->fecha_estimada_culminacion ? $budget->fecha_estimada_culminacion->format('d/m/Y') : '-' }}</td>
                                    <td class="px-2 py-2">{{ $budget->estado_proyecto_dias ?? '-' }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $budget->fecha_culminacion_real ? $budget->fecha_culminacion_real->format('d/m/Y') : '-' }}</td>
                                    <td class="px-2 py-2">{{ $budget->estado }}</td>
                                    <td class="px-2 py-2">{{ $budget->enviado_facturar }}</td>
                                    <td class="px-2 py-2">{{ $budget->nro_factura }}</td>
                                    <td class="px-2 py-2">{{ $budget->porc_facturacion }}</td>
                                    <td class="px-2 py-2 font-mono">{{ $budget->saldo ? number_format($budget->saldo, 2, ',', '.') : '-' }}</td>
                                    <td class="px-2 py-2">{{ $budget->horas_ponderadas ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="17" class="px-6 py-8 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-lg font-medium">No hay presupuestos registrados</p>
                                            <p class="text-sm">Importe datos desde el módulo de importación</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-between items-center">
                        <a href="{{ route($importRoute) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Importar Presupuestos
                        </a>
                        <a href="{{ route($ventasRoute) }}" class="text-green-600 hover:text-green-900">
                            Ver Historial de Ventas →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
