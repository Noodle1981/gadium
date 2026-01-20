<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use OwenIt\Auditing\Models\Audit;
use App\Models\User;

new class extends Component {
    use WithPagination;

    // Filters
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedUser = '';
    public $selectedModule = '';
    public $selectedEvent = '';
    public $search = '';

    // Modal
    public $showDetailModal = false;
    public $selectedAudit = null;

    public function mount() {
        // Default: last 7 days
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatedDateFrom() {
        $this->resetPage();
    }

    public function updatedDateTo() {
        $this->resetPage();
    }

    public function updatedSelectedUser() {
        $this->resetPage();
    }

    public function updatedSelectedModule() {
        $this->resetPage();
    }

    public function updatedSelectedEvent() {
        $this->resetPage();
    }

    public function clearFilters() {
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->selectedUser = '';
        $this->selectedModule = '';
        $this->selectedEvent = '';
        $this->search = '';
        $this->resetPage();
    }

    public function viewDetails($auditId) {
        $this->selectedAudit = Audit::with('user')->find($auditId);
        $this->showDetailModal = true;
    }

    public function getModuleNameProperty() {
        $modules = [
            'App\Models\Sale' => 'Ventas',
            'App\Models\Budget' => 'Presupuestos',
            'App\Models\PurchaseDetail' => 'Compras',
            'App\Models\HourDetail' => 'Horas',
            'App\Models\BoardDetail' => 'Tableros',
            'App\Models\AutomationProject' => 'Proyectos',
            'App\Models\ClientSatisfactionResponse' => 'Sat. Clientes',
            'App\Models\StaffSatisfactionResponse' => 'Sat. Personal',
        ];
        return $modules;
    }

    public function getEventNameProperty() {
        return [
            'created' => 'Creado',
            'updated' => 'Editado',
            'deleted' => 'Eliminado',
        ];
    }

    public function with() {
        $query = Audit::with('user')
            ->whereBetween('created_at', [
                $this->dateFrom . ' 00:00:00',
                $this->dateTo . ' 23:59:59'
            ])
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($this->selectedUser) {
            $query->where('user_id', $this->selectedUser);
        }

        // Filter by module
        if ($this->selectedModule) {
            $query->where('auditable_type', $this->selectedModule);
        }

        // Filter by event
        if ($this->selectedEvent) {
            $query->where('event', $this->selectedEvent);
        }

        $audits = $query->paginate(20);

        // Get stats for last 30 days (monthly)
        $stats = [
            'imports' => Audit::where('created_at', '>=', now()->subDays(30))
                ->where('event', 'created')
                ->whereIn('auditable_type', array_keys($this->moduleName))
                ->count(),
            'edits' => Audit::where('created_at', '>=', now()->subDays(30))
                ->where('event', 'updated')
                ->count(),
            'deletes' => Audit::where('created_at', '>=', now()->subDays(30))
                ->where('event', 'deleted')
                ->count(),
        ];

        return [
            'audits' => $audits,
            'users' => User::orderBy('name')->get(),
            'stats' => $stats,
        ];
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">📋 Bitácora del Sistema</h1>
                        <p class="text-orange-100 text-sm">Registro completo de todas las operaciones realizadas</p>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Stats Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Importaciones (mensual)</p>
                            <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['imports'] }}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-xl">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Ediciones (mensual)</p>
                            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['edits'] }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-xl">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Eliminaciones (mensual)</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['deletes'] }}</p>
                        </div>
                        <div class="p-3 bg-red-50 rounded-xl">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">🔍 Filtros</h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="date" wire:model.live="dateFrom" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="date" wire:model.live="dateTo" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                        <select wire:model.live="selectedUser" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
                        <select wire:model.live="selectedModule" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            @foreach($this->moduleName as $class => $name)
                                <option value="{{ $class }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Acción</label>
                        <select wire:model.live="selectedEvent" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todas</option>
                            @foreach($this->eventName as $event => $name)
                                <option value="{{ $event }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button wire:click="clearFilters" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                        Limpiar Filtros
                    </button>
                </div>
            </div>

            {{-- Audit Table --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Registro</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Detalles</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($audits as $audit)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $audit->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $audit->user?->name ?? 'Sistema' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $this->moduleName[$audit->auditable_type] ?? 'Desconocido' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($audit->event === 'created')
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ➕ Creado
                                            </span>
                                        @elseif($audit->event === 'updated')
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                ✏️ Editado
                                            </span>
                                        @elseif($audit->event === 'deleted')
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                🗑️ Eliminado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ID: {{ $audit->auditable_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <button wire:click="viewDetails({{ $audit->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            Ver Diff →
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <p class="font-medium">No se encontraron registros</p>
                                            <p class="text-sm mt-1">Intenta ajustar los filtros</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $audits->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedAudit)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showDetailModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full">
                    <div class="bg-white px-6 pt-5 pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900">
                                Detalles de Cambio - {{ $this->moduleName[$selectedAudit->auditable_type] ?? 'Registro' }} #{{ $selectedAudit->auditable_id }}
                            </h3>
                            <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            {{-- Metadata --}}
                            <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Usuario</p>
                                    <p class="text-sm text-gray-900">{{ $selectedAudit->user?->name ?? 'Sistema' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Fecha/Hora</p>
                                    <p class="text-sm text-gray-900">{{ $selectedAudit->created_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">IP</p>
                                    <p class="text-sm text-gray-900">{{ $selectedAudit->ip_address ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Acción</p>
                                    <p class="text-sm text-gray-900">{{ $this->eventName[$selectedAudit->event] ?? $selectedAudit->event }}</p>
                                </div>
                            </div>

                            {{-- Changes --}}
                            @if($selectedAudit->event === 'updated' && $selectedAudit->old_values && $selectedAudit->new_values)
                                <div>
                                    <h4 class="text-sm font-bold text-gray-700 mb-2">Cambios Realizados:</h4>
                                    <div class="space-y-3">
                                        @foreach($selectedAudit->new_values as $key => $newValue)
                                            @if(isset($selectedAudit->old_values[$key]) && $selectedAudit->old_values[$key] != $newValue)
                                                <div class="p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                                                    <p class="text-xs font-bold text-gray-700 mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <span class="text-red-600">❌ {{ $selectedAudit->old_values[$key] ?? 'null' }}</span>
                                                        <span class="text-gray-400">→</span>
                                                        <span class="text-green-600">✅ {{ $newValue ?? 'null' }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($selectedAudit->event === 'created')
                                <div class="p-4 bg-green-50 border-l-4 border-green-400 rounded">
                                    <p class="text-sm text-green-800">✅ Registro creado exitosamente</p>
                                </div>
                            @elseif($selectedAudit->event === 'deleted')
                                <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded">
                                    <p class="text-sm text-red-800">🗑️ Registro eliminado</p>
                                    @if($selectedAudit->old_values)
                                        <p class="text-xs text-gray-600 mt-2">Valores antes de eliminar:</p>
                                        <pre class="text-xs mt-1 text-gray-700">{{ json_encode($selectedAudit->old_values, JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end">
                        <button wire:click="$set('showDetailModal', false)" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
