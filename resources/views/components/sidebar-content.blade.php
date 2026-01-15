@props(['isAdmin', 'isSuperAdmin', 'isManager', 'isViewer', 'isVendedor', 'isPresupuestador', 'isHours', 'isPurchases', 'isStaffSat', 'isClientSat', 'isBoards', 'isAutomation', 'dashboardRoute'])

<!-- Principal -->
<div class="text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2">Principal</div>

@if(!$isVendedor && !$isPresupuestador && !$isHours && !$isPurchases && !$isStaffSat && !$isClientSat && !$isBoards && !$isAutomation)
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
            Importación Automática
        </x-sidebar-link>
        <x-sidebar-link :href="route('sales.create')" :active="request()->routeIs('sales.create')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
            Importación Manual
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
    <x-sidebar-link :href="route('budget.dashboard')" :active="request()->routeIs('budget.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'>
        Dashboard
    </x-sidebar-link>

    @can('view_budgets')
        <x-sidebar-link :href="route('budget.import')" :active="request()->routeIs('budget.import')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>'>
            Importación Automática
        </x-sidebar-link>
        <x-sidebar-link :href="route('budget.create')" :active="request()->routeIs('budget.create')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'>
            Importación Manual
        </x-sidebar-link>
        <x-sidebar-link :href="route('budget.historial.importacion')" :active="request()->routeIs('budget.historial.importacion')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
            Historial Importación
        </x-sidebar-link>
    @endcan
@endif

{{-- Sidebar para Gestor de Horas --}}
@if(auth()->user()->hasRole('Gestor de Horas'))
    <x-sidebar-link :href="route('hours.dashboard')" :active="request()->routeIs('hours.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'>
        Dashboard
    </x-sidebar-link>
@endif

{{-- Sidebar para Gestor de Compras --}}
@if(auth()->user()->hasRole('Gestor de Compras'))
    <x-sidebar-link :href="route('purchases.dashboard')" :active="request()->routeIs('purchases.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>'>
        Dashboard
    </x-sidebar-link>
@endif

{{-- Sidebar para Safisfacción Personal --}}
@if(auth()->user()->hasRole('Gestor de Satisfacción Personal'))
    <x-sidebar-link :href="route('staff-satisfaction.dashboard')" :active="request()->routeIs('staff-satisfaction.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'>
        Dashboard
    </x-sidebar-link>
@endif

{{-- Sidebar para Safisfacción Clientes --}}
@if(auth()->user()->hasRole('Gestor de Satisfacción Clientes'))
    <x-sidebar-link :href="route('client-satisfaction.dashboard')" :active="request()->routeIs('client-satisfaction.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>'>
        Dashboard
    </x-sidebar-link>
@endif

{{-- Sidebar para Tableros --}}
@if(auth()->user()->hasRole('Gestor de Tableros'))
    <x-sidebar-link :href="route('boards.dashboard')" :active="request()->routeIs('boards.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'>
        Dashboard
    </x-sidebar-link>
@endif

{{-- Sidebar para Proyectos --}}
@if(auth()->user()->hasRole('Gestor de Proyectos'))
    <x-sidebar-link :href="route('automation.dashboard')" :active="request()->routeIs('automation.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>'>
        Dashboard
    </x-sidebar-link>
@endif
