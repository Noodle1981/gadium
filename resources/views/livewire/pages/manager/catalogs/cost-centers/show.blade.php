<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\CostCenter;
use App\Models\Budget;
use App\Models\PurchaseDetail;

new class extends Component {
    use WithPagination;

    public $costCenterId = null;
    public $costCenter = null;
    public $activeTab = 'budgets';
    public $searchBudgets = '';
    public $searchPurchases = '';

    // Summary Stats
    public $totalBudgets = 0;
    public $totalBudgetAmount = 0;
    public $totalPurchases = 0;
    public $totalPurchaseAmount = 0;

    public function mount($id = null)
    {
        if ($id) {
            $this->costCenterId = $id;
            $this->costCenter = CostCenter::find($id);
            $this->loadStats();
        }
    }

    public function loadStats()
    {
        if (!$this->costCenter) return;

        $ccName = $this->costCenter->name;

        // Budget stats (using centro_costo string field)
        $budgetsQuery = Budget::where('centro_costo', $ccName)
            ->orWhere('cost_center_id', $this->costCenterId);
        $this->totalBudgets = $budgetsQuery->count();
        $this->totalBudgetAmount = (clone $budgetsQuery)->sum('monto');

        // Purchase stats (using cc string field)
        $purchasesQuery = PurchaseDetail::where('cc', $ccName)
            ->orWhere('cost_center_id', $this->costCenterId);
        $this->totalPurchases = $purchasesQuery->count();
        $this->totalPurchaseAmount = (clone $purchasesQuery)->sum('materiales_presupuestados');
    }

    public function updatedSearchBudgets()
    {
        $this->resetPage('budgetsPage');
    }

    public function updatedSearchPurchases()
    {
        $this->resetPage('purchasesPage');
    }

    public function with()
    {
        $budgets = collect();
        $purchases = collect();

        if ($this->costCenter) {
            $ccName = $this->costCenter->name;

            // Get budgets
            $budgetsQuery = Budget::where(function($q) use ($ccName) {
                $q->where('centro_costo', $ccName)
                  ->orWhere('cost_center_id', $this->costCenterId);
            });

            if ($this->searchBudgets) {
                $budgetsQuery->where(function($q) {
                    $q->where('cliente_nombre', 'like', '%' . $this->searchBudgets . '%')
                      ->orWhere('nombre_proyecto', 'like', '%' . $this->searchBudgets . '%')
                      ->orWhere('comprobante', 'like', '%' . $this->searchBudgets . '%');
                });
            }

            $budgets = $budgetsQuery->orderByDesc('fecha')->paginate(10, ['*'], 'budgetsPage');

            // Get purchases
            $purchasesQuery = PurchaseDetail::where(function($q) use ($ccName) {
                $q->where('cc', $ccName)
                  ->orWhere('cost_center_id', $this->costCenterId);
            });

            if ($this->searchPurchases) {
                $purchasesQuery->where(function($q) {
                    $q->where('empresa', 'like', '%' . $this->searchPurchases . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->searchPurchases . '%');
                });
            }

            $purchases = $purchasesQuery->orderByDesc('ano')->paginate(10, ['*'], 'purchasesPage');
        }

        return [
            'budgets' => $budgets,
            'purchases' => $purchases,
        ];
    }
}; ?>

