<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use App\Models\PurchaseDetail;

new class extends Component {
    use WithPagination;

    // Search & Filters
    public $search = '';
    public $statusFilter = '';

    // Modal State
    public $showModal = false;
    public $isEditing = false;
    public $modalTitle = '';

    // Form Fields
    public $formName = '';
    public $formTaxId = '';
    public $formAddress = '';
    public $formEmail = '';
    public $formPhone = '';
    public $formStatus = 'Activo';
    public $formId = null;
    
    // Autocomplete State
    public $supplierSuggestions = [];
    public $showSupplierSuggestions = false;

    // Tabs
    public $activeTab = 'list'; // list, resolution

    // Resolution Logic
    public $unresolvedSuppliers = [];

    public function mount() {
        $this->loadUnresolvedSuppliers();
    }

    public function loadUnresolvedSuppliers() {
        // Find distinct supplier names in PurchaseDetail without supplier_id
        $this->unresolvedSuppliers = PurchaseDetail::query()
            ->select('empresa')
            ->distinct()
            ->whereNotNull('empresa')
            ->whereNull('supplier_id')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->empresa,
                    'count' => PurchaseDetail::where('empresa', $item->empresa)->whereNull('supplier_id')->count()
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->toArray();
    }

    public function resolveSupplier($name) {
        // Open create modal pre-filled with this name
        $this->resetForm();
        $this->formName = $name;
        $this->isEditing = false;
        $this->modalTitle = 'Registrar Proveedor Detectado';
        $this->showModal = true;
    }

    // --- Search & Pagination ---
    public function updatedSearch() {
        $this->resetPage();
    }
    
    public function updatedFormName($value) {
        if (strlen($value) >= 2) {
            $this->supplierSuggestions = Supplier::where('name', 'like', '%' . $value . '%')
                ->take(5)
                ->get();
            $this->showSupplierSuggestions = count($this->supplierSuggestions) > 0;
        } else {
            $this->supplierSuggestions = [];
            $this->showSupplierSuggestions = false;
        }
    }
    
    public function searchSimilarSuppliers() {
        // Manually trigger search for similar suppliers with improved matching
        if (strlen($this->formName) >= 2) {
            // Extract words from search term
            $searchWords = preg_split('/\s+/', strtoupper(trim($this->formName)));
            
            // Build query to find suppliers containing any of the words
            $query = Supplier::query();
            foreach ($searchWords as $word) {
                if (strlen($word) >= 2) {
                    $query->orWhere('name', 'like', '%' . $word . '%');
                }
            }
            
            $this->supplierSuggestions = $query->take(10)->get();
            
            // Always show feedback, even if empty
            $this->showSupplierSuggestions = true;
        }
    }
    
    public function selectExistingSupplier($id, $name) {
        // User confirmed this detected name should be unified with existing supplier
        // Normalize ALL source records to use the canonical supplier name
        
        $detectedName = $this->formName; // e.g., "ACEROS S.R.L"
        $canonicalName = $name; // e.g., "ACEROS"
        $canonicalSupplierId = $id; // ID of the supplier we're keeping
        
        // First, check if there are duplicate suppliers with the canonical name
        $duplicates = Supplier::whereRaw('UPPER(name) = ?', [strtoupper($canonicalName)])
            ->where('id', '!=', $canonicalSupplierId)
            ->get();
        
        // Delete duplicates (keep only the one with $canonicalSupplierId)
        foreach ($duplicates as $duplicate) {
            $duplicate->delete();
        }
        
        // Update PurchaseDetail records - normalize empresa field
        $purchaseUpdated = PurchaseDetail::where('empresa', $detectedName)
            ->update([
                'empresa' => $canonicalName,
                'supplier_id' => $canonicalSupplierId
            ]);
        
        $totalUpdated = $purchaseUpdated;
        $duplicatesDeleted = $duplicates->count();
        
        $this->showModal = false;
        $this->resetForm();
        $this->loadUnresolvedSuppliers();
        
        $message = "✓ Normalización completa: {$totalUpdated} registro(s) actualizados de '{$detectedName}' → '{$canonicalName}'";
        if ($duplicatesDeleted > 0) {
            $message .= " | {$duplicatesDeleted} duplicado(s) eliminado(s)";
        }
        
        session()->flash('message', $message);
    }

    // --- CRUD Actions ---
    public function create() {
        $this->resetForm();
        $this->isEditing = false;
        $this->modalTitle = 'Nuevo Proveedor';
        $this->showModal = true;
    }

    public function edit($id) {
        $supplier = Supplier::findOrFail($id);
        
        $this->formId = $supplier->id;
        $this->formName = $supplier->name;
        $this->formTaxId = $supplier->tax_id ?? '';
        $this->formAddress = $supplier->address ?? '';
        $this->formEmail = $supplier->email ?? '';
        $this->formPhone = $supplier->phone ?? '';
        $this->formStatus = $supplier->status ?? 'Activo';
        
        $this->isEditing = true;
        $this->modalTitle = 'Editar Proveedor';
        $this->showModal = true;
    }

    public function save() {
        $rules = [
            'formName' => [
                'required',
                'string',
                'max:255',
            ],
            'formTaxId' => 'nullable|string|max:50',
            'formAddress' => 'nullable|string|max:500',
            'formEmail' => 'nullable|email|max:255',
            'formPhone' => 'nullable|string|max:50',
            'formStatus' => 'required|in:Activo,Inactivo',
        ];
        
        // Add unique validation (case-insensitive)
        if ($this->isEditing) {
            // When editing, ignore current record
            $rules['formName'][] = function ($attribute, $value, $fail) {
                $exists = Supplier::where('id', '!=', $this->formId)
                    ->whereRaw('UPPER(name) = ?', [strtoupper($value)])
                    ->exists();
                if ($exists) {
                    $fail('Ya existe un proveedor con este nombre (sin distinguir mayúsculas/minúsculas).');
                }
            };
        } else {
            // When creating, check if name exists
            $rules['formName'][] = function ($attribute, $value, $fail) {
                $exists = Supplier::whereRaw('UPPER(name) = ?', [strtoupper($value)])->exists();
                if ($exists) {
                    $fail('Ya existe un proveedor con este nombre. Usa "Buscar Similares" para verificar.');
                }
            };
        }

        $this->validate($rules);

        if ($this->isEditing) {
            $supplier = Supplier::findOrFail($this->formId);
            $supplier->update([
                'name' => $this->formName,
                'tax_id' => $this->formTaxId,
                'address' => $this->formAddress,
                'email' => $this->formEmail,
                'phone' => $this->formPhone,
                'status' => $this->formStatus,
            ]);
        } else {
            $supplier = Supplier::create([
                'name' => $this->formName,
                'tax_id' => $this->formTaxId,
                'address' => $this->formAddress,
                'email' => $this->formEmail,
                'phone' => $this->formPhone,
                'status' => $this->formStatus,
            ]);
            
            // Auto-assign to matching purchase_details
            PurchaseDetail::where('empresa', $this->formName)
                ->whereNull('supplier_id')
                ->update(['supplier_id' => $supplier->id]);
        }
        
        // Refresh resolution list
        if (!$this->isEditing) {
            $this->loadUnresolvedSuppliers();
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id) {
        Supplier::findOrFail($id)->delete();
    }

    public function resetForm() {
        $this->formId = null;
        $this->formName = '';
        $this->formTaxId = '';
        $this->formAddress = '';
        $this->formEmail = '';
        $this->formPhone = '';
        $this->formStatus = 'Activo';
        $this->supplierSuggestions = [];
        $this->showSupplierSuggestions = false;
    }

    public function with() {
        return [
            'suppliers' => Supplier::query()
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('tax_id', 'like', '%' . $this->search . '%');
                })
                ->when($this->statusFilter, function($q) {
                    $q->where('status', $this->statusFilter);
                })
                ->orderBy('name', 'asc')
                ->paginate(15),
        ];
    }
}; ?>


