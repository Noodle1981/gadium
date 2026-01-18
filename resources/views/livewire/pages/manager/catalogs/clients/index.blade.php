<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Client;
use App\Models\BoardDetail;
use App\Models\AutomationProject;

new class extends Component {
    use WithPagination;

    // Search & Filters
    public $search = '';

    // Modal State
    public $showModal = false;
    public $isEditing = false;
    public $modalTitle = '';

    // Form Fields
    public $formNombre = '';
    public $formId = null;
    
    // Autocomplete State
    public $clientSuggestions = [];
    public $showClientSuggestions = false;

    // Tabs
    public $activeTab = 'list'; // list, resolution

    // Resolution Logic
    public $unresolvedClients = [];

    public function mount() {
        $this->loadUnresolvedClients();
    }

    public function loadUnresolvedClients() {
        // Find distinct client names across BoardDetail and AutomationProject
        $existingClientNames = Client::pluck('nombre')->map(fn($n) => strtoupper(trim($n)))->toArray();
        
        $clients = collect();
        
        // 1. From BoardDetail
        $boardClients = BoardDetail::query()
            ->select('cliente')
            ->distinct()
            ->whereNotNull('cliente')
            ->get()
            ->map(function($item) {
                return [
                    'name' => trim($item->cliente),
                    'source' => 'Tableros',
                    'count' => BoardDetail::where('cliente', $item->cliente)->count(),
                ];
            })
            ->filter(fn($item) => !in_array(strtoupper($item['name']), $existingClientNames));
        
        // 2. From AutomationProject
        $automationClients = AutomationProject::query()
            ->select('cliente')
            ->distinct()
            ->whereNotNull('cliente')
            ->get()
            ->map(function($item) {
                return [
                    'name' => trim($item->cliente),
                    'source' => 'Automatización',
                    'count' => AutomationProject::where('cliente', $item->cliente)->count(),
                ];
            })
            ->filter(fn($item) => !in_array(strtoupper($item['name']), $existingClientNames));
        
        // Merge all sources
        $clients = $clients->merge($boardClients)->merge($automationClients);
        
        // Group by name (case-insensitive)
        $grouped = $clients->groupBy(fn($item) => strtoupper($item['name']))->map(function($group) {
            $first = $group->first();
            return [
                'name' => $first['name'],
                'sources' => $group->pluck('source')->unique()->implode(', '),
                'total_count' => $group->sum('count'),
            ];
        });
        
        $this->unresolvedClients = $grouped->sortByDesc('total_count')->values()->toArray();
    }

    public function resolveClient($name) {
        // Open create modal pre-filled with this name
        $this->resetForm();
        $this->formNombre = $name;
        $this->isEditing = false;
        $this->modalTitle = 'Registrar Cliente Detectado';
        $this->showModal = true;
    }

    // --- Search & Pagination ---
    public function updatedSearch() {
        $this->resetPage();
    }
    
    public function updatedFormNombre($value) {
        if (strlen($value) >= 2) {
            $this->clientSuggestions = Client::where('nombre', 'like', '%' . $value . '%')
                ->take(5)
                ->get();
            $this->showClientSuggestions = count($this->clientSuggestions) > 0;
        } else {
            $this->clientSuggestions = [];
            $this->showClientSuggestions = false;
        }
    }
    
    public function searchSimilarClients() {
        // Manually trigger search for similar clients with improved matching
        if (strlen($this->formNombre) >= 2) {
            // Extract words from search term
            $searchWords = preg_split('/\s+/', strtoupper(trim($this->formNombre)));
            
            // Build query to find clients containing any of the words
            $query = Client::query();
            foreach ($searchWords as $word) {
                if (strlen($word) >= 2) {
                    $query->orWhere('nombre', 'like', '%' . $word . '%');
                }
            }
            
            $this->clientSuggestions = $query->take(10)->get();
            
            // Always show feedback, even if empty
            $this->showClientSuggestions = true;
        }
    }
    
    public function selectExistingClient($id, $name) {
        // User confirmed this detected name should be unified with existing client
        // Normalize ALL source records to use the canonical client name
        
        $detectedName = $this->formNombre; // e.g., "DOM S.R.L"
        $canonicalName = $name; // e.g., "DOM"
        $canonicalClientId = $id; // ID of the client we're keeping
        
        // First, check if there are duplicate clients with the canonical name
        $duplicates = Client::whereRaw('UPPER(nombre) = ?', [strtoupper($canonicalName)])
            ->where('id', '!=', $canonicalClientId)
            ->get();
        
        // Delete duplicates (keep only the one with $canonicalClientId)
        foreach ($duplicates as $duplicate) {
            $duplicate->delete();
        }
        
        // Update BoardDetail records
        $boardUpdated = \App\Models\BoardDetail::where('cliente', $detectedName)
            ->update(['cliente' => $canonicalName]);
        
        // Update AutomationProject records
        $automationUpdated = \App\Models\AutomationProject::where('cliente', $detectedName)
            ->update(['cliente' => $canonicalName]);
        
        $totalUpdated = $boardUpdated + $automationUpdated;
        $duplicatesDeleted = $duplicates->count();
        
        $this->showModal = false;
        $this->resetForm();
        $this->loadUnresolvedClients();
        
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
        $this->modalTitle = 'Nuevo Cliente';
        $this->showModal = true;
    }

    public function edit($id) {
        $client = Client::findOrFail($id);
        
        $this->formId = $client->id;
        $this->formNombre = $client->nombre;
        
        $this->isEditing = true;
        $this->modalTitle = 'Editar Cliente';
        $this->showModal = true;
    }

    public function save() {
        $rules = [
            'formNombre' => [
                'required',
                'string',
                'max:255',
            ],
        ];
        
        // Add unique validation (case-insensitive)
        if ($this->isEditing) {
            // When editing, ignore current record
            $rules['formNombre'][] = function ($attribute, $value, $fail) {
                $exists = Client::where('id', '!=', $this->formId)
                    ->whereRaw('UPPER(nombre) = ?', [strtoupper($value)])
                    ->exists();
                if ($exists) {
                    $fail('Ya existe un cliente con este nombre (sin distinguir mayúsculas/minúsculas).');
                }
            };
        } else {
            // When creating, check if name exists
            $rules['formNombre'][] = function ($attribute, $value, $fail) {
                $exists = Client::whereRaw('UPPER(nombre) = ?', [strtoupper($value)])->exists();
                if ($exists) {
                    $fail('Ya existe un cliente con este nombre. Usa "Buscar Similares" para verificar.');
                }
            };
        }

        $this->validate($rules);

        if ($this->isEditing) {
            $client = Client::findOrFail($this->formId);
            $client->update([
                'nombre' => $this->formNombre,
            ]);
        } else {
            Client::create([
                'nombre' => $this->formNombre,
            ]);
            
            // Refresh resolution list
            $this->loadUnresolvedClients();
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id) {
        Client::findOrFail($id)->delete();
    }

    public function resetForm() {
        $this->formId = null;
        $this->formNombre = '';
        $this->clientSuggestions = [];
        $this->showClientSuggestions = false;
    }

    public function with() {
        return [
            'clients' => Client::query()
                ->where('nombre', 'like', '%' . $this->search . '%')
                ->orderBy('nombre', 'asc')
                ->paginate(15),
        ];
    }
}; ?>

<div class="min-h-screen bg-gray-100 pb-12">
    <!-- Banner Principal -->
    <div class="bg-gradient-to-r from-orange-600 to-orange-800 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-extrabold text-white tracking-tight">Catálogo de Clientes</h2>
                    <p class="mt-2 text-orange-100 text-sm">Gestión maestra de clientes y normalización de nombres.</p>
                </div>
                <button wire:click="create" 
                    class="inline-flex items-center px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-all shadow-lg backdrop-blur-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuevo Cliente
                </button>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="flex space-x-1 mt-6 bg-white/10 p-1 rounded-xl w-fit backdrop-blur-sm">
                <button wire:click="$set('activeTab', 'list')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $activeTab === 'list' ? 'bg-white text-orange-700 shadow-sm' : 'text-orange-100 hover:bg-white/5' }}">
                    Listado Maestro
                </button>
                <button wire:click="$set('activeTab', 'resolution')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center {{ $activeTab === 'resolution' ? 'bg-white text-orange-700 shadow-sm' : 'text-orange-100 hover:bg-white/5' }}">
                    Resolución de Pendientes
                    @if(count($unresolvedClients) > 0)
                        <span class="ml-2 px-1.5 py-0.5 rounded-full text-xs bg-red-500 text-white animate-pulse">
                            {{ count($unresolvedClients) }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        
        <!-- Success Message -->
        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm">
                <p class="text-sm font-medium">{{ session('message') }}</p>
            </div>
        @endif
        
        <!-- LISTADO TAB -->
        <div x-show="$wire.activeTab === 'list'" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 transition-all duration-300">
            <!-- Toolbar -->
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" 
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm transition-all" 
                        placeholder="Buscar cliente por nombre...">
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre del Cliente</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($clients as $client)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $client->nombre }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $client->id }})" class="text-orange-600 hover:text-orange-900 mr-3">Editar</button>
                                    <button wire:click="delete({{ $client->id }})" class="text-red-400 hover:text-red-600" onclick="confirm('¿Estás seguro?') || event.stopImmediatePropagation()">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <p class="text-gray-500 text-sm font-medium">No se encontraron clientes.</p>
                                        @if(count($unresolvedClients) > 0)
                                            <p class="text-orange-500 text-xs mt-2">Hay {{ count($unresolvedClients) }} clientes detectados sin registrar. <button wire:click="$set('activeTab', 'resolution')" class="underline font-bold">Ver Resolución</button></p>
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
                {{ $clients->links() }}
            </div>
        </div>

        <!-- RESOLUTION TAB -->
        <div x-show="$wire.activeTab === 'resolution'" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 transition-all duration-300">
            <div class="p-6 border-b border-gray-100 bg-yellow-50/50">
                <h3 class="text-lg font-bold text-yellow-800">Clientes Detectados no Registrados</h3>
                <p class="text-sm text-yellow-600">Nombres de clientes encontrados en Tableros y Automatización que aún no han sido dados de alta en el catálogo maestro.</p>
            </div>
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre Detectado</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fuente(s)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Registros Afectados</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($unresolvedClients as $client)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-800">
                                    {{ $client['name'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $client['sources'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $client['total_count'] }} registro(s)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="resolveClient('{{ addslashes($client['name']) }}')" class="text-orange-600 hover:text-orange-900 font-bold">
                                    + Registrar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                ¡Todo al día! No hay clientes pendientes de resolución.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                                <label class="block text-sm font-medium text-gray-700">Nombre del Cliente *</label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="formNombre" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                                           placeholder="Escribe el nombre del cliente...">
                                    <button type="button"
                                            wire:click="searchSimilarClients"
                                            class="mt-1 px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors text-sm font-medium whitespace-nowrap">
                                        🔍 Buscar Similares
                                    </button>
                                </div>
                                @error('formNombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                
                                <!-- Autocomplete Suggestions -->
                                @if($showClientSuggestions && count($clientSuggestions) > 0)
                                    <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                        <div class="p-2 bg-yellow-50 border-b border-yellow-200">
                                            <p class="text-xs text-yellow-800 font-semibold">⚠️ Clientes similares encontrados:</p>
                                        </div>
                                        @foreach($clientSuggestions as $suggestion)
                                            <button type="button"
                                                    wire:click="selectExistingClient({{ $suggestion->id }}, '{{ addslashes($suggestion->nombre) }}')"
                                                    class="w-full text-left px-4 py-2 hover:bg-orange-50 transition-colors border-b border-gray-100 last:border-b-0">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-900">{{ $suggestion->nombre }}</span>
                                                    <span class="text-xs text-orange-600 font-semibold">Ya existe ✓</span>
                                                </div>
                                            </button>
                                        @endforeach
                                        <div class="p-2 bg-gray-50 border-t border-gray-200">
                                            <p class="text-xs text-gray-600">💡 Si ninguno coincide, puedes crear uno nuevo.</p>
                                        </div>
                                    </div>
                                @elseif($showClientSuggestions && count($clientSuggestions) === 0)
                                    <div class="mt-2 p-3 bg-green-50 border-l-4 border-green-400 text-green-700">
                                        <p class="text-sm">✓ No se encontraron clientes similares. Puedes proceder a crear este cliente.</p>
                                    </div>
                                @endif
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
