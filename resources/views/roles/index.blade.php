<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Gestión de Roles') }}
            </h2>
            <a href="{{ route('app.roles.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Rol
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @php
                $systemRoles = $roles->filter(fn($r) => in_array($r->name, ['Super Admin', 'Admin', 'Manager']));
                $sectorRoles = $roles->reject(fn($r) => in_array($r->name, ['Super Admin', 'Admin', 'Manager']));
            @endphp
            
            <!-- Roles de Sistema -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-4 uppercase tracking-wider">Roles de Sistema</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($systemRoles as $role)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-200 border-l-4 border-gray-500">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $role->name }}
                                    </h3>
                                    @if($role->name === 'Super Admin')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Protegido
                                        </span>
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">{{ $role->users_count }}</span> usuario(s) asignado(s)
                                    </p>
                                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                        <div class="bg-gray-600 h-1.5 rounded-full" style="width: 100%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Acceso Total</p>
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('app.roles.permissions', $role) }}" class="flex-1 inline-flex justify-center items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Ver Permisos
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Roles Personalizados -->
            <div>
                <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-4 uppercase tracking-wider">Roles Personalizados</h3>
                
                @if($sectorRoles->isEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center text-gray-500">
                        No hay roles personalizados aún. <a href="{{ route('app.roles.create') }}" class="text-primary hover:underline">Crear nuevo rol</a>.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($sectorRoles as $role)
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-200 border-l-4 border-indigo-500">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/50 rounded-lg mr-3">
                                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                                {{ $role->name }}
                                            </h3>
                                        </div>
                                    </div>

                                    <div class="mb-6 space-y-3">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Personal Asignado:</span>
                                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $role->users_count }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Permisos Activos:</span>
                                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $role->permissions->count() }}</span>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <a href="{{ route('app.roles.permissions', $role) }}" class="flex-1 inline-flex justify-center items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Configurar
                                        </a>
                                        
                                        <a href="{{ route('app.roles.edit', $role) }}" class="inline-flex justify-center items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </a>

                                        @if($role->users_count === 0)
                                            <form action="{{ route('app.roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este sector?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex justify-center items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
