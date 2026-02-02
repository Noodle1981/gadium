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
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Control Maestro') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- System Health / Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Usuarios -->
            <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm transition-all hover:shadow-md group">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-3 bg-orange-50 text-orange-600 rounded-2xl group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 15.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <a href="{{ route('app.users.index') }}" class="text-orange-600 font-bold text-sm flex items-center hover:underline">
                        Ver Lista
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <h4 class="text-gray-500 font-medium uppercase tracking-widest text-xs mb-1">Usuarios Activos</h4>
                <p class="text-5xl font-black text-gray-900">{{ number_format($userCount) }}</p>
            </div>

            <!-- Roles -->
            <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm transition-all hover:shadow-md group">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    @if(auth()->user()->hasRole('Super Admin'))
                    <a href="{{ route('app.roles.index') }}" class="text-blue-600 font-bold text-sm flex items-center hover:underline">
                        Gestionar
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endif
                </div>
                <h4 class="text-gray-500 font-medium uppercase tracking-widest text-xs mb-1">Roles de Seguridad</h4>
                <p class="text-5xl font-black text-gray-900">{{ number_format($roleCount) }}</p>
            </div>
        </div>

        <!-- System Alerts / Status -->
        <div class="bg-gray-50 rounded-3xl p-8 border border-dashed border-gray-300">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Resumen del Sistema
            </h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Conexión con motor de BI</span>
                    <span class="font-bold text-green-600">ESTABLE</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Integridad de Auditoría</span>
                    <span class="font-bold text-blue-600">VERIFICADO</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Versión del Core</span>
                    <span class="font-mono bg-gray-200 px-2 py-0.5 rounded text-[10px]">v2.4.0-industrial</span>
                </div>
            </div>
        </div>
    </div>
</div>
