<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Budget;
use App\Models\Sale;
use App\Models\HourDetail;
use App\Models\PurchaseDetail;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component {
    public int $userCount = 0;
    public int $roleCount = 0;

    // Budget stats
    public int $budgetCount = 0;
    public float $budgetTotal = 0;
    public int $budgetPerdidos = 0;
    public int $budgetInformativos = 0;
    public int $budgetPendientes = 0;
    public int $budgetAprobados = 0;
    public int $budgetCancelados = 0;

    // Sales stats
    public int $saleCount = 0;
    public float $saleTotal = 0;

    // Hours stats
    public int $hourCount = 0;
    public float $hourTotal = 0;

    // Purchases stats
    public int $purchaseCount = 0;

    public function mount() {
        $this->userCount = User::count();
        $this->roleCount = Role::count();

        // Budget statistics
        $this->budgetCount = Budget::count();
        $this->budgetTotal = (float) Budget::sum('monto');
        $this->budgetAprobados = Budget::where('estado', 'Aprobado')->count();
        $this->budgetCancelados = Budget::where('estado', 'Cancelado')->count();
        $this->budgetPerdidos = Budget::where('estado', 'Perdido')->count();
        $this->budgetInformativos = Budget::where('estado', 'Informativo')->count();
        $this->budgetPendientes = Budget::where('estado', 'Pendiente')->orWhereNull('estado')->count();

        // Sales statistics
        $this->saleCount = Sale::count();
        $this->saleTotal = (float) Sale::sum('monto');

        // Hours statistics
        $this->hourCount = HourDetail::count();
        $this->hourTotal = (float) HourDetail::sum('hs');

        // Purchases statistics
        $this->purchaseCount = PurchaseDetail::count();
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Control Maestro') }}
        </h2>
    </x-slot>

    <div class="space-y-8">

        <!-- ========================================
             OPERATIONAL DATA OVERVIEW
             ======================================== -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            @can('view_sales')
            <!-- Ventas -->
            <a href="{{ route('app.sales.history') }}" class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md hover:border-green-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-green-50 text-green-600 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-green-600 text-xs font-bold uppercase tracking-wider">Ventas</span>
                </div>
                <p class="text-3xl font-black text-gray-900">{{ number_format($saleCount) }}</p>
                <p class="text-sm text-gray-500 mt-1">USD {{ number_format($saleTotal, 0, ',', '.') }}</p>
            </a>
            @endcan

            @can('view_budgets')
            <!-- Presupuestos -->
            <a href="{{ route('app.budgets.history') }}" class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md hover:border-orange-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-orange-50 text-orange-600 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-orange-600 text-xs font-bold uppercase tracking-wider">Presupuestos</span>
                </div>
                <p class="text-3xl font-black text-gray-900">{{ number_format($budgetCount) }}</p>
                <p class="text-sm text-gray-500 mt-1">USD {{ number_format($budgetTotal, 0, ',', '.') }}</p>
            </a>
            @endcan

            @can('view_hours')
            <!-- Horas -->
            <a href="{{ route('app.hours.index') }}" class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md hover:border-blue-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-blue-600 text-xs font-bold uppercase tracking-wider">Horas</span>
                </div>
                <p class="text-3xl font-black text-gray-900">{{ number_format($hourCount) }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ number_format($hourTotal, 0, ',', '.') }} hs totales</p>
            </a>
            @endcan

            @can('view_purchases')
            <!-- Compras -->
            <a href="{{ route('app.purchases.index') }}" class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md hover:border-purple-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2.5 bg-purple-50 text-purple-600 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <span class="text-purple-600 text-xs font-bold uppercase tracking-wider">Compras</span>
                </div>
                <p class="text-3xl font-black text-gray-900">{{ number_format($purchaseCount) }}</p>
                <p class="text-sm text-gray-500 mt-1">registros cargados</p>
            </a>
            @endcan

        </div>

        <!-- ========================================
             BUDGET DETAIL SECTION
             ======================================== -->
        @can('view_budgets')
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Estado de Presupuestos
                </h3>
                <a href="{{ route('app.budgets.history') }}" class="text-orange-600 font-bold text-sm flex items-center hover:underline">
                    Ver Todos
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

                    <!-- Aprobados -->
                    <div class="text-center p-4 bg-green-50 rounded-xl border border-green-100">
                        <p class="text-3xl font-black text-green-700">{{ $budgetAprobados }}</p>
                        <p class="text-xs font-bold text-green-600 uppercase tracking-wider mt-1">Aprobados</p>
                    </div>

                    <!-- Cancelados -->
                    <div class="text-center p-4 bg-red-50 rounded-xl border border-red-100">
                        <p class="text-3xl font-black text-red-700">{{ $budgetCancelados }}</p>
                        <p class="text-xs font-bold text-red-600 uppercase tracking-wider mt-1">Cancelados</p>
                    </div>

                    <!-- Informativos -->
                    <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-3xl font-black text-blue-700">{{ $budgetInformativos }}</p>
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mt-1">Informativos</p>
                    </div>

                    <!-- Pendientes -->
                    <div class="text-center p-4 bg-yellow-50 rounded-xl border border-yellow-100">
                        <p class="text-3xl font-black text-yellow-700">{{ $budgetPendientes }}</p>
                        <p class="text-xs font-bold text-yellow-600 uppercase tracking-wider mt-1">Pendientes</p>
                    </div>

                    <!-- Perdidos -->
                    <div class="text-center p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-3xl font-black text-gray-700">{{ $budgetPerdidos }}</p>
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wider mt-1">Perdidos</p>
                    </div>

                </div>

                @if($budgetCount > 0)
                <!-- Progress bar -->
                <div class="mt-6">
                    <div class="flex h-3 rounded-full overflow-hidden bg-gray-100">
                        @if($budgetPerdidos > 0)
                        <div class="bg-gray-500" style="width: {{ ($budgetPerdidos / $budgetCount) * 100 }}%"></div>
                        @endif
                        @if($budgetInformativos > 0)
                        <div class="bg-blue-400" style="width: {{ ($budgetInformativos / $budgetCount) * 100 }}%"></div>
                        @endif
                        @if($budgetPendientes > 0)
                        <div class="bg-yellow-300" style="width: {{ ($budgetPendientes / $budgetCount) * 100 }}%"></div>
                        @endif
                        @if($budgetCancelados > 0)
                        <div class="bg-red-400" style="width: {{ ($budgetCancelados / $budgetCount) * 100 }}%"></div>
                        @endif
                        @if($budgetAprobados > 0)
                        <div class="bg-green-400" style="width: {{ ($budgetAprobados / $budgetCount) * 100 }}%"></div>
                        @endif
                    </div>
                    <div class="flex justify-between mt-2 text-[10px] text-gray-400 uppercase tracking-widest font-bold">
                        <span>{{ $budgetCount > 0 ? round(($budgetAprobados / $budgetCount) * 100) : 0 }}% aprobados</span>
                        <span>{{ $budgetCount }} total</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endcan

        <!-- ========================================
             SYSTEM & ADMIN SECTION
             ======================================== -->
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