<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">Catálogo de Proveedores</h1>
                        <p class="text-orange-100 text-sm">Gestión maestra de proveedores y empresas.</p>
                    </div>
                    <button wire:click="create" 
                        class="inline-flex items-center px-5 py-2.5 bg-white text-orange-700 rounded-xl font-bold shadow-md hover:bg-orange-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuevo Proveedor
                    </button>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Tabs Navigation -->
            <div class="flex space-x-1 mb-6 bg-gray-200 p-1 rounded-xl w-fit">
                <button wire:click="$set('activeTab', 'list')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $activeTab === 'list' ? 'bg-white text-orange-700 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-300/50' }}">
                    Listado Maestro
                </button>
                <button wire:click="$set('activeTab', 'resolution')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center {{ $activeTab === 'resolution' ? 'bg-white text-orange-700 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-300/50' }}">
                    Resolución de Pendientes
                    @if(count($unresolvedSuppliers) > 0)
                        <span class="ml-2 px-1.5 py-0.5 rounded-full text-xs bg-red-500 text-white animate-pulse">
                            {{ count($unresolvedSuppliers) }}
                        </span>
                    @endif
                </button>
            </div>

            <!-- Success Message -->
            @if(session('message'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm">
                    <p class="text-sm font-medium">{{ session('message') }}</p>
                </div>
            @endif
            
            <!-- LISTADO TAB -->
            <div x-show="$wire.activeTab === 'list'" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 transition-all duration-300">
                <!-- Toolbar -->
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row gap-4 justify-between items-center">
                    <div class="flex-1 w-full md:w-auto relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" 
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm transition-all" 
                            placeholder="Buscar por nombre o Tax ID...">
                    </div>
                    
                    <div class="w-full md:w-48">
                        <select wire:model.live="statusFilter" class="block w-full pl-3 pr-10 py-2.5 text-sm border-gray-200 focus:outline-none focus:ring-orange-500 focus:border-orange-500 rounded-xl">
                            <option value="">Todos los Estados</option>
                            <option value="Activo">Activos</option>
                            <option value="Inactivo">Inactivos</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tax ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contacto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($suppliers as $supplier)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $supplier->name }}</div>
                                        @if($supplier->address)
                                            <div class="text-xs text-gray-500">{{ Str::limit($supplier->address, 40) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-500">{{ $supplier->tax_id ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($supplier->email)
                                            <div class="text-xs text-gray-700">{{ $supplier->email }}</div>
                                        @endif
                                        @if($supplier->phone)
                                            <div class="text-xs text-gray-500">{{ $supplier->phone }}</div>
                                        @endif
                                        @if(!$supplier->email && !$supplier->phone)
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = $supplier->status === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ $supplier->status ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="edit({{ $supplier->id }})" class="text-orange-600 hover:text-orange-900 mr-3">Editar</button>
                                        <button wire:click="delete({{ $supplier->id }})" class="text-red-400 hover:text-red-600" onclick="confirm('¿Estás seguro?') || event.stopImmediatePropagation()">Eliminar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            </svg>
                                            <p class="text-gray-500 text-sm font-medium">No se encontraron proveedores.</p>
                                            @if(count($unresolvedSuppliers) > 0)
                                                <p class="text-orange-500 text-xs mt-2">Hay {{ count($unresolvedSuppliers) }} proveedores detectados sin registrar. <button wire:click="$set('activeTab', 'resolution')" class="underline font-bold">Ver Resolución</button></p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $suppliers->links() }}
                </div>
            </div>

            <!-- RESOLUTION TAB -->
            <div x-show="$wire.activeTab === 'resolution'" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 transition-all duration-300">
                <div class="p-6 border-b border-gray-100 bg-yellow-50/50">
                    <h3 class="text-lg font-bold text-yellow-800">Proveedores Detectados no Registrados</h3>
                    <p class="text-sm text-yellow-600">Nombres de empresas encontrados en el historial de compras que aún no han sido dados de alta en el catálogo maestro.</p>
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
                        @forelse($unresolvedSuppliers as $supplier)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-800">
                                        {{ $supplier['name'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $supplier['count'] }} registro(s)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="resolveSupplier('{{ addslashes($supplier['name']) }}')" class="text-orange-600 hover:text-orange-900 font-bold">
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
                                    ¡Todo al día! No hay proveedores pendientes de resolución.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                            {{ $modalTitle }}
                        </h3>
                        <div class="space-y-4">
                            <!-- Warning Message -->
                            @if(session('warning'))
                                <div class="p-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700">
                                    <p class="text-sm">{{ session('warning') }}</p>
                                </div>
                            @endif
                            
                            <!-- Nombre -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700">Nombre del Proveedor *</label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="formName" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                                           placeholder="Escribe el nombre del proveedor...">
                                    <button type="button"
                                            wire:click="searchSimilarSuppliers"
                                            class="mt-1 px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors text-sm font-medium whitespace-nowrap">
                                        🔍 Buscar Similares
                                    </button>
                                </div>
                                @error('formName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                
                                <!-- Autocomplete Suggestions -->
                                @if($showSupplierSuggestions && count($supplierSuggestions) > 0)
                                    <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                        <div class="p-2 bg-yellow-50 border-b border-yellow-200">
                                            <p class="text-xs text-yellow-800 font-semibold">⚠️ Proveedores similares encontrados:</p>
                                        </div>
                                        @foreach($supplierSuggestions as $suggestion)
                                            <button type="button"
                                                    wire:click="selectExistingSupplier({{ $suggestion->id }}, '{{ addslashes($suggestion->name) }}')"
                                                    class="w-full text-left px-4 py-2 hover:bg-orange-50 transition-colors border-b border-gray-100 last:border-b-0">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-900">{{ $suggestion->name }}</span>
                                                    <span class="text-xs text-orange-600 font-semibold">Ya existe ✓</span>
                                                </div>
                                            </button>
                                        @endforeach
                                        <div class="p-2 bg-gray-50 border-t border-gray-200">
                                            <p class="text-xs text-gray-600">💡 Si ninguno coincide, puedes crear uno nuevo.</p>
                                        </div>
                                    </div>
                                @elseif($showSupplierSuggestions && count($supplierSuggestions) === 0)
                                    <div class="mt-2 p-3 bg-green-50 border-l-4 border-green-400 text-green-700">
                                        <p class="text-sm">✓ No se encontraron proveedores similares. Puedes proceder a crear este proveedor.</p>
                                    </div>
                                @endif
                            </div>
                            <!-- Tax ID -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tax ID / RUC / CUIT</label>
                                <input type="text" wire:model="formTaxId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                @error('formTaxId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <!-- Address -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dirección</label>
                                <textarea wire:model="formAddress" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"></textarea>
                                @error('formAddress') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" wire:model="formEmail" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                @error('formEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input type="text" wire:model="formPhone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                @error('formPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <!-- Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estado</label>
                                <select wire:model="formStatus" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                                @error('formStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar
                        </button>
                        <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
