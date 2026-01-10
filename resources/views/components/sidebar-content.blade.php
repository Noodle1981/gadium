@props(['isAdmin', 'isSuperAdmin', 'isManager', 'isViewer', 'dashboardRoute'])

<!-- Principal -->
<div class="text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2">Principal</div>

<x-sidebar-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'>
    Dashboard
</x-sidebar-link>

@if($isAdmin || $isManager)
    <div class="pt-4 text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2">Operaciones</div>
    
    <x-sidebar-link :href="route($isAdmin ? 'admin.sales.import' : 'manager.sales.import')" :active="request()->routeIs('*.sales.import')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
        Importación
    </x-sidebar-link>

    <x-sidebar-link :href="route($isAdmin ? 'admin.manufacturing.production.log' : 'manager.manufacturing.production.log')" :active="request()->routeIs('*.production.log')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'>
        Producción
    </x-sidebar-link>
@endif

<div class="pt-4 text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2">Inteligencia</div>

<x-sidebar-link :href="route('viewer.dashboard')" :active="request()->routeIs('viewer.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'>
    Grafana
</x-sidebar-link>

@if($isAdmin)
    <div class="pt-4 text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2">Configuración</div>
    
    <x-sidebar-link :href="route('users.index')" :active="request()->routeIs('users.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 15.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'>
        Usuarios
    </x-sidebar-link>

    @if($isSuperAdmin)
        <x-sidebar-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'>
            Roles
        </x-sidebar-link>
    @endif

    <x-sidebar-link :href="route('admin.hr.factors')" :active="request()->routeIs('admin.hr.factors')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>'>
        Factores
    </x-sidebar-link>
@endif
