<x-app-layout>
    <x-slot name="header">
         <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Historial de Tableros</h1>
                        <p class="text-orange-100 text-sm">Registro histórico y seguimiento de proyectos de tableros eléctricos.</p>
                    </div>
                    
                    <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.boards.create' : (auth()->user()->hasRole('Gestor de Tableros') ? 'boards.create' : 'admin.boards.create')) }}" 
                       class="inline-flex items-center px-5 py-2.5 bg-white text-orange-700 rounded-xl font-bold shadow-md hover:bg-orange-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuevo Tablero
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="p-6">
                    
                    @if (session('message'))
                         <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="font-medium">{{ session('message') }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between items-center mb-6">
                        <p class="text-gray-500 text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Mostrando los últimos 50 registros cargados.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Año</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Proyecto</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th scope="col" class="px-3 py-2 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Columnas</th>
                                    <th scope="col" class="px-3 py-2 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Gabinetes</th>
                                    <th scope="col" class="px-3 py-2 text-center text-xs font-bold text-gray-500 uppercase tracking-wider" title="Suma de: Potencia + Pot/Control + Control + Intervención">
                                        Horas Totales
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($boards as $board)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">{{ $board->ano }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-gray-900">{{ $board->proyecto_numero }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">{{ $board->cliente }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-500 max-w-xs truncate" title="{{ $board->descripcion_proyecto }}">
                                            {{ Str::limit($board->descripcion_proyecto, 30) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-center font-medium text-gray-900">{{ $board->columnas }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-center font-medium text-gray-900">{{ $board->gabinetes }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-center font-bold text-orange-600" title="Potencia: {{ $board->potencia }} | Control: {{ $board->control }} | P/C: {{ $board->pot_control }} | Int: {{ $board->intervencion }}">
                                            {{ $board->potencia + $board->pot_control + $board->control + $board->intervencion }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.boards.edit' : (auth()->user()->hasRole('Gestor de Tableros') ? 'boards.edit' : 'admin.boards.edit'), $board->id) }}" class="text-orange-600 hover:text-orange-900 font-bold hover:underline">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                             <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                <span class="text-gray-500 font-medium">No hay registros recientes de tableros.</span>
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
