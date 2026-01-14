@php
    $isManager = auth()->user()->hasRole('Manager');
    $presupuestoRoute = $isManager ? 'manager.historial.presupuesto' : 'admin.historial.presupuesto';
    $importRoute = $isManager ? 'manager.sales.import' : 'admin.sales.import';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('📊 Historial de Ventas (Tango)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-bold">Últimas 50 Ventas Registradas</h3>
                        <span class="text-sm text-gray-500">Total: {{ $sales->count() }} registros</span>
                    </div>

                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Comprobante</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Monto</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Moneda</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cód. Cliente</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">N° Remito</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Tipo Comp.</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cond. Vta</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">% Desc</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cotiz</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cód. Transp</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Transporte</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cód. Artículo</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Descripción</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Depósito</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">UM</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cantidad</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Precio</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Tot. S/Imp</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">N° Comp Rem</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cant. Rem</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Fecha Rem</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($sales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $sale->fecha->format('d/m/Y') }}</td>
                                    <td class="px-2 py-2">{{ $sale->cliente_nombre }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $sale->comprobante }}</td>
                                    <td class="px-2 py-2 font-mono whitespace-nowrap">{{ number_format($sale->monto, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2">{{ $sale->moneda }}</td>
                                    <td class="px-2 py-2">{{ $sale->cod_cli }}</td>
                                    <td class="px-2 py-2">{{ $sale->n_remito }}</td>
                                    <td class="px-2 py-2">{{ $sale->t_comp }}</td>
                                    <td class="px-2 py-2">{{ $sale->cond_vta }}</td>
                                    <td class="px-2 py-2">{{ $sale->porc_desc }}</td>
                                    <td class="px-2 py-2">{{ $sale->cotiz }}</td>
                                    <td class="px-2 py-2">{{ $sale->cod_transp }}</td>
                                    <td class="px-2 py-2">{{ $sale->nom_transp }}</td>
                                    <td class="px-2 py-2">{{ $sale->cod_articu }}</td>
                                    <td class="px-2 py-2">{{ $sale->descripcio }}</td>
                                    <td class="px-2 py-2">{{ $sale->cod_dep }}</td>
                                    <td class="px-2 py-2">{{ $sale->um }}</td>
                                    <td class="px-2 py-2">{{ $sale->cantidad }}</td>
                                    <td class="px-2 py-2 font-mono">{{ number_format($sale->precio, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2 font-mono">{{ number_format($sale->tot_s_imp, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2">{{ $sale->n_comp_rem }}</td>
                                    <td class="px-2 py-2">{{ $sale->cant_rem }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap">{{ $sale->fecha_rem ? $sale->fecha_rem->format('d/m/Y') : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="23" class="px-6 py-8 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-lg font-medium">No hay ventas registradas</p>
                                            <p class="text-sm">Importe datos desde el módulo de importación</p>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Importar Ventas
                        </a>
                        <a href="{{ route($presupuestoRoute) }}" class="text-indigo-600 hover:text-indigo-900">
                            Ver Historial de Presupuestos →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
