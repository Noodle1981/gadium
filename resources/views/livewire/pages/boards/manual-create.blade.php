<?php

use Livewire\Volt\Component;
use App\Models\BoardDetail;

new class extends Component {
    public $ano;
    public $proyecto_numero;
    public $cliente;
    public $descripcion_proyecto;
    public $columnas = 0;
    public $gabinetes = 0;
    public $potencia = 0;
    public $pot_control = 0;
    public $control = 0;
    public $intervencion = 0;
    public $documento_correccion_fallas = 0;

    // Autocomplete State
    public $searchResults = [];
    public $selectedClientId = null;
    public $showClientSearch = false;
    public $isNewClient = false;

    public function updatedCliente($value)
    {
        $this->selectedClientId = null;
        $this->isNewClient = false;

        if (strlen($value) >= 2) {
            $service = app(\App\Services\ClientNormalizationService::class);
            $results = $service->findSimilarClients($value)->take(5);
            
            $this->searchResults = $results->map(function($item) {
                return [
                    'id' => $item['client']->id,
                    'nombre' => $item['client']->nombre,
                ];
            })->toArray();
            
            $this->showClientSearch = !empty($this->searchResults);
        } else {
            $this->searchResults = [];
            $this->showClientSearch = false;
        }
    }

    public function selectClient($id, $name)
    {
        $this->cliente = $name;
        $this->selectedClientId = $id;
        $this->showClientSearch = false;
        $this->searchResults = [];
        $this->isNewClient = false;
    }

    public function markAsNewClient()
    {
        $this->selectedClientId = null;
        $this->isNewClient = true;
        $this->showClientSearch = false;
        $this->searchResults = [];
    }

    public function mount()
    {
        $this->ano = date('Y');
    }

    public function store()
    {
        $validated = $this->validate([
            'ano' => 'required|integer|min:2000|max:2100',
            'proyecto_numero' => 'required|string|max:255',
            'cliente' => 'required|string|max:255',
            'descripcion_proyecto' => 'required|string',
            'columnas' => 'required|integer|min:0',
            'gabinetes' => 'required|integer|min:0',
            'potencia' => 'required|integer|min:0',
            'pot_control' => 'required|integer|min:0',
            'control' => 'required|integer|min:0',
            'intervencion' => 'required|integer|min:0',
            'documento_correccion_fallas' => 'required|integer|min:0',
        ]);

        $hash = BoardDetail::generateHash(
            $this->ano,
            $this->proyecto_numero,
            $this->cliente,
            $this->descripcion_proyecto
        );

        if (BoardDetail::existsByHash($hash)) {
            $this->addError('proyecto_numero', 'Ya existe un registro idéntico (Año, Proyecto, Cliente, Descripción).');
            return;
        }

        BoardDetail::create(array_merge($validated, ['hash' => $hash]));

        session()->flash('message', 'Tablero registrado correctamente.');
        $this->redirect(route(auth()->user()->hasRole('Manager') ? 'manager.historial.tableros' : (auth()->user()->hasRole('Gestor de Tableros') ? 'boards.historial.importacion' : 'admin.historial.tableros')));
    }
}; ?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Registrar Tablero Manualmente</h2>

        <form wire:submit="store" class="space-y-6">
            <!-- Bloque 1: Datos del Proyecto -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Datos Principales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Año</label>
                        <input type="number" wire:model="ano" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('ano') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nro Proyecto</label>
                        <input type="text" wire:model="proyecto_numero" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('proyecto_numero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                     <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Cliente</label>
                        <div class="relative mt-1">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="cliente" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $selectedClientId ? 'bg-green-50 border-green-300 text-green-800' : '' }}"
                                    placeholder="Buscar Cliente..."
                                >
                                
                                <!-- Badge: Cliente Existente -->
                                @if($selectedClientId)
                                    <span class="absolute right-2 top-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded font-medium">
                                        ✓ Existente
                                    </span>
                                @endif
                                
                                <!-- Badge: Cliente Nuevo -->
                                @if($isNewClient)
                                    <span class="absolute right-2 top-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded font-medium">
                                        ⊕ Nuevo
                                    </span>
                                @endif
                            </div>
                            
                            @if($showClientSearch)
                                <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                    @if(!empty($searchResults))
                                        @foreach($searchResults as $result)
                                            <div 
                                                wire:click="selectClient({{ $result['id'] }}, '{{ $result['nombre'] }}')"
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-orange-50 flex justify-between items-center"
                                            >
                                                <span class="block truncate font-medium">{{ $result['nombre'] }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="py-3 px-4 text-sm text-gray-500 text-center">
                                            No se encontraron coincidencias
                                        </div>
                                    @endif
                                    
                                    <!-- Opción: Crear nuevo -->
                                    <div 
                                        wire:click="markAsNewClient"
                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 bg-blue-50 hover:bg-blue-100 border-t border-blue-200 text-blue-700 font-medium"
                                    >
                                        <span class="block">⊕ Crear nuevo: "{{ $cliente }}"</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @error('cliente') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                     <div class="col-span-4">
                        <label class="block text-sm font-medium text-gray-700">Descripción Proyecto</label>
                        <textarea wire:model="descripcion_proyecto" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        @error('descripcion_proyecto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Bloque 2: Cantidades -->
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h3 class="text-lg font-medium text-blue-800 mb-4">Cantidades Fabricadas</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Columnas</label>
                        <input type="number" wire:model="columnas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('columnas') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gabinetes</label>
                        <input type="number" wire:model="gabinetes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('gabinetes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Potencia</label>
                        <input type="number" wire:model="potencia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('potencia') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pot/Control</label>
                        <input type="number" wire:model="pot_control" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('pot_control') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Control</label>
                        <input type="number" wire:model="control" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('control') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Intervención</label>
                        <input type="number" wire:model="intervencion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('intervencion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 text-xs">Doc. Corr. Fallas</label>
                        <input type="number" wire:model="documento_correccion_fallas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('documento_correccion_fallas') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.historial.tableros' : (auth()->user()->hasRole('Gestor de Tableros') ? 'boards.historial.importacion' : 'admin.historial.tableros')) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Guardar Registro
                </button>
            </div>
        </form>
    </div>
</div>
