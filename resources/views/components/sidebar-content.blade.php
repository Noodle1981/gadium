@props(['isAdmin', 'isSuperAdmin', 'isManager', 'isViewer', 'isVendedor', 'isPresupuestador', 'dashboardRoute'])

<!-- Principal -->
<div class="text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2">Principal</div>

@if(!$isVendedor && !$isPresupuestador)
    <x-sidebar-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'>
        Dashboard
    </x-sidebar-link>
@endif

@if(auth()->user()->can('view_sales') || auth()->user()->can('view_production') || auth()->user()->can('view_hr'))
    <div class="pt-4 text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2">Operaciones</div>
    
    @can('view_sales')
        @if(!$isViewer && !$isManager && !$isVendedor && !$isPresupuestador)
            @php
                $salesRoute = 'admin.sales.import';
            @endphp
            <x-sidebar-link :href="route($salesRoute)" :active="request()->routeIs($salesRoute)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
                Importación
            </x-sidebar-link>
        @endif
    @endcan

    @can('view_production')
        @if(!$isViewer && !$isManager && !$isVendedor && !$isPresupuestador)
            @php
                $prodRoute = $isManager ? 'manager.manufacturing.production.log' : 'admin.manufacturing.production.log';
            @endphp
            <x-sidebar-link :href="route($prodRoute)" :active="request()->routeIs($prodRoute)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'>
                Producción
            </x-sidebar-link>
        @endif
    @endcan
    
    @can('view_sales')
        @if(!$isViewer && !$isManager && !$isVendedor && !$isPresupuestador)
            @php
                $clientsRoute = 'admin.clients.resolve';
            @endphp
            <x-sidebar-link :href="route($clientsRoute)" :active="request()->routeIs($clientsRoute)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'>
                Resolución Clientes
            </x-sidebar-link>
        @endif
        
        @if(!$isViewer && !$isVendedor && !$isPresupuestador)
            @php
                $ventasRoute = $isManager ? 'manager.historial.ventas' : 'admin.historial.ventas';
                $presupuestoRoute = $isManager ? 'manager.historial.presupuesto' : 'admin.historial.presupuesto';
            @endphp
            
            <x-sidebar-link :href="route($ventasRoute)" :active="request()->routeIs($ventasRoute)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
                📊 Historial Ventas
            </x-sidebar-link>
            
            <x-sidebar-link :href="route($presupuestoRoute)" :active="request()->routeIs($presupuestoRoute)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
                📑 Historial Presupuestos
            </x-sidebar-link>
        @endif
    @endcan
@endif



@if(auth()->user()->can('view_users') || auth()->user()->can('view_roles') || auth()->user()->can('view_hr'))
    <div class="pt-4 text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2">Configuración</div>
    
    @can('view_users')
        @php
            $usersRoute = $isManager ? 'manager.users.index' : 'users.index';
            $usersPattern = $isManager ? 'manager.users.*' : 'users.*';
        @endphp
        <x-sidebar-link :href="route($usersRoute)" :active="request()->routeIs($usersPattern)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 15.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'>
            Usuarios
        </x-sidebar-link>
    @endcan

    @can('view_roles')
        @php
            $rolesRoute = $isManager ? 'manager.roles.index' : 'roles.index';
            $rolesPattern = $isManager ? 'manager.roles.*' : 'roles.*';
        @endphp
        <x-sidebar-link :href="route($rolesRoute)" :active="request()->routeIs($rolesPattern)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'>
            Roles
        </x-sidebar-link>
    @endcan

    @can('view_hr')
        @if(!$isViewer && !$isManager && !$isVendedor && !$isPresupuestador)
            @php
                $hrRoute = $isManager ? 'manager.hr.factors' : 'admin.hr.factors';
            @endphp
            <x-sidebar-link :href="route($hrRoute)" :active="request()->routeIs($hrRoute)" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>'>
                Factores
            </x-sidebar-link>
        @endif
    @endcan
@endif

{{-- Sidebar para Vendedor --}}
@if(auth()->user()->hasRole('Vendedor'))
    <x-sidebar-link :href="route('sales.dashboard')" :active="request()->routeIs('sales.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'>
        Dashboard
    </x-sidebar-link>

    @can('view_sales')
        <x-sidebar-link :href="route('sales.import')" :active="request()->routeIs('sales.import')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>'>
            Importación
        </x-sidebar-link>

        <x-sidebar-link :href="route('sales.clients.resolve')" :active="request()->routeIs('sales.clients.resolve')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'>
            Resolución Clientes
        </x-sidebar-link>

        <x-sidebar-link :href="route('sales.historial.ventas')" :active="request()->routeIs('sales.historial.ventas')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
            Historial Ventas
        </x-sidebar-link>
    @endcan
@endif

{{-- Sidebar para Presupuestador --}}
@if(auth()->user()->hasRole('Presupuestador'))
    <x-sidebar-link :href="route('budget.dashboard')" :active="request()->routeIs('budget.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 36v-3m-6 6v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>'>
        Dashboard
    </x-sidebar-link>

    @can('view_budgets')
        <x-sidebar-link :href="route('budget.import')" :active="request()->routeIs('budget.import')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>'>
            Importación
        </x-sidebar-link>

        <x-sidebar-link :href="route('budget.historial.importacion')" :active="request()->routeIs('budget.historial.importacion')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
            Historial Importación
        </x-sidebar-link>
    @endcan
@endif
