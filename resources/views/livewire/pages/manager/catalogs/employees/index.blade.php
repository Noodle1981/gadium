<?php
use Livewire\Volt\Component;
use App\Models\JobFunction;
use App\Models\Guardia;
use App\Models\User;
use App\Models\UserAlias;
use App\Models\HourDetail;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;

    public $tab = 'functions'; // functions | guardias | aliases

    // Modal State
    public $showModal = false;
    public $isEditing = false;
    public $modalTitle = '';
    public $editingId = null;
    public $formName = '';

    // Alias Resolution State
    public $aliasSearch = '';
    public $selectedUserForAlias = []; // Map: alias_key => user_id

    // Cache
    public $users = [];

    public function mount() {
        $this->users = User::orderBy('name')->get();
    }

    // --- Tab Switching ---
    public function setTab($tab) {
        $this->tab = $tab;
        $this->resetPage();
    }

    // --- CRUD Actions ---
    public function create() {
        $this->formName = '';
        $this->editingId = null;
        $this->isEditing = false;
        $this->modalTitle = $this->tab === 'functions' ? 'Nueva Función' : 'Nueva Guardia';
        $this->showModal = true;
    }

    public function edit($id) {
        $model = $this->tab === 'functions' ? JobFunction::findOrFail($id) : Guardia::findOrFail($id);
        $this->editingId = $model->id;
        $this->formName = $model->name;
        $this->isEditing = true;
        $this->modalTitle = $this->tab === 'functions' ? 'Editar Función' : 'Editar Guardia';
        $this->showModal = true;
    }

    public function save() {
        $this->validate(['formName' => 'required|string|max:255']);

        if ($this->tab === 'functions') {
            if ($this->isEditing) {
                JobFunction::findOrFail($this->editingId)->update(['name' => $this->formName]);
            } else {
                JobFunction::create(['name' => $this->formName]);
            }
        } elseif ($this->tab === 'guardias') {
            if ($this->isEditing) {
                Guardia::findOrFail($this->editingId)->update(['name' => $this->formName]);
            } else {
                Guardia::create(['name' => $this->formName]);
            }
        }

        $this->showModal = false;
        $this->formName = '';
    }

    public function delete($id) {
        if ($this->tab === 'functions') {
            JobFunction::findOrFail($id)->delete();
        } elseif ($this->tab === 'guardias') {
            Guardia::findOrFail($id)->delete();
        }
    }

    // --- Alias Resolution Logic ---
    public function resolveAlias($aliasName) {
        // Find key in array (base64 encoded to handle spaces/chars in wire:model keys)
        $key = base64_encode($aliasName);
        
        if (!isset($this->selectedUserForAlias[$key]) || empty($this->selectedUserForAlias[$key])) {
            $this->js("alert('Seleccione un usuario para vincular.')");
            return;
        }

        $userId = $this->selectedUserForAlias[$key];
        
        DB::transaction(function() use ($aliasName, $userId) {
            // 1. Create Alias
            UserAlias::firstOrCreate(
                ['alias' => $aliasName],
                ['user_id' => $userId]
            );

            // 2. Update HourDetails Retroactively
            HourDetail::where('personal', $aliasName)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);
        });

        // Clear selection
        unset($this->selectedUserForAlias[$key]);
        $this->js("alert('Vinculado correctamente. Los registros históricos han sido actualizados.')");
    }

    public function with() {
        $aliases = collect([]);

        if ($this->tab === 'aliases') {
            // Find unique names in HourDetails with NULL user_id
            $query = HourDetail::whereNull('user_id')
                ->select('personal', DB::raw('count(*) as total'))
                ->groupBy('personal')
                ->orderByDesc('total');

            if ($this->aliasSearch) {
                $query->where('personal', 'like', '%' . $this->aliasSearch . '%');
            }
            
            // Pagination is tricky with groupBy, using simple paginate on query
            $aliases = $query->paginate(10);
        }

        return [
            'functions' => ($this->tab === 'functions') 
                ? JobFunction::orderBy('name')->paginate(10) 
                : collect([]),
            'guardias' => ($this->tab === 'guardias') 
                ? Guardia::orderBy('name')->paginate(10) 
                : collect([]),
            'unresolvedAliases' => $aliases,
        ];
    }
}; ?>

