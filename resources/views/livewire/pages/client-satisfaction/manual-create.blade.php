<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Client;
use App\Models\ClientSatisfactionResponse;
use App\Services\ClientSatisfactionCalculator;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    
    public $rows = [];
    public $clients = [];
    
    public function mount()
    {
        $this->clients = Client::orderBy('nombre')->get();
        // Iniciar con una fila vacía
        $this->addRow();
    }

    public function addRow()
    {
        $this->rows[] = [
            'fecha' => date('Y-m-d'),
            'client_id' => '',
            'cliente_nombre' => '', // Para inputs de texto si no usa select
            'proyecto' => '',
            'p1' => '', // Satisfacción Obra
            'p2' => '', // Desempeño Técnico
            'p3' => '', // Necesidades
            'p4' => '', // Plazo
        ];
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows); // Reindexar
        
        if (empty($this->rows)) {
            $this->addRow();
        }
    }

    public function clearAll()
    {
        $this->rows = [];
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
        return redirect()->route('client-satisfaction.dashboard');
    }
    
    // Helper para opciones de rating 1-5
    public function getRatingsProperty()
    {
        return [1, 2, 3, 4, 5];
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Entrada Manual de Satisfacción') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Formulario de Entrada Rápida</h3>
                    <p class="text-sm text-gray-500">Complete los datos directamente en la tabla. Puede agregar múltiples filas y guardar todas a la vez.</p>
                </div>

                <div class="mb-4 flex space-x-2">
                    <button wire:click="addRow" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 active:bg-orange-700 focus:outline-none focus:border-orange-700 focus:ring focus:ring-orange-200 disabled:opacity-25 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Agregar Fila
                    </button>
                    <button wire:click="clearAll" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Limpiar Todo
                    </button>
                </div>

                <div class="overflow-x-auto">
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
                                    
                                    <td class="px-3 py-2">
                                        <select wire:model="rows.{{ $index }}.client_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Seleccionar...</option>
                                            @foreach($clients as $c)
                                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error("rows.{$index}.client_id") <span class="text-red-500 text-xs">Requerido</span> @enderror
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
                    
                    <a href="{{ route('client-satisfaction.dashboard') }}" class="ml-4 inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
