<?php
// Since this is a blade view extending app layout, logic is handled in routes/web.php closure
// Logic for variables $sales, $isManager, $presupuestoRoute, $importRoute is passed from route
?>

<x-app-layout>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Historial de Ventas</h1>
                        <p class="text-orange-100 text-sm">Registro completo de operaciones comerciales</p>
                    </div>
                    <div class="hidden md:block">
                        <svg class="w-12 h-12 text-orange-300 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ expanded: false }">
        <div class="mx-auto sm:px-6 lg:px-8 transition-all duration-300" :class="expanded ? 'max-w-full' : 'max-w-7xl'">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-4 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4">
                            <h3 class="text-lg font-bold">Últimas 50 Ventas Registradas</h3>
                            <button @click="expanded = !expanded" 
                                    class="inline-flex items-center px-3 py-1 bg-orange-100 border border-orange-200 rounded-md font-semibold text-xs text-orange-800 uppercase tracking-widest hover:bg-orange-200 active:bg-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    title="Alternar vista en pantalla completa">
                                <span x-show="!expanded" class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                    >>> Expandir Vista Tabla
                                </span>
                                <span x-show="expanded" class="flex items-center" style="display: none;">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Contraer Vista
                                </span>
                            </button>
                        </div>
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
                                    
                                    <!-- Columnas Secundarias -->
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cód. Cliente</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">N° Remito</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Tipo Comp.</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cond. Vta</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">% Desc</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cotiz</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cód. Transp</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Transporte</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cód. Artículo</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Descripción</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Depósito</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">UM</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cantidad</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Precio</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Tot. S/Imp</th>
                                    
                                    <th class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Acciones</th>
                                    
                                    <!-- Columnas Secundarias Finales -->
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">N° Comp Rem</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Cant. Rem</th>
                                    <th x-show="expanded" class="px-2 py-2 text-left font-medium text-gray-500 uppercase">Fecha Rem</th>
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
                                    
                                    <!-- Datos Secundarios -->
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->cod_cli }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->n_remito }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->t_comp }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->cond_vta }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->porc_desc }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->cotiz }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->cod_transp }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->nom_transp }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->cod_articu }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->descripcio }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->cod_dep }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->um }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->cantidad }}</td>
                                    <td x-show="expanded" class="px-2 py-2 font-mono">{{ number_format($sale->precio, 2, ',', '.') }}</td>
                                    <td x-show="expanded" class="px-2 py-2 font-mono">{{ number_format($sale->tot_s_imp, 2, ',', '.') }}</td>
                                    
                                    <td class="px-2 py-2 whitespace-nowrap">
                                        <a href="{{ route('sales.edit', $sale) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Editar</a>
                                    </td>
                                    
                                    <!-- Datos Secundarios Finales -->
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->n_comp_rem }}</td>
                                    <td x-show="expanded" class="px-2 py-2">{{ $sale->cant_rem }}</td>
                                    <td x-show="expanded" class="px-2 py-2 whitespace-nowrap">{{ $sale->fecha_rem ? $sale->fecha_rem->format('d/m/Y') : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <!-- Colspan dinámico: 6 visibles + 18 ocultas = 24 total -->
                                    <td :colspan="expanded ? 24 : 6" class="px-6 py-8 text-center text-gray-400">
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

                    <div class="mt-6">
                        <a href="{{ route(auth()->user()->hasRole('Manager') ? 'admin.sales.import' : 'sales.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Importar Ventas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
