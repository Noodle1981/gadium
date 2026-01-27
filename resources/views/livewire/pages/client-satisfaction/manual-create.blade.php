<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Client;
use App\Models\ClientSatisfactionResponse;
use App\Services\ClientSatisfactionCalculator;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    
    public $rows = [];
    
    // Autocomplete State per Row
    public $searchQueries = []; // [index => 'query']
    public $searchResults = []; // [index => [results]]
    public $showResults = [];   // [index => bool]
    
    public function mount()
    {
        // No load all clients anymore
        $this->addRow();
    }

    public function addRow()
    {
        $this->rows[] = [
            'fecha' => date('Y-m-d'),
            'client_id' => '',
            'cliente_nombre' => '',
            'proyecto' => '',
            'p1' => '',
            'p2' => '',
            'p3' => '',
            'p4' => '',
        ];
        // Initialize autocomplete state for this row
        $index = count($this->rows) - 1;
        $this->searchQueries[$index] = '';
        $this->searchResults[$index] = [];
        $this->showResults[$index] = false;
    }

    public function updatedSearchQueries($value, $key)
    {
        // $key might be "0" or similar
        if (strlen($value) >= 2) {
            $service = app(\App\Services\ClientNormalizationService::class);
            $results = $service->findSimilarClients($value)->take(5);
            
            $this->searchResults[$key] = $results->map(function($item) {
                return [
                    'id' => $item['client']->id,
                    'nombre' => $item['client']->nombre,
                ];
            })->toArray();
            
            $this->showResults[$key] = !empty($this->searchResults[$key]);
        } else {
            $this->searchResults[$key] = [];
            $this->showResults[$key] = false;
        }
    }

    public function selectClient($index, $id, $name)
    {
        $this->rows[$index]['client_id'] = $id;
        $this->rows[$index]['cliente_nombre'] = $name;
        $this->searchQueries[$index] = $name;
        $this->showResults[$index] = false;
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        unset($this->searchQueries[$index]);
        unset($this->searchResults[$index]);
        unset($this->showResults[$index]);
        
        $this->rows = array_values($this->rows);
        $this->searchQueries = array_values($this->searchQueries);
        $this->searchResults = array_values($this->searchResults);
        $this->showResults = array_values($this->showResults);
        
        if (empty($this->rows)) {
            $this->addRow();
        }
    }

    public function clearAll()
    {
        $this->rows = [];
        $this->searchQueries = [];
        $this->searchResults = [];
        $this->showResults = [];
        $this->addRow();
    }

    public function save()
    {
        $this->validate([
            'rows.*.fecha' => 'required|date',
            'rows.*.client_id' => 'required',
            'rows.*.proyecto' => 'required|string',
            'rows.*.p1' => 'required|integer|min:1|max:5',
            'rows.*.p2' => 'required|integer|min:1|max:5',
            'rows.*.p3' => 'required|integer|min:1|max:5',
            'rows.*.p4' => 'required|integer|min:1|max:5',
        ], [
            'rows.*.client_id.required' => 'El cliente es obligatorio en todas las filas.',
            'rows.*.p1.required' => 'Complete todas las calificaciones.',
        ]);

        $calculator = app(ClientSatisfactionCalculator::class);
        $affectedClients = [];
        $datesToCheck = [];

        foreach ($this->rows as $row) {
            $client = Client::find($row['client_id']);
            $clientName = $client ? $client->nombre : 'Desconocido';
            
            // Generar Hash
            $hash = ClientSatisfactionResponse::generateHash(
                $row['fecha'], 
                $clientName, 
                $row['proyecto'], 
                $row['p1'], $row['p2'], $row['p3'], $row['p4']
            );

            if (ClientSatisfactionResponse::existsByHash($hash)) {
                // Skip duplicates silently or warn? For bulk speed, maybe just skip or update?
                // Plan says create. We skip duplicates to avoid double counting.
                continue;
            }

            ClientSatisfactionResponse::create([
                'fecha' => $row['fecha'],
                'client_id' => $row['client_id'],
                'cliente_nombre' => $clientName,
                'proyecto' => $row['proyecto'],
                'pregunta_1' => $row['p1'],
                'pregunta_2' => $row['p2'],
                'pregunta_3' => $row['p3'],
                'pregunta_4' => $row['p4'],
                'hash' => $hash,
            ]);
            
            $affectedClients[$row['client_id']] = true;
            $datesToCheck[$row['fecha']] = true;
        }

        // Recalcular estadisticas
        foreach ($datesToCheck as $date => $val) {
             $calculator->updateMonthlyStats($date, null); // Global
             foreach ($affectedClients as $clientId => $val2) {
                 $calculator->updateMonthlyStats($date, $clientId); // Cliente
             }
        }

        session()->flash('message', 'Datos guardados correctamente.');

        $historyRoute = 'admin.client-satisfaction.index';
        if (auth()->user()->hasRole('Manager')) {
            $historyRoute = 'manager.client-satisfaction.index';
        } elseif (auth()->user()->hasRole('Gestor de Satisfacción Clientes')) {
            $historyRoute = 'client-satisfaction.historial.importacion';
        }
        return redirect()->route($historyRoute);
    }
    
    // Helper para opciones de rating 1-5
    public function getRatingsProperty()
    {
        return [1, 2, 3, 4, 5];
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 via-orange-500 to-amber-500 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
            <div class="px-8 py-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 bg-orange-900/10 rounded-full blur-3xl"></div>
                
                <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white text-xs font-medium backdrop-blur-md mb-2 border border-white/20">
                            <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                            Entrada Manual
                        </div>
                        <h1 class="text-2xl font-bold text-white mb-1">Cargar Satisfacción Clientes</h1>
                        <p class="text-orange-100 text-sm">Registro manual de encuestas de calidad</p>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.client-satisfaction.index' : (auth()->user()->hasRole('Gestor de Satisfacción Clientes') ? 'client-satisfaction.historial.importacion' : 'admin.client-satisfaction.index')) }}" class="inline-flex items-center px-4 py-2 bg-orange-700/30 text-white font-bold rounded-lg hover:bg-orange-700/40 transition-all border border-white/20 backdrop-blur-sm text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <!-- Form Header -->
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Formulario de Entrada Rápida</h3>
                        <p class="text-sm text-gray-500 font-medium">Complete los datos directamente en la tabla para carga masiva.</p>
                    </div>
                    <div>
                        <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.client-satisfaction.import' : (auth()->user()->hasRole('Gestor de Satisfacción Clientes') ? 'client-satisfaction.import' : 'admin.client-satisfaction.import')) }}" class="inline-flex items-center px-6 py-3 bg-indigo-950 border border-transparent rounded-2xl font-bold text-sm text-white shadow-xl hover:bg-indigo-900 transition-all hover:scale-105 active:scale-95">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            Importación Automática Excel
                        </a>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div class="mb-6 flex items-center space-x-3">
                    <button wire:click="addRow" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-orange-500 transition-all shadow-md active:scale-95">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Agregar Fila
                    </button>
                    <button wire:click="clearAll" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-xl font-bold text-xs text-gray-400 uppercase tracking-widest hover:bg-gray-50 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Limpiar Todo
                    </button>
                </div>

                <div class="overflow-x-auto pb-60">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-indigo-900 text-white">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider">#</th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider w-32">Fecha *</th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider w-48">Cliente *</th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider w-40">Proyecto</th>
                                <th class="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider w-32" title="¿Qué grado de satisfacción tiene sobre la obra/producto/servicio terminado?">Satisf. Obra</th>
                                <th class="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider w-32" title="¿Cómo calificaría el servicio en cuanto al desempeño técnico?">Calif. Técnica</th>
                                <th class="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider w-32" title="Durante la ejecución del proyecto, ¿tuvo respuestas a todas sus necesidades?">Resp. Necesidades</th>
                                <th class="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider w-32" title="¿Cómo calificaría el servicio ofrecido en cuanto al plazo de ejecución?">Calif. Plazo</th>
                                <th class="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($rows as $index => $row)
                                <tr>
                                    <td class="px-3 py-2 text-center text-sm text-gray-500">{{ $index + 1 }}</td>
                                    
                                    <td class="px-3 py-2">
                                        <input type="date" wire:model="rows.{{ $index }}.fecha" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error("rows.{$index}.fecha") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    
                                    <td class="px-3 py-2 relative">
                                        <div class="relative">
                                            <input 
                                                type="text" 
                                                wire:model.live.debounce.300ms="searchQueries.{{ $index }}"
                                                placeholder="Buscar Cliente..." 
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm {{ $rows[$index]['client_id'] ? 'bg-green-50 border-green-300 text-green-800 font-semibold' : '' }}"
                                            >
                                            
                                            <!-- Checkmark if selected -->
                                            @if($rows[$index]['client_id'])
                                                <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            @endif
                                            
                                            <!-- Dropdown Results -->
                                            @if(isset($showResults[$index]) && $showResults[$index])
                                                <div class="absolute z-50 mt-1 w-64 bg-white shadow-xl max-h-48 rounded-md text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm left-0">
                                                    @foreach($searchResults[$index] as $result)
                                                        <div 
                                                            wire:click="selectClient({{ $index }}, {{ $result['id'] }}, '{{ $result['nombre'] }}')"
                                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-orange-50"
                                                        >
                                                            <span class="block truncate font-medium">{{ $result['nombre'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error("rows.{$index}.client_id") <span class="text-red-500 text-xs block mt-1">Requerido</span> @enderror
                                    </td>
                                    
                                    <td class="px-3 py-2">
                                        <input type="text" wire:model="rows.{{ $index }}.proyecto" placeholder="Proyecto" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error("rows.{$index}.proyecto") <span class="text-red-500 text-xs">Requerido</span> @enderror
                                    </td>
                                    
                                    @foreach(['p1', 'p2', 'p3', 'p4'] as $p)
                                        <td class="px-3 py-2">
                                            <select wire:model="rows.{{ $index }}.{{ $p }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">Sel...</option>
                                                @foreach([1,2,3,4,5] as $r)
                                                    <option value="{{ $r }}">{{ $r }}</option>
                                                @endforeach
                                            </select>
                                            @error("rows.{$index}.{$p}") <span class="text-red-500 text-xs">*</span> @enderror
                                        </td>
                                    @endforeach
                                    
                                    <td class="px-3 py-2 text-center">
                                        <button wire:click="removeRow({{ $index }})" class="text-red-600 hover:text-red-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex justify-between items-center bg-gray-50 p-4 rounded-lg border border-gray-100">
                     <button wire:click="save" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Guardar Datos
                    </button>
                    
                    <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.client-satisfaction.index' : (auth()->user()->hasRole('Gestor de Satisfacción Clientes') ? 'client-satisfaction.historial.importacion' : 'admin.client-satisfaction.index')) }}" class="ml-4 inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancelar
                    </a>
                </div>
            </div>

            <!-- Instrucciones -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                     <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Información sobre la entrada manual</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Los campos marcados con * son obligatorios.</li>
                                <li>Las calificaciones deben ser valores entre 1 y 5.</li>
                                <li>Puede agregar tantas filas como necesite antes de guardar (Bulk Insert).</li>
                                <li>Los datos se guardarán todos juntos al presionar "Guardar Datos".</li>
                                <li>El sistema detectará duplicados automáticamente y no los duplicará.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
