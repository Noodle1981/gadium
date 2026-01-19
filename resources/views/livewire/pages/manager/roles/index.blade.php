<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public function with()
    {
        $query = Role::withCount('users');
        
        // Security: Managers cannot see Super Admin
        if (Auth::user()->hasRole('Manager') && !Auth::user()->hasRole('Super Admin')) {
            $query->where('name', '!=', 'Super Admin');
        }

        $roles = $query->get();

        $systemRoles = $roles->filter(fn($r) => in_array($r->name, ['Super Admin', 'Admin', 'Manager']));
        $customRoles = $roles->reject(fn($r) => in_array($r->name, ['Super Admin', 'Admin', 'Manager']));

        return [
            'systemRoles' => $systemRoles,
            'customRoles' => $customRoles
        ];
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);
        
        // Extra Security Check
        if (in_array($role->name, ['Super Admin', 'Admin', 'Manager'])) {
            session()->flash('error', 'No se pueden eliminar roles de sistema.');
            return;
        }

        if ($role->users()->count() > 0) {
            session()->flash('error', 'No se puede eliminar un rol con usuarios asignados.');
            return;
        }

        $role->delete();
        session()->flash('success', 'Rol eliminado exitosamente.');
    }
}; ?>

<div class="min-h-screen bg-gray-100 pb-12">
    <!-- Header -->
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Gestión de Roles</h1>
                        <p class="text-orange-100 text-sm">Definición de perfiles y control de acceso al sistema.</p>
                    </div>
                    
                    @if(auth()->user()->can('create_roles'))
                    <a href="{{ route('manager.roles.create') }}" 
                        class="inline-flex items-center px-5 py-2.5 bg-white text-orange-700 rounded-xl font-bold shadow-md hover:bg-orange-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuevo Rol
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Success/Error Messages -->
             @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Roles de Sistema -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-gray-200 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 tracking-tight">Roles de Sistema</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($systemRoles as $role)
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all group">
                            <!-- Banner Card -->
                            <div class="h-2 w-full {{ $role->name === 'Super Admin' ? 'bg-red-500' : ($role->name === 'Manager' ? 'bg-orange-500' : 'bg-blue-500') }}"></div>
                            
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-orange-600 transition-colors">
                                        {{ $role->name }}
                                    </h3>
                                    @if($role->name === 'Super Admin')
                                        <span class="px-2 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-lg uppercase tracking-wider">
                                            Protegido
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg uppercase tracking-wider">
                                            Core
                                        </span>
                                    @endif
                                </div>

                                <div class="space-y-4 mb-6">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <span class="text-sm font-medium text-gray-500">Usuarios Activos</span>
                                        <span class="text-lg font-bold text-gray-900">{{ $role->users_count }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500">
                                        Rol fundamental del sistema con permisos predefinidos y acceso crítico.
                                    </p>
                                </div>

                                <a href="{{ route('manager.roles.permissions', $role) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-gray-800 text-white text-sm font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Ver Permisos
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Roles Personalizados -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-orange-100 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 tracking-tight">Roles Personalizados</h3>
                </div>

                @if($customRoles->isEmpty())
                    <div class="bg-white rounded-2xl p-12 text-center border border-dashed border-gray-300">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay roles personalizados</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza creando un rol para definir grupos de acceso específicos.</p>
                        <div class="mt-6">
                            <a href="{{ route('manager.roles.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Crear Nuevo Rol
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($customRoles as $role)
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all group relative">
                                <!-- Actions Overlay -->
                                <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('manager.roles.edit', $role) }}" class="p-2 bg-white text-orange-600 rounded-lg shadow-sm hover:bg-orange-50 border border-gray-200" title="Editar Nombre">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                     @if($role->users_count === 0)
                                        <button wire:confirm="¿Eliminar este rol?" wire:click="delete({{ $role->id }})" class="p-2 bg-white text-red-600 rounded-lg shadow-sm hover:bg-red-50 border border-gray-200" title="Eliminar Rol">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>

                                <div class="p-6">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2.5 bg-orange-50 rounded-xl group-hover:bg-orange-100 transition-colors">
                                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 leading-tight">
                                            {{ $role->name }}
                                        </h3>
                                    </div>

                                    <div class="space-y-3 mb-6">
                                        <div class="flex justify-between items-center text-sm p-3 bg-gray-50 rounded-lg">
                                            <span class="text-gray-500">Personal Asignado</span>
                                            <span class="font-bold text-gray-900">{{ $role->users_count }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-sm p-3 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                                            <span class="text-gray-500">Permisos Configurados</span>
                                            <span class="font-bold text-orange-600">{{ $role->permissions->count() }}</span>
                                        </div>
                                    </div>

                                    <a href="{{ route('manager.roles.permissions', $role) }}" 
                                       class="w-full inline-flex justify-center items-center px-4 py-3 bg-orange-600 text-white font-bold rounded-xl hover:bg-orange-700 transition-colors shadow-lg shadow-orange-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Configurar Permisos
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
