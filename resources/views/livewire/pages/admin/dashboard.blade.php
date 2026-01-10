<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component {
    public int $userCount = 0;
    public int $roleCount = 0;

    public function mount() {
        $this->userCount = User::count();
        $this->roleCount = Role::count();
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-orange-50 border-l-4 border-orange-500 rounded">
                        <p class="text-sm text-gray-600 uppercase font-bold">Total Usuarios</p>
                        <p class="text-3xl font-bold">{{ $userCount }}</p>
                    </div>
                    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                        <p class="text-sm text-gray-600 uppercase font-bold">Roles Definidos</p>
                        <p class="text-3xl font-bold">{{ $roleCount }}</p>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones Rápidas</h3>
                    <div class="flex space-x-4">
                        <a href="{{ route('users.index') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded transition">
                            Gestionar Usuarios
                        </a>
                        <a href="{{ route('roles.index') }}" class="bg-gray-800 hover:bg-black text-white font-bold py-2 px-4 rounded transition">
                            Gestionar Roles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
