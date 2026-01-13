<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('🧪 Test CSV Import Analyzer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-bold mb-4">📊 Historial de Importaciones (Base de Datos)</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Historial de Ventas -->
                        <div>
                            <h4 class="text-md font-semibold text-indigo-600 mb-2">🛒 Últimas Ventas</h4>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Fecha</th>
                                            <th class="px-3 py-2 text-left">Cliente</th>
                                            <th class="px-3 py-2 text-left">Monto</th>
                                            <th class="px-3 py-2 text-left">Moneda</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($sales as $sale)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap">{{ $sale->fecha->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2">{{ $sale->cliente_nombre }}</td>
                                            <td class="px-3 py-2 font-mono">{{ number_format($sale->monto, 2) }}</td>
                                            <td class="px-3 py-2">{{ $sale->moneda }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-4 text-center text-gray-400">Sin registros</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Historial de Presupuestos -->
                        <div>
                            <h4 class="text-md font-semibold text-green-600 mb-2">📑 Últimos Presupuestos</h4>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Fecha</th>
                                            <th class="px-3 py-2 text-left">Cliente</th>
                                            <th class="px-3 py-2 text-left">Monto</th>
                                            <th class="px-3 py-2 text-left">Moneda</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($budgets as $budget)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap">{{ $budget->fecha->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2">{{ $budget->cliente_nombre }}</td>
                                            <td class="px-3 py-2 font-mono">{{ number_format($budget->monto, 2) }}</td>
                                            <td class="px-3 py-2">{{ $budget->moneda }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-4 text-center text-gray-400">Sin registros</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Botón para intentar importar nuevo (Opcional) -->
                    <div class="mt-8 border-t pt-6">
                         <p class="text-sm text-gray-500 mb-4">Para probar nuevas importaciones, use el Importador Oficial en el Dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
