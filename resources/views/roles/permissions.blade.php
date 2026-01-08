<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Permisos del Rol: ') }} <span class="text-primary">{{ $role->name }}</span>
            </h2>
            <a href="{{ route('roles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($role->name === 'Super Admin')
                <div class="mb-6 bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Advertencia:</strong>
                    <span class="block sm:inline">El rol Super Admin tiene todos los permisos y no puede ser modificado.</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('roles.permissions.update', $role) }}">
                        @csrf

                        <div class="space-y-6">
                            @foreach($modules as $moduleName => $permissions)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $moduleName }}
                                        </h3>
                                        @if($role->name !== 'Super Admin')
                                            <button type="button" onclick="toggleModule('{{ $moduleName }}')" class="text-sm text-primary hover:text-primary-700">
                                                Seleccionar todos
                                            </button>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                        @foreach($permissions as $permission)
                                            <label class="flex items-center space-x-3 cursor-pointer">
                                                <input 
                                                    type="checkbox" 
                                                    name="permissions[]" 
                                                    value="{{ $permission->name }}"
                                                    data-module="{{ $moduleName }}"
                                                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                                    {{ $role->name === 'Super Admin' ? 'disabled' : '' }}
                                                    class="rounded border-gray-300 dark:border-gray-600 text-primary shadow-sm focus:ring-primary"
                                                >
                                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                                    {{ ucfirst(str_replace('_', ' ', explode('_', $permission->name)[0])) }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($role->name !== 'Super Admin')
                            <div class="mt-6 flex items-center justify-end gap-4">
                                <a href="{{ route('roles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Cancelar
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-600 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Guardar Permisos
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleModule(moduleName) {
            const checkboxes = document.querySelectorAll(`input[data-module="${moduleName}"]`);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = !allChecked;
                }
            });
        }
    </script>
</x-app-layout>