<div class="min-h-screen bg-gray-100 pb-12">
    <!-- Banner -->
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-800 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('app.catalogs.cost-centers') }}" class="text-emerald-200 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <span class="text-emerald-200 text-sm">Centro de Costo</span>
                    </div>
                    @if($costCenter)
                        <h2 class="text-3xl font-extrabold text-white tracking-tight">{{ $costCenter->name }}</h2>
                        <p class="mt-1 text-emerald-100 text-sm">Código: {{ $costCenter->code }}</p>
                        @if($costCenter->description)
                            <p class="mt-1 text-emerald-200 text-sm">{{ $costCenter->description }}</p>
                        @endif
                    @else
                        <h2 class="text-3xl font-extrabold text-white tracking-tight">Centro de Costo no encontrado</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($costCenter)
        <!-- Stats Cards -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-blue-500">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Presupuestos</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalBudgets) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-green-500">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Monto Presupuestado</p>
                    <p class="text-2xl font-bold text-gray-800">$ {{ number_format($totalBudgetAmount, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-orange-500">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Compras</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalPurchases) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 border-purple-500">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Mat. Presupuestados</p>
                    <p class="text-2xl font-bold text-gray-800">$ {{ number_format($totalPurchaseAmount, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <!-- Tab Navigation -->
                <div class="flex border-b border-gray-200">
                    <button wire:click="$set('activeTab', 'budgets')"
                            class="flex-1 px-6 py-4 text-sm font-medium transition-colors {{ $activeTab === 'budgets' ? 'text-emerald-600 border-b-2 border-emerald-600 bg-emerald-50' : 'text-gray-500 hover:text-gray-700' }}">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Presupuestos ({{ $totalBudgets }})
                    </button>
                    <button wire:click="$set('activeTab', 'purchases')"
                            class="flex-1 px-6 py-4 text-sm font-medium transition-colors {{ $activeTab === 'purchases' ? 'text-emerald-600 border-b-2 border-emerald-600 bg-emerald-50' : 'text-gray-500 hover:text-gray-700' }}">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Compras ({{ $totalPurchases }})
                    </button>
                </div>

                <!-- Budgets Tab -->
                <div x-show="$wire.activeTab === 'budgets'" class="p-6">
                    <!-- Search -->
                    <div class="mb-4">
                        <input wire:model.live.debounce.300ms="searchBudgets" type="text"
                            class="block w-full pl-4 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm"
                            placeholder="Buscar por cliente, proyecto o comprobante...">
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Proyecto</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Comprobante</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Monto</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Moneda</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($budgets as $budget)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                            {{ $budget->fecha ? \Carbon\Carbon::parse($budget->fecha)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $budget->cliente_nombre ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $budget->nombre_proyecto ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $budget->comprobante ?? '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-gray-800">
                                            {{ number_format($budget->monto, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $budget->moneda === 'USD' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ $budget->moneda ?? 'ARS' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $budget->estado === 'completado' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $budget->estado ?? 'pendiente' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            No hay presupuestos asociados a este centro de costo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($budgets->hasPages())
                        <div class="mt-4 border-t border-gray-200 pt-4">
                            {{ $budgets->links() }}
                        </div>
                    @endif
                </div>

                <!-- Purchases Tab -->
                <div x-show="$wire.activeTab === 'purchases'" class="p-6">
                    <!-- Search -->
                    <div class="mb-4">
                        <input wire:model.live.debounce.300ms="searchPurchases" type="text"
                            class="block w-full pl-4 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm"
                            placeholder="Buscar por empresa o descripcion...">
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Anio</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Empresa</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Descripcion</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Mat. Presupuestados</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Mat. Comprados</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Resto</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Moneda</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($purchases as $purchase)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $purchase->ano ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $purchase->empresa ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($purchase->descripcion, 50) ?? '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-gray-800">
                                            {{ number_format($purchase->materiales_presupuestados, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                                            {{ number_format($purchase->materiales_comprados, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right {{ ($purchase->resto_valor ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($purchase->resto_valor, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $purchase->moneda === 'USD' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ $purchase->moneda ?? 'ARS' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            No hay compras asociadas a este centro de costo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($purchases->hasPages())
                        <div class="mt-4 border-t border-gray-200 pt-4">
                            {{ $purchases->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M12 12h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-600 mb-4">El centro de costo solicitado no existe.</p>
                <a href="{{ route('app.catalogs.cost-centers') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver al listado
                </a>
            </div>
        </div>
    @endif
</div>
