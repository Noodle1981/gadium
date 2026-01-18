<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\Client;
use Illuminate\Validation\Rule;

new class extends Component {
    use WithPagination;

    // Search & Filers
    public $search = '';
    public $statusFilter = '';

    // Modal State
    public $showModal = false;
    public $isEditing = false;
    public $modalTitle = '';

    // Form Fields
    public $formId = ''; // The Project Code
    public $formName = '';
    public $formClientId = null;
    public $formSearchClient = ''; // For autocomplete
    public $formStatus = 'Activo';

    // Autocomplete State
    public $clientSuggestions = [];
    public $showClientSuggestions = false;

    public function mount() {
        $this->loadUnresolvedProjects();
    }

    // Tabs
    public $activeTab = 'list'; // list, resolution

    // Resolution Logic
    public $unresolvedProjects = [];

    public function loadUnresolvedProjects() {
        // Find distinct project codes across all operational tables
        $existingProjectIds = Project::pluck('id')->toArray();
        
        $projects = collect();
        
        // 1. From HourDetail
        $hoursProjects = \App\Models\HourDetail::query()
            ->select('proyecto')
            ->distinct()
            ->whereNotNull('proyecto')
            ->whereNotIn('proyecto', $existingProjectIds)
            ->get()
            ->map(function($item) {
                return [
                    'code' => $item->proyecto,
                    'source' => 'Horas',
                    'count' => \App\Models\HourDetail::where('proyecto', $item->proyecto)->count(),
                    'hint' => null, // No client info in hours
                ];
            });
        
        // 2. From BoardDetail
        $boardProjects = \App\Models\BoardDetail::query()
            ->select('proyecto_numero', 'cliente', 'descripcion_proyecto')
            ->distinct()
            ->whereNotNull('proyecto_numero')
            ->whereNotIn('proyecto_numero', $existingProjectIds)
            ->get()
            ->map(function($item) {
                return [
                    'code' => $item->proyecto_numero,
                    'source' => 'Tableros',
                    'count' => \App\Models\BoardDetail::where('proyecto_numero', $item->proyecto_numero)->count(),
                    'hint' => $item->cliente . ($item->descripcion_proyecto ? ' - ' . \Illuminate\Support\Str::limit($item->descripcion_proyecto, 40) : ''),
                ];
            });
        
        // 3. From AutomationProject
        $automationProjects = \App\Models\AutomationProject::query()
            ->select('proyecto_id', 'cliente', 'proyecto_descripcion')
            ->distinct()
            ->whereNotNull('proyecto_id')
            ->whereNotIn('proyecto_id', $existingProjectIds)
            ->get()
            ->map(function($item) {
                return [
                    'code' => $item->proyecto_id,
                    'source' => 'Automatización',
                    'count' => 1, // Each automation project is unique
                    'hint' => $item->cliente . ($item->proyecto_descripcion ? ' - ' . \Illuminate\Support\Str::limit($item->proyecto_descripcion, 40) : ''),
                ];
            });
        
        // Merge all sources
        $projects = $projects->merge($hoursProjects)
                            ->merge($boardProjects)
                            ->merge($automationProjects);
        
        // Group by code (in case same code appears in multiple sources)
        $grouped = $projects->groupBy('code')->map(function($group) {
            $first = $group->first();
            return [
                'code' => $first['code'],
                'sources' => $group->pluck('source')->unique()->implode(', '),
                'total_count' => $group->sum('count'),
                'hint' => $group->firstWhere('hint') ? $group->firstWhere('hint')['hint'] : null,
            ];
        });
        
        $this->unresolvedProjects = $grouped->sortByDesc('total_count')->values()->toArray();
    }

    public function resolveProject($code) {
        // Open create modal pre-filled with this code
        $this->resetForm();
        $this->formId = $code;
        $this->formName = ''; // User needs to provide name
        
        // Try to auto-detect client from source data
        $detectedClientName = null;
        
        // Check BoardDetail first (has cliente field)
        $boardRecord = \App\Models\BoardDetail::where('proyecto_numero', $code)->first();
        if ($boardRecord && $boardRecord->cliente) {
            $detectedClientName = $boardRecord->cliente;
        }
        
        // If not found, check AutomationProject
        if (!$detectedClientName) {
            $automationRecord = \App\Models\AutomationProject::where('proyecto_id', $code)->first();
            if ($automationRecord && $automationRecord->cliente) {
                $detectedClientName = $automationRecord->cliente;
            }
        }
        
        // If we detected a client name, try to match it with existing clients using word-based search
        if ($detectedClientName) {
            // Extract words from detected name for better matching
            $searchWords = preg_split('/\s+/', strtoupper(trim($detectedClientName)));
            
            // Build query to find clients containing any of the words
            $query = Client::query();
            foreach ($searchWords as $word) {
                if (strlen($word) >= 2) {
                    $query->orWhere('nombre', 'like', '%' . $word . '%');
                }
            }
            
            $matchedClient = $query->first();
            
            if ($matchedClient) {
                $this->formClientId = $matchedClient->id;
                $this->formSearchClient = $matchedClient->nombre;
            } else {
                // Pre-fill search with detected name for easy manual selection
                $this->formSearchClient = $detectedClientName;
            }
        }
        
        $this->isEditing = false;
        $this->modalTitle = 'Registrar Proyecto Detectado';
        $this->showModal = true;
    }

    // --- Search & Pagination ---
    public function updatedSearch() {
        $this->resetPage();
    }

    public function updatedFormSearchClient($value) {
        if (strlen($value) >= 2) {
            $this->clientSuggestions = Client::where('nombre', 'like', '%' . $value . '%')
                ->take(5)
                ->get();
            $this->showClientSuggestions = true;
        } else {
            $this->clientSuggestions = [];
            $this->showClientSuggestions = false;
        }
    }

    public function selectClient($id, $name) {
        $this->formClientId = $id;
        $this->formSearchClient = $name;
        $this->showClientSuggestions = false;
    }

    // --- CRUD Actions ---
    public function create() {
        $this->resetForm();
        $this->isEditing = false;
        $this->modalTitle = 'Nuevo Proyecto';
        $this->showModal = true;
    }

    public function edit($id) {
        $project = Project::findOrFail($id);
        
        $this->formId = $project->id;
        $this->formName = $project->name;
        $this->formClientId = $project->client_id;
        $this->formSearchClient = $project->client ? $project->client->nombre : '';
        $this->formStatus = $project->status;
        
        $this->isEditing = true;
        $this->modalTitle = 'Editar Proyecto';
        $this->showModal = true;
    }

    public function save() {
        $rules = [
            'formId' => ['required', 'string', 'max:50', $this->isEditing ? '' : 'unique:projects,id'],
            'formName' => 'required|string|max:255',
            'formClientId' => 'required|exists:clients,id',
            'formStatus' => 'required|in:Activo,Inactivo,Finalizado',
        ];

        $this->validate($rules);

        if ($this->isEditing) {
            $project = Project::findOrFail($this->formId);
            $project->update([
                'name' => $this->formName,
                'client_id' => $this->formClientId,
                'status' => $this->formStatus,
            ]);
        } else {
            Project::create([
                'id' => $this->formId, // Manual ID
                'name' => $this->formName,
                'client_id' => $this->formClientId,
                'status' => $this->formStatus,
            ]);
        }
        
        // Refresh resolution list if we just created a project that was missing
        if (!$this->isEditing) {
            $this->loadUnresolvedProjects();
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id) {
        // Soft delete
        Project::findOrFail($id)->delete();
        $this->loadUnresolvedProjects(); // In case deleting makes it appear again? No, soft delete keeps it in table usually, wait. Valid logic: soft deleted project ID still exists for `whereNotIn` unless we add filtering?? `whereNotIn` checks DB. unique check.
        // Actually soft deleted records might cause unique constraint issues if trying to recreate same ID.
        // But for resolution, if it's soft deleted, it exists in DB, so it WON'T show in resolution. Correct.
    }

    public function resetForm() {
        $this->formId = '';
        $this->formName = '';
        $this->formClientId = null;
        $this->formSearchClient = '';
        $this->formStatus = 'Activo';
        $this->clientSuggestions = [];
    }

    public function with() {
        return [
            'projects' => Project::query()
                ->with('client')
                ->where(function($q) {
                    $q->where('id', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%');
                })
                ->when($this->statusFilter, function($q) {
                    $q->where('status', $this->statusFilter);
                })
                ->orderBy('id', 'desc')
                ->paginate(15),
        ];
    }
}; ?>

<div class="min-h-screen bg-gray-100 pb-12">
    <!-- Banner Principal (Estilo Blue para Gestión) -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-extrabold text-white tracking-tight">Catalogo de Proyectos</h2>
                    <p class="mt-2 text-blue-100 text-sm">Gestión maestra de obras y servicios activos.</p>
                </div>
                <!-- Action Button -->
                <button wire:click="create" 
                    class="inline-flex items-center px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-all shadow-lg backdrop-blur-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuevo Proyecto
                </button>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="flex space-x-1 mt-6 bg-white/10 p-1 rounded-xl w-fit backdrop-blur-sm">
                <button wire:click="$set('activeTab', 'list')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $activeTab === 'list' ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-white/5' }}">
                    Listado Maestro
                </button>
                <button wire:click="$set('activeTab', 'resolution')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center {{ $activeTab === 'resolution' ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-white/5' }}">
                    Resolución de Pendientes
                    @if(count($unresolvedProjects) > 0)
                        <span class="ml-2 px-1.5 py-0.5 rounded-full text-xs bg-red-500 text-white animate-pulse">
                            {{ count($unresolvedProjects) }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        
        <!-- LISTADO TAB -->
        <div x-show="$wire.activeTab === 'list'" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 transition-all duration-300">
            <!-- Toolbar -->
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="flex-1 w-full md:w-auto relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" 
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm transition-all" 
                        placeholder="Buscar por código o nombre...">
                </div>
                
                <div class="w-full md:w-48">
                    <select wire:model.live="statusFilter" class="block w-full pl-3 pr-10 py-2.5 text-sm border-gray-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-xl">
                        <option value="">Todos los Estados</option>
                        <option value="Activo">Activos</option>
                        <option value="Inactivo">Inactivos</option>
                        <option value="Finalizado">Finalizados</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Código (ID)</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre del Proyecto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($projects as $project)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-gray-100 text-gray-800">
                                        {{ $project->id }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $project->client->nombre ?? 'Sin Cliente' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = match($project->status) {
                                            'Activo' => 'bg-green-100 text-green-800',
                                            'Inactivo' => 'bg-gray-100 text-gray-800',
                                            'Finalizado' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ $project->status ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit('{{ $project->id }}')" class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
                                    <button wire:click="delete('{{ $project->id }}')" class="text-red-400 hover:text-red-600" onclick="confirm('¿Estás seguro?') || event.stopImmediatePropagation()">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <p class="text-gray-500 text-sm font-medium">No se encontraron proyectos.</p>
                                        @if(count($unresolvedProjects) > 0)
                                            <p class="text-blue-500 text-xs mt-2">Hay {{ count($unresolvedProjects) }} códigos detectados sin registrar. <button wire:click="$set('activeTab', 'resolution')" class="underline font-bold">Ver Resolución</button></p>
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
                {{ $projects->links() }}
            </div>
        </div>

        <!-- RESOLUTION TAB -->
        <div x-show="$wire.activeTab === 'resolution'" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 transition-all duration-300">
            <div class="p-6 border-b border-gray-100 bg-yellow-50/50">
                <h3 class="text-lg font-bold text-yellow-800">Proyectos Detectados no Registrados</h3>
                <p class="text-sm text-yellow-600">Códigos de proyecto encontrados en Horas, Tableros y Automatización que aún no han sido dados de alta en el catálogo maestro.</p>
            </div>
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fuente(s)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Info Detectada</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Registros</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($unresolvedProjects as $project)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-gray-100 text-gray-800">
                                    {{ $project['code'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs text-gray-600 bg-blue-50 px-2 py-1 rounded">
                                    {{ $project['sources'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($project['hint'])
                                    <div class="text-xs text-gray-700">
                                        <span class="font-medium">{{ $project['hint'] }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Sin información adicional</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $project['total_count'] }} registro(s)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="resolveProject('{{ $project['code'] }}')" class="text-blue-600 hover:text-blue-900 font-bold">
                                    + Registrar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                ¡Todo al día! No hay proyectos pendientes de resolución.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form (Shared) -->
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
                            <!-- Código (ID) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Código de Proyecto (ID)</label>
                                <input type="text" wire:model="formId" 
                                    @if($isEditing) disabled @endif
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500" 
                                    placeholder="Ej: P-2025-01">
                                @error('formId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <!-- Nombre -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre del Proyecto</label>
                                <input type="text" wire:model="formName" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('formName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <!-- Cliente Autocomplete -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                                <input type="text" wire:model.live.debounce.300ms="formSearchClient" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Buscar cliente..." autocomplete="off">
                                @if($showClientSuggestions)
                                    <div class="absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-48 overflow-y-auto border border-gray-200">
                                        @foreach($clientSuggestions as $client)
                                            <div wire:click="selectClient({{ $client->id }}, '{{ $client->nombre }}')" 
                                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                                                {{ $client->nombre }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @error('formClientId') <span class="text-red-500 text-xs">Debe seleccionar un cliente válido.</span> @enderror
                            </div>
                            <!-- Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estado</label>
                                <select wire:model="formStatus" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                    <option value="Finalizado">Finalizado</option>
                                </select>
                                @error('formStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar
                        </button>
                        <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
