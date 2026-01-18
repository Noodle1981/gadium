<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\CostCenter;
use App\Models\Budget;
use Illuminate\Validation\Rule;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $isEditing = false;
    public $modalTitle = '';

    // Form Fields
    public $editingId = null;
    public $code = '';
    public $name = '';
    public $description = '';
    
    // Autocomplete State
    public $costCenterSuggestions = [];
    public $showCostCenterSuggestions = false;
    
    // Tabs
    public $activeTab = 'list'; // list, resolution
    
    // Resolution Logic
    public $unresolvedCostCenters = [];
    
    public function mount() {
        $this->loadUnresolvedCostCenters();
    }
    
    public function loadUnresolvedCostCenters() {
        // Find distinct cost center names in Budget
        $existingNames = CostCenter::pluck('name')->map(fn($n) => strtoupper(trim($n)))->toArray();
        
        $this->unresolvedCostCenters = Budget::query()
            ->select('centro_costo')
            ->distinct()
            ->whereNotNull('centro_costo')
            ->get()
            ->map(function($item) {
                return [
                    'name' => trim($item->centro_costo),
                    'count' => Budget::where('centro_costo', $item->centro_costo)->count()
                ];
            })
            ->filter(fn($item) => !in_array(strtoupper($item['name']), $existingNames))
            ->sortByDesc('count')
            ->values()
            ->toArray();
    }
    
    public function resolveCostCenter($name) {
        // Open create modal pre-filled with this name
        $this->resetForm();
        $this->name = $name;
        $this->isEditing = false;
        $this->modalTitle = 'Registrar Centro de Costo Detectado';
        $this->showModal = true;
    }

    public function updatedSearch() {
        $this->resetPage();
    }
    
    public function updatedName($value) {
        if (strlen($value) >= 2) {
            $this->costCenterSuggestions = CostCenter::where('name', 'like', '%' . $value . '%')
                ->take(5)
                ->get();
            $this->showCostCenterSuggestions = count($this->costCenterSuggestions) > 0;
        } else {
            $this->costCenterSuggestions = [];
            $this->showCostCenterSuggestions = false;
        }
    }
    
    public function searchSimilarCostCenters() {
        // Manually trigger search for similar cost centers
        if (strlen($this->name) >= 2) {
            // Extract words from search term
            $searchWords = preg_split('/\s+/', strtoupper(trim($this->name)));
            
            // Build query to find cost centers containing any of the words
            $query = CostCenter::query();
            foreach ($searchWords as $word) {
                if (strlen($word) >= 2) {
                    $query->orWhere('name', 'like', '%' . $word . '%');
                }
            }
            
            $this->costCenterSuggestions = $query->take(10)->get();
            
            // Always show feedback, even if empty
            $this->showCostCenterSuggestions = true;
        }
    }
    
    public function selectExistingCostCenter($id, $name) {
        // User confirmed this detected name should be unified with existing cost center
        // Normalize ALL source records to use the canonical cost center name
        
        $detectedName = $this->name; // e.g., "prod"
        $canonicalName = $name; // e.g., "Producción"
        $canonicalCostCenterId = $id; // ID of the cost center we're keeping
        
        // First, check if there are duplicate cost centers with the canonical name
        $duplicates = CostCenter::whereRaw('UPPER(name) = ?', [strtoupper($canonicalName)])
            ->where('id', '!=', $canonicalCostCenterId)
            ->get();
        
        // Delete duplicates (keep only the one with $canonicalCostCenterId)
        foreach ($duplicates as $duplicate) {
            $duplicate->delete();
        }
        
        // Update Budget records - normalize centro_costo field
        $budgetUpdated = Budget::where('centro_costo', $detectedName)
            ->update(['centro_costo' => $canonicalName]);
        
        $totalUpdated = $budgetUpdated;
        $duplicatesDeleted = $duplicates->count();
        
        $this->showModal = false;
        $this->resetForm();
        $this->loadUnresolvedCostCenters();
        
        $message = "✓ Normalización completa: {$totalUpdated} registro(s) actualizados de '{$detectedName}' → '{$canonicalName}'";
        if ($duplicatesDeleted > 0) {
            $message .= " | {$duplicatesDeleted} duplicado(s) eliminado(s)";
        }
        
        session()->flash('message', $message);
    }

    public function create() {
        $this->resetForm();
        $this->modalTitle = 'Nuevo Centro de Costo';
        $this->showModal = true;
    }

    public function edit($id) {
        $cc = CostCenter::findOrFail($id);
        $this->editingId = $cc->id;
        $this->code = $cc->code;
        $this->name = $cc->name;
        $this->description = $cc->description;
        
        $this->isEditing = true;
        $this->modalTitle = 'Editar Centro de Costo';
        $this->showModal = true;
    }

    public function save() {
        $rules = [
            'code' => ['required', 'string', 'max:50', Rule::unique('cost_centers', 'code')->ignore($this->editingId)],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ];
        
        // Add unique validation for name (case-insensitive)
        if ($this->isEditing) {
            $rules['name'][] = function ($attribute, $value, $fail) {
                $exists = CostCenter::where('id', '!=', $this->editingId)
                    ->whereRaw('UPPER(name) = ?', [strtoupper($value)])
                    ->exists();
                if ($exists) {
                    $fail('Ya existe un centro de costo con este nombre (sin distinguir mayúsculas/minúsculas).');
                }
            };
        } else {
            $rules['name'][] = function ($attribute, $value, $fail) {
                $exists = CostCenter::whereRaw('UPPER(name) = ?', [strtoupper($value)])->exists();
                if ($exists) {
                    $fail('Ya existe un centro de costo con este nombre. Usa "Buscar Similares" para verificar.');
                }
            };
        }
        
        $this->validate($rules);

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->isEditing) {
            CostCenter::findOrFail($this->editingId)->update($data);
        } else {
            CostCenter::create($data);
            
            // Refresh resolution list
            $this->loadUnresolvedCostCenters();
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id) {
        CostCenter::findOrFail($id)->delete();
    }

    public function resetForm() {
        $this->editingId = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->isEditing = false;
        $this->costCenterSuggestions = [];
        $this->showCostCenterSuggestions = false;
    }

    public function with() {
        return [
            'costCenters' => CostCenter::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->orderBy('code')
                ->paginate(10),
        ];
    }
}; ?>