<div class="min-h-screen bg-gray-100 pb-12">
    <!-- Banner Principal -->
    <div class="bg-gradient-to-r from-orange-600 to-orange-800 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-white tracking-tight">Catálogo de Personal</h2>
                    <p class="mt-2 text-orange-100 text-sm">Gestión de Funciones, Guardias y Normalización de Nombres.</p>
                </div>
                
                @if($tab !== 'aliases')
                <button wire:click="create" 
                    class="inline-flex items-center px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-all shadow-lg backdrop-blur-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuevo Item
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Navegación de Pestañas -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        <div class="bg-white rounded-t-2xl shadow-xl border-x border-t border-gray-100 p-2 flex space-x-2 overflow-x-auto">
            <button wire:click="setTab('functions')" 
                class="px-6 py-3 rounded-xl text-sm font-bold transition-all {{ $tab === 'functions' ? 'bg-orange-50 text-orange-600 shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">
                Funciones
            </button>
            <button wire:click="setTab('guardias')" 
                class="px-6 py-3 rounded-xl text-sm font-bold transition-all {{ $tab === 'guardias' ? 'bg-orange-50 text-orange-600 shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">
                Guardias
            </button>
             <button wire:click="setTab('aliases')" class="px-6 py-3 rounded-xl text-sm font-bold transition-all {{ $tab === 'aliases' ? 'bg-orange-50 text-orange-600 shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">
                Resolución de Alias
            </button>
        </div>
        
        <div class="bg-white rounded-b-2xl shadow-xl border-x border-b border-gray-100 min-h-[400px]">
            <!-- Tab Content: Functions -->
            @if($tab === 'functions')
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre de Función</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($functions as $func)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $func->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="edit({{ $func->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
                                            <button wire:click="delete({{ $func->id }})" class="text-red-400 hover:text-red-600" onclick="confirm('¿Eliminar?') || event.stopImmediatePropagation()">Eliminar</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-12 text-center text-gray-500">No hay funciones registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Tab Content: Guardias -->
            @if($tab === 'guardias')
                <div class="p-6">
                     <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo de Guardia</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($guardias as $guardia)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $guardia->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="edit({{ $guardia->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
                                            <button wire:click="delete({{ $guardia->id }})" class="text-red-400 hover:text-red-600" onclick="confirm('¿Eliminar?') || event.stopImmediatePropagation()">Eliminar</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-12 text-center text-gray-500">No hay guardias registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

             <!-- Tab Content: Alias Resolution -->
            @if($tab === 'aliases')
                <div class="p-6">
                    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Estos nombres aparecen en los registros de Horas pero no están vinculados a ningún Usuario. 
                                    Vincúlelos a un usuario existente para normalizar la base de datos y corregir automáticamente el historial.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre Detectado (Alias)</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Registros Afectados</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Vincular a Usuario</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($unresolvedAliases as $alias)
                                    @php $key = base64_encode($alias->personal); @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $alias->personal }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $alias->total }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <select wire:model="selectedUserForAlias.{{ $key }}" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md">
                                                <option value="">Seleccionar Usuario...</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="resolveAlias('{{ $alias->personal }}')" 
                                                class="text-white bg-orange-600 hover:bg-orange-700 px-3 py-1.5 rounded-lg shadow-sm font-medium transition-colors">
                                                Vincular y Corregir
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="font-medium text-lg">¡Excelente! Todos los registros están normalizados.</span>
                                                <span class="text-sm mt-1">No se detectaron nombres sin vincular.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
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
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ $modalTitle }}</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input type="text" wire:model="formName" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                            @error('formName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar
                        </button>
                        <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
