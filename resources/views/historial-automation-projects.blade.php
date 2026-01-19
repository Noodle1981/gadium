<x-app-layout>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Historial de Proyectos de Automatización</h1>
                        <p class="text-orange-100 text-sm">Registro histórico y seguimiento de proyectos de control y automatización.</p>
                    </div>
                    
                    <a href="{{ route('automation_projects.create') }}" 
                        class="inline-flex items-center px-5 py-2.5 bg-white text-orange-700 rounded-xl font-bold shadow-md hover:bg-orange-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuevo Proyecto
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="p-6">
                    
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
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ID Proyecto</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">FAT</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">PEM</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha Creación</th>
                                    <th scope="col" class="px-3 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($projects as $project)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $project->proyecto_id }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">{{ $project->cliente }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-500 max-w-xs truncate" title="{{ $project->proyecto_descripcion }}">{{ Str::limit($project->proyecto_descripcion, 30) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-bold rounded-lg {{ $project->fat === 'SI' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $project->fat }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-bold rounded-lg {{ $project->pem === 'SI' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $project->pem }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ $project->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('automation_projects.edit', $project) }}" class="text-orange-600 hover:text-orange-900 font-bold hover:underline">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                                </svg>
                                                <span class="text-gray-500 font-medium">No hay registros recientes de automatización.</span>
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
