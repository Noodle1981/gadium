<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $showRevoked = false;
    public $perPage = 10;

    // Reset pagination when filtering
    public function updatedSearch() { $this->resetPage(); }
    public function updatedRoleFilter() { $this->resetPage(); }
    public function updatedShowRevoked() { $this->resetPage(); }

    public function restore($id)
    {
        $user = User::withTrashed()->find($id);
        
        if ($user) {
            $user->restore();
            session()->flash('success', 'Acceso restaurado exitosamente para el usuario ' . $user->name);
        }
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->find($id);

        if ($user) {
            $user->forceDelete();
            session()->flash('success', 'Usuario eliminado definitivamente: ' . $user->name);
        }
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        // Prevent deleting yourself or Super Admin
        if ($user->hasRole('Super Admin')) {
             session()->flash('error', 'No se puede eliminar al Super Administrador.');
             return;
        }
        if ($user->id === Auth::id()) {
             session()->flash('error', 'No puedes eliminar tu propia cuenta.');
             return;
        }

        $user->delete(); // Soft delete
        session()->flash('success', 'Usuario desactivado exitosamente.');
    }

    public function with()
    {
        $query = User::query()
            ->when($this->search, function ($q) {
                $q->where(function($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($q) {
                $q->whereHas('roles', function ($sub) {
                    $sub->where('name', $this->roleFilter);
                });
            })
            ->when($this->showRevoked, function ($q) {
                $q->onlyTrashed();
            });

        // Security: Managers cannot see Super Admins
        if (Auth::user()->hasRole('Manager') && !Auth::user()->hasRole('Super Admin')) {
            $query->whereDoesntHave('roles', function($q) {
                $q->where('name', 'Super Admin');
            });
        }

        $users = $query->with('roles')->latest()->paginate($this->perPage);
        $roles = Role::all();

        return [
            'users' => $users,
            'roles' => $roles
        ];
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Gestión de Usuarios</h1>
                        <p class="text-orange-100 text-sm">Administración de cuentas, roles y accesos al sistema.</p>
                    </div>
                    
                    @can('create_users')
                    <a href="{{ route('manager.users.create') }}" 
                        class="inline-flex items-center px-5 py-2.5 bg-white text-orange-700 rounded-xl font-bold shadow-md hover:bg-orange-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Nuevo Usuario
                    </a>
                    @endcan
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

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <!-- Toolbar -->
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row gap-4 justify-between items-center">
                    <!-- Search -->
                    <div class="flex-1 w-full md:w-auto relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" 
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm transition-all" 
                            placeholder="Buscar por nombre o email...">
                    </div>
                    
                    <!-- Filters -->
                    <div class="flex gap-4 w-full md:w-auto">
                        <div class="w-full md:w-48">
                            <select wire:model.live="roleFilter" class="block w-full pl-3 pr-10 py-2.5 text-sm border-gray-200 focus:outline-none focus:ring-orange-500 focus:border-orange-500 rounded-xl">
                                <option value="">Todos los Roles</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center whitespace-nowrap">
                            <label class="inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" wire:model.live="showRevoked" class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 font-medium">Ver Revocados</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha Registro</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors {{ $user->trashed() ? 'bg-red-50/50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold shadow-sm">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                @if($user->trashed())
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        Acceso Revocado
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @foreach($user->roles as $role)
                                            @php
                                                $badgeClass = match($role->name) {
                                                    'Super Admin' => 'bg-purple-100 text-purple-800',
                                                    'Admin' => 'bg-blue-100 text-blue-800',
                                                    'Manager' => 'bg-orange-100 text-orange-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($user->trashed())
                                            <button wire:click="restore({{ $user->id }})" class="text-green-600 hover:text-green-900 font-bold mr-3">
                                                Restaurar
                                            </button>
                                            <button wire:confirm="¿Está SEGURO de eliminar definitivamente? Esta acción NO se puede deshacer." 
                                                    wire:click="forceDelete({{ $user->id }})" 
                                                    class="text-red-600 hover:text-red-900 font-bold"
                                                    title="Eliminación permanente">
                                                Eliminar
                                            </button>
                                        @else
                                            <a href="{{ route('manager.users.edit', $user) }}" class="text-orange-600 hover:text-orange-900 mr-3 font-semibold">
                                                Editar
                                            </a>
                                            @if(!$user->hasRole('Super Admin') && $user->id !== auth()->id())
                                                <button wire:confirm="¿Revocar acceso a este usuario?"
                                                        wire:click="delete({{ $user->id }})" 
                                                        class="text-red-400 hover:text-red-600 font-medium">
                                                    Revocar
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            <span class="block text-lg font-medium">No se encontraron usuarios</span>
                                            <span class="block text-sm">Intenta ajustar los filtros de búsqueda</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
