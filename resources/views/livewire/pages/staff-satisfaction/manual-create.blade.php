<?php

use Livewire\Volt\Component;
use App\Models\StaffSatisfactionResponse;
use App\Services\StaffSatisfactionCalculator;
use Carbon\Carbon;

new class extends Component {
    public $rows = [];
    public $fecha;

    // Autocomplete State per Row
    public $searchQueries = [];
    public $searchResults = [];
    public $showResults = [];

    public function mount()
    {
        $this->fecha = now()->format('Y-m-d');
        $this->addRow();
    }

    public function addRow()
    {
        $this->rows[] = [
            'personal' => '',
            'p1_mal' => false, 'p1_normal' => false, 'p1_bien' => false,
            'p2_mal' => false, 'p2_normal' => false, 'p2_bien' => false,
            'p3_mal' => false, 'p3_normal' => false, 'p3_bien' => false,
            'p4_mal' => false, 'p4_normal' => false, 'p4_bien' => false,
        ];
        
        $index = count($this->rows) - 1;
        $this->searchQueries[$index] = '';
        $this->searchResults[$index] = [];
        $this->showResults[$index] = false;
    }

    public function updated($propertyName)
    {
        // Handle autocomplete
        if (str_starts_with($propertyName, 'searchQueries.')) {
            $parts = explode('.', $propertyName);
            if (isset($parts[1])) {
                $key = $parts[1];
                $value = $this->searchQueries[$key];
                
                if (strlen($value) > 1) {
                    $names = \App\Models\HourDetail::select('personal')
                        ->distinct()
                        ->where('personal', 'like', '%' . $value . '%')
                        ->limit(5)
                        ->pluck('personal');
                    
                    $this->searchResults[$key] = $names->map(function($name) {
                        return [
                            'id' => md5($name),
                            'nombre' => $name,
                        ];
                    })->toArray();
                    
                    $this->showResults[$key] = !empty($this->searchResults[$key]);
                } else {
                    $this->searchResults[$key] = [];
                    $this->showResults[$key] = false;
                }
            }
        }

        // Handle checkbox mutual exclusivity in rows
        if (str_starts_with($propertyName, 'rows.')) {
            $parts = explode('.', $propertyName);
            if (count($parts) === 3 && str_starts_with($parts[2], 'p')) {
                $index = $parts[1];
                $field = $parts[2];
                
                if ($this->rows[$index][$field] === true) {
                    $group = substr($field, 0, 2); // p1, p2, p3, p4
                    $options = ['mal', 'normal', 'bien'];
                    foreach ($options as $opt) {
                        $otherKey = $group . '_' . $opt;
                        if ($otherKey !== $field) {
                            $this->rows[$index][$otherKey] = false;
                        }
                    }
                }
            }
        }
    }

    public function selectPersonal($index, $name)
    {
        $this->rows[$index]['personal'] = $name;
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
        
        if (empty($this->rows)) $this->addRow();
    }

    public function save()
    {
        $this->validate([
            'fecha' => 'required|date',
            'rows.*.personal' => 'required|string',
        ]);

        $count = 0;
        foreach ($this->rows as $row) {
            if (empty($row['personal'])) continue;
            
            // Ensure single select per question logic if needed? 
            // The excel allows X in one box per question usually.
            // We'll proceed with saving as is.

            $data = array_merge($row, ['fecha' => $this->fecha]);
            
            $hash = StaffSatisfactionResponse::generateHash($data);

            if (!StaffSatisfactionResponse::existsByHash($hash)) {
                $data['hash'] = $hash;
                StaffSatisfactionResponse::create($data);
                $count++;
            }
        }

        // Recalculate global stats for the month
        app(StaffSatisfactionCalculator::class)->updateMonthlyStats(Carbon::parse($this->fecha)->format('Y-m'));

        session()->flash('message', "Se guardaron {$count} registros correctamente.");
        $this->reset(['rows']);
        $this->addRow();
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
                            <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"/></svg>
                            Ingreso de Datos
                        </div>
                        <h1 class="text-2xl font-bold text-white mb-1">Encuesta Personal</h1>
                        <p class="text-orange-100 text-sm">Registro manual de satisfacción de operarios</p>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('staff-satisfaction.import') }}" class="inline-flex items-center px-4 py-2 bg-white text-orange-600 font-bold rounded-lg hover:bg-orange-50 transition-all shadow-md active:scale-95 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            Importar Excel
                        </a>
                        <a href="{{ route('staff-satisfaction.historial.importacion') }}" class="inline-flex items-center px-4 py-2 bg-orange-700/30 text-white font-bold rounded-lg hover:bg-orange-700/40 transition-all border border-white/20 backdrop-blur-sm text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8 rounded-2xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6">
                    @if (session()->has('message'))
                        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-center text-green-700">
                            <div class="p-2 bg-green-100 rounded-lg mr-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                            <span class="font-bold">{{ session('message') }}</span>
                        </div>
                    @endif

                    <div class="w-64 space-y-2">
                        <label class="block text-sm font-bold text-gray-700 ml-1">Fecha de Encuesta</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-orange-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <input type="date" wire:model="fecha" class="pl-11 block w-full bg-gray-50 border-gray-200 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm transition-all py-2.5">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-1 pb-60 overflow-x-auto min-h-[500px]">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">Personal</th>
                        
                        <th colspan="3" class="px-1 py-1 text-center text-xs font-bold bg-blue-50 border-l border-blue-200">1. Trato Jefe</th>
                        <th colspan="3" class="px-1 py-1 text-center text-xs font-bold bg-green-50 border-l border-green-200">2. Trato Comp.</th>
                        <th colspan="3" class="px-1 py-1 text-center text-xs font-bold bg-yellow-50 border-l border-yellow-200">3. Clima</th>
                        <th colspan="3" class="px-1 py-1 text-center text-xs font-bold bg-purple-50 border-l border-purple-200">4. Comodidad</th>
                        
                        <th class="relative px-3 py-2"><span class="sr-only">Borrar</span></th>
                    </tr>
                    <tr>
                        <th class="px-3 py-1 sticky left-0 bg-gray-50 z-10"></th>
                        
                        <th class="text-[10px] text-center text-red-600 bg-blue-50 border-l border-blue-200">Mal</th>
                        <th class="text-[10px] text-center text-yellow-600 bg-blue-50">Norm</th>
                        <th class="text-[10px] text-center text-green-600 bg-blue-50">Bien</th>

                        <th class="text-[10px] text-center text-red-600 bg-green-50 border-l border-green-200">Mal</th>
                        <th class="text-[10px] text-center text-yellow-600 bg-green-50">Norm</th>
                        <th class="text-[10px] text-center text-green-600 bg-green-50">Bien</th>

                        <th class="text-[10px] text-center text-red-600 bg-yellow-50 border-l border-yellow-200">Mal</th>
                        <th class="text-[10px] text-center text-yellow-600 bg-yellow-50">Norm</th>
                        <th class="text-[10px] text-center text-green-600 bg-yellow-50">Bien</th>

                        <th class="text-[10px] text-center text-red-600 bg-purple-50 border-l border-purple-200">Incó</th>
                        <th class="text-[10px] text-center text-yellow-600 bg-purple-50">Norm</th>
                        <th class="text-[10px] text-center text-green-600 bg-purple-50">Cóm</th>
                        
                        <th></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rows as $index => $row)
                        <tr>
                            <td class="px-3 py-2 sticky left-0 bg-white z-10 shadow-sm">
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        wire:model.live.debounce.300ms="searchQueries.{{ $index }}"
                                        placeholder="Nombre Operario" 
                                        class="block w-40 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs {{ $rows[$index]['personal'] ? 'bg-green-50 border-green-300 text-green-900 font-semibold' : '' }}"
                                    >
                                    
                                    @if(isset($showResults[$index]) && $showResults[$index])
                                        <div class="absolute z-50 mt-1 w-48 bg-white shadow-xl max-h-48 rounded-md text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm left-0">
                                            @foreach($searchResults[$index] as $result)
                                                <div 
                                                    wire:click="selectPersonal({{ $index }}, '{{ $result['nombre'] }}')"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-2 hover:bg-orange-50 text-xs"
                                                >
                                                    <span class="block truncate font-medium">{{ $result['nombre'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- P1 -->
                            <td class="px-1 py-2 text-center bg-blue-50 border-l border-blue-100"><input type="checkbox" wire:model="rows.{{ $index }}.p1_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-blue-50"><input type="checkbox" wire:model="rows.{{ $index }}.p1_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-blue-50"><input type="checkbox" wire:model="rows.{{ $index }}.p1_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <!-- P2 -->
                            <td class="px-1 py-2 text-center bg-green-50 border-l border-green-100"><input type="checkbox" wire:model="rows.{{ $index }}.p2_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-green-50"><input type="checkbox" wire:model="rows.{{ $index }}.p2_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-green-50"><input type="checkbox" wire:model="rows.{{ $index }}.p2_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <!-- P3 -->
                            <td class="px-1 py-2 text-center bg-yellow-50 border-l border-yellow-100"><input type="checkbox" wire:model="rows.{{ $index }}.p3_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-yellow-50"><input type="checkbox" wire:model="rows.{{ $index }}.p3_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-yellow-50"><input type="checkbox" wire:model="rows.{{ $index }}.p3_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <!-- P4 -->
                            <td class="px-1 py-2 text-center bg-purple-50 border-l border-purple-100"><input type="checkbox" wire:model="rows.{{ $index }}.p4_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-purple-50"><input type="checkbox" wire:model="rows.{{ $index }}.p4_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-purple-50"><input type="checkbox" wire:model="rows.{{ $index }}.p4_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <td class="px-3 py-2 text-center">
                                <button wire:click="removeRow({{ $index }})" class="text-red-600 hover:text-red-900">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-between">
            <button wire:click="addRow" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                + Agregar Fila
            </button>

            <button wire:click="save" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                Guardar Todo
            </button>
        </div>
    </div>
</div>