<div class="min-h-screen bg-gray-100 pb-12">
    <!-- Banner -->
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-800 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-extrabold text-white tracking-tight">Centros de Costo</h2>
                    <p class="mt-2 text-emerald-100 text-sm">Gestión de unidades de negocio y centros de costo.</p>
                </div>
                <button wire:click="create" 
                    class="inline-flex items-center px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-all shadow-lg backdrop-blur-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuevo CC
                </button>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="flex space-x-1 mt-6 bg-white/10 p-1 rounded-xl w-fit backdrop-blur-sm">
                <button wire:click="$set('activeTab', 'list')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $activeTab === 'list' ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/5' }}">
                    Listado Maestro
                </button>
                <button wire:click="$set('activeTab', 'resolution')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center {{ $activeTab === 'resolution' ? 'bg-white text-emerald-700 shadow-sm' : 'text-emerald-100 hover:bg-white/5' }}">
                    Resolución de Pendientes
                    @if(count($unresolvedCostCenters) > 0)
                        <span class="ml-2 px-1.5 py-0.5 rounded-full text-xs bg-red-500 text-white animate-pulse">
                            {{ count($unresolvedCostCenters) }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        
        <!-- Success Message -->
        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm">
                <p class="text-sm font-medium">{{ session('message') }}</p>
            </div>
        @endif
        
        <!-- LISTADO TAB -->
        <div x-show="$wire.activeTab === 'list'" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Toolbar -->
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <input wire:model.live.debounce.300ms="search" type="text" 
                    class="block w-full pl-4 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm transition-all" 
                    placeholder="Buscar por código o nombre...">
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($costCenters as $cc)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-emerald-700">{{ $cc->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $cc->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cc->description ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $cc->id }})" class="text-emerald-600 hover:text-emerald-900 mr-3">Editar</button>
                                    <button wire:click="delete({{ $cc->id }})" class="text-red-400 hover:text-red-600" onclick="confirm('¿Seguro?') || event.stopImmediatePropagation()">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <p class="text-gray-500 text-sm font-medium">No hay centros de costo registrados.</p>
                                        @if(count($unresolvedCostCenters) > 0)
                                            <p class="text-emerald-500 text-xs mt-2">Hay {{ count($unresolvedCostCenters) }} centros detectados sin registrar. <button wire:click="$set('activeTab', 'resolution')" class="underline font-bold">Ver Resolución</button></p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">{{ $costCenters->links() }}</div>
        </div>
        
        <!-- RESOLUTION TAB -->
        <div x-show="$wire.activeTab === 'resolution'" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="p-6 border-b border-gray-100 bg-yellow-50/50">
                <h3 class="text-lg font-bold text-yellow-800">Centros de Costo Detectados no Registrados</h3>
                <p class="text-sm text-yellow-600">Nombres de centros de costo encontrados en presupuestos que aún no han sido dados de alta en el catálogo maestro.</p>
            </div>
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre Detectado</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Registros Afectados</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($unresolvedCostCenters as $cc)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-800">
                                    {{ $cc['name'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $cc['count'] }} registro(s)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="resolveCostCenter('{{ addslashes($cc['name']) }}')" class="text-emerald-600 hover:text-emerald-900 font-bold">
                                    + Registrar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                ¡Todo al día! No hay centros de costo pendientes de resolución.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ $modalTitle }}</h3>
                        <div class="space-y-4">
                            <!-- Warning Message -->
                            @if(session('warning'))
                                <div class="p-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700">
                                    <p class="text-sm">{{ session('warning') }}</p>
                                </div>
                            @endif
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Código *</label>
                                <input type="text" wire:model="code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Ej: 101">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Nombre with Autocomplete -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="name" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                           placeholder="Escribe el nombre del centro de costo...">
                                    <button type="button"
                                            wire:click="searchSimilarCostCenters"
                                            class="mt-1 px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors text-sm font-medium whitespace-nowrap">
                                        🔍 Buscar Similares
                                    </button>
                                </div>
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                
                                <!-- Autocomplete Suggestions -->
                                @if($showCostCenterSuggestions && count($costCenterSuggestions) > 0)
                                    <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                        <div class="p-2 bg-yellow-50 border-b border-yellow-200">
                                            <p class="text-xs text-yellow-800 font-semibold">⚠️ Centros de costo similares encontrados:</p>
                                        </div>
                                        @foreach($costCenterSuggestions as $suggestion)
                                            <button type="button"
                                                    wire:click="selectExistingCostCenter({{ $suggestion->id }}, '{{ addslashes($suggestion->name) }}')"
                                                    class="w-full text-left px-4 py-2 hover:bg-emerald-50 transition-colors border-b border-gray-100 last:border-b-0">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-900">{{ $suggestion->name }}</span>
                                                    <span class="text-xs text-emerald-600 font-semibold">Ya existe ✓</span>
                                                </div>
                                            </button>
                                        @endforeach
                                        <div class="p-2 bg-gray-50 border-t border-gray-200">
                                            <p class="text-xs text-gray-600">💡 Si ninguno coincide, puedes crear uno nuevo.</p>
                                        </div>
                                    </div>
                                @elseif($showCostCenterSuggestions && count($costCenterSuggestions) === 0)
                                    <div class="mt-2 p-3 bg-green-50 border-l-4 border-green-400 text-green-700">
                                        <p class="text-sm">✓ No se encontraron centros de costo similares. Puedes proceder a crear este centro.</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                                <textarea wire:model="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Guardar</button>
                        <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
