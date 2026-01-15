<?php
use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $user = auth()->user();
    $isAdmin = $user->hasAnyRole(['Super Admin', 'Admin']);
    $isSuperAdmin = $user->hasRole('Super Admin');
    $isManager = $user->hasRole('Manager');
    $isViewer = $user->hasRole('Viewer');
    $isVendedor = $user->hasRole('Vendedor');
    $isPresupuestador = $user->hasRole('Presupuestador');

    $dashboardRoute = 'dashboard';
    if ($isAdmin) $dashboardRoute = 'admin.dashboard';
    elseif ($isManager) $dashboardRoute = 'manager.dashboard';
    elseif ($isVendedor) $dashboardRoute = 'sales.dashboard';
    elseif ($isPresupuestador) $dashboardRoute = 'budget.dashboard';

    $profileRoute = 'admin.profile'; // Default
    if ($isManager) $profileRoute = 'manager.profile';
    elseif ($isAdmin) $profileRoute = 'admin.profile';
    elseif ($isVendedor) $profileRoute = 'sales.profile';
    elseif ($isPresupuestador) $profileRoute = 'budget.profile';

@endphp

<div x-data="{ open: false }" class="relative">
    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex flex-col w-64 h-screen bg-gray-950 border-r border-gray-800 fixed left-0 top-0 z-40 transition-all duration-300">
        <!-- Header / Logo -->
        <div class="flex items-center justify-center h-20 border-b border-gray-800 px-6">
            <a href="{{ route($dashboardRoute) }}" class="flex items-center justify-center w-full" wire:navigate>
                 <img src="{{ asset('img/logo.webp') }}" alt="Gadium" class="h-10 w-auto object-contain hover:scale-105 transition-transform duration-300">
            </a>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
            <x-sidebar-content :isAdmin="$isAdmin" :isSuperAdmin="$isSuperAdmin" :isManager="$isManager" :isViewer="$isViewer" :isVendedor="$isVendedor" :isPresupuestador="$isPresupuestador" :dashboardRoute="$dashboardRoute" />
        </div>

        <!-- Footer / User Profile -->
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center p-3 bg-gray-900/50 rounded-xl">
                <div class="w-10 h-10 rounded-lg bg-orange-600 flex items-center justify-center text-white font-bold shadow-lg">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="ml-3 overflow-hidden">
                    <p class="text-sm font-semibold text-white truncate">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $user->roles->first()?->name ?? 'Usuario' }}</p>
                </div>
            </div>
            
            <!-- Profile Link -->
            <a href="{{ route($profileRoute) }}" class="mt-4 w-full flex items-center justify-center px-4 py-2 text-xs font-semibold text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors duration-200" wire:navigate>
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Perfil
            </a>

            <!-- Logout Button -->
            <button wire:click="logout" class="mt-2 w-full flex items-center justify-center px-4 py-2 text-xs font-semibold text-gray-500 hover:text-white hover:bg-red-900/20 rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Cerrar Sesión
            </button>
        </div>
    </aside>

    <!-- Mobile Top Header -->
    <header class="lg:hidden w-full h-16 bg-gray-950 border-b border-gray-800 flex items-center justify-between px-6 fixed top-0 left-0 z-30">
        <span class="text-lg font-bold text-white uppercase tracking-wider">Gadium</span>
        <button @click="open = !open" class="text-gray-400 hover:text-white focus:outline-none">
            <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </header>

    <!-- Mobile Sidebar Drawer -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="lg:hidden fixed inset-0 z-50 overflow-hidden" 
         @click.away="open = false">
        
        <div class="w-2/3 max-w-sm h-full bg-gray-950 border-r border-gray-800 shadow-2xl flex flex-col">
            <!-- Reuse sidebar content for mobile -->
            <div class="h-16 flex items-center px-6 border-b border-gray-800">
                <span class="text-lg font-bold text-white uppercase tracking-wider">Menú</span>
            </div>
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
                <x-sidebar-content :isAdmin="$isAdmin" :isSuperAdmin="$isSuperAdmin" :isManager="$isManager" :isViewer="$isViewer" :isVendedor="$isVendedor" :isPresupuestador="$isPresupuestador" :dashboardRoute="$dashboardRoute" />
                
                <hr class="border-gray-800 my-4">
                
                <!-- Logout Button Mobile -->
                <button wire:click="logout" class="w-full flex items-center px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all duration-200">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Cerrar Sesión
                </button>
            </div>
        </div>
        
        <!-- Backdrop -->
        <div x-show="open" @click="open = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm -z-10"></div>
    </div>
</div>
