{{--
    Sidebar Content - Permission-Based Navigation
    No role flags needed - all visibility is controlled by @can() directives
--}}

{{-- ========================================
     DASHBOARD SECTION
     ======================================== --}}
@can('view_dashboards')
    <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2 whitespace-nowrap overflow-hidden transition-all duration-300"
         :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0' : 'opacity-100 h-auto'">
        Principal
    </div>

    <x-sidebar-link :href="route('app.dashboard')" :active="request()->routeIs('app.dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'>
        Dashboard
    </x-sidebar-link>

    <x-sidebar-link :href="route('app.intelligence')" :active="request()->routeIs('app.intelligence')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>'>
        Inteligencia
    </x-sidebar-link>
@endcan

{{-- ========================================
     OPERATIONS SECTION
     ======================================== --}}
@if(auth()->user()->can('view_sales') || auth()->user()->can('view_budgets'))
    <div class="pt-4 text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2 whitespace-nowrap overflow-hidden transition-all duration-300"
         :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0' : 'opacity-100 h-auto'">
        Operaciones
    </div>
@endif

@can('view_sales')
    <x-sidebar-link :href="route('app.sales.history')" :active="request()->routeIs('app.sales.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
        Ventas
    </x-sidebar-link>
@endcan

@can('view_budgets')
    <x-sidebar-link :href="route('app.budgets.history')" :active="request()->routeIs('app.budgets.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
        Presupuestos
    </x-sidebar-link>
@endcan

{{-- ========================================
     DATA ENTRY SECTION
     ======================================== --}}
@if(auth()->user()->can('view_hours') || auth()->user()->can('view_purchases') || auth()->user()->can('view_boards') || auth()->user()->can('view_automation'))
    <div class="pt-4 text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2 whitespace-nowrap overflow-hidden transition-all duration-300"
         :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0' : 'opacity-100 h-auto'">
        Datos
    </div>
@endif

@can('view_hours')
    <x-sidebar-link :href="route('app.hours.index')" :active="request()->routeIs('app.hours.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'>
        Horas
    </x-sidebar-link>
@endcan

@can('view_purchases')
    <x-sidebar-link :href="route('app.purchases.index')" :active="request()->routeIs('app.purchases.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>'>
        Compras
    </x-sidebar-link>
@endcan

@can('view_boards')
    <x-sidebar-link :href="route('app.boards.index')" :active="request()->routeIs('app.boards.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'>
        Tableros
    </x-sidebar-link>
@endcan

@can('view_automation')
    <x-sidebar-link :href="route('app.automation.index')" :active="request()->routeIs('app.automation.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'>
        Automatizacion
    </x-sidebar-link>
@endcan

{{-- ========================================
     SATISFACTION SECTION
     ======================================== --}}
@if(auth()->user()->can('view_staff_satisfaction') || auth()->user()->can('view_client_satisfaction'))
    <div class="pt-4 text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2 whitespace-nowrap overflow-hidden transition-all duration-300"
         :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0' : 'opacity-100 h-auto'">
        Satisfaccion
    </div>
@endif

@can('view_staff_satisfaction')
    <x-sidebar-link :href="route('app.staff-satisfaction.surveys')" :active="request()->routeIs('app.staff-satisfaction.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'>
        Personal
    </x-sidebar-link>
@endcan

@can('view_client_satisfaction')
    <x-sidebar-link :href="route('app.client-satisfaction.index')" :active="request()->routeIs('app.client-satisfaction.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'>
        Clientes
    </x-sidebar-link>
@endcan

{{-- ========================================
     CONFIGURATION SECTION
     ======================================== --}}
<div class="pt-4 text-xs font-semibold text-gray-500 uppercase tracking-widest px-4 mb-2 whitespace-nowrap overflow-hidden transition-all duration-300"
     :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0' : 'opacity-100 h-auto'">
    Configuracion
</div>

<x-sidebar-link :href="route('app.profile')" :active="request()->routeIs('app.profile')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'>
        Mi Cuenta
    </x-sidebar-link>

@can('view_users')
    <x-sidebar-link :href="route('app.users.index')" :active="request()->routeIs('app.users.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 15.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'>
        Usuarios
    </x-sidebar-link>
@endcan

@can('view_roles')
    <x-sidebar-link :href="route('app.roles.index')" :active="request()->routeIs('app.roles.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'>
        Roles
    </x-sidebar-link>
@endcan

@can('view_hr')
    <x-sidebar-link :href="route('app.hr.factors')" :active="request()->routeIs('app.hr.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>'>
        Factores HR
    </x-sidebar-link>
@endcan

@can('view_dashboards')
    <x-sidebar-link :href="route('app.audit')" :active="request()->routeIs('app.audit')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
        Bitacora
    </x-sidebar-link>

    <x-sidebar-link :href="route('app.catalogs.cost-centers')" :active="request()->routeIs('app.catalogs.*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>'>
        Centros de Costo
    </x-sidebar-link>
@endcan
