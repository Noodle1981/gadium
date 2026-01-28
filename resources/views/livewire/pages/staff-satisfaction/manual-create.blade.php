<?php

use Livewire\Volt\Component;
use App\Models\StaffSatisfactionResponse;
use App\Services\StaffSatisfactionCalculator;
use Carbon\Carbon;

new class extends Component {
    public $rows = [];
    public $fecha;
    public $numPersonas = 10;
    public $showSuccessModal = false;
    public $savedCount = 0;
    public $isStarted = false;

    // Autocomplete State per Row
    public $searchQueries = [];
    public $searchResults = [];
    public $showResults = [];

    public function mount()
    {
        $this->fecha = now()->format('Y-m-d');
        // Start with empty rows
        $this->rows = [];
    }

    public function startSurvey()
    {
        $this->rows = [];
        $this->searchQueries = [];
        $this->searchResults = [];
        $this->showResults = [];
        $this->isStarted = true;
        
        // Add first row automatically with ID 1
        $this->addRow(1);
    }

    public function addRow($personalId = null)
    {
        $this->rows[] = [
            'personal' => $personalId ? (string)$personalId : '',
            'p1_mal' => false, 'p1_normal' => false, 'p1_bien' => false,
            'p2_mal' => false, 'p2_normal' => false, 'p2_bien' => false,
            'p3_mal' => false, 'p3_normal' => false, 'p3_bien' => false,
            'p4_mal' => false, 'p4_normal' => false, 'p4_bien' => false,
        ];
        
        $index = count($this->rows) - 1;
        $this->searchQueries[$index] = $personalId ? (string)$personalId : '';
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
                
                // SYNC: Always update rows[index]['personal'] with searchQueries value
                $this->rows[$key]['personal'] = $value;
                
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

        // Handle checkbox logic & auto-advance
        if (str_starts_with($propertyName, 'rows.')) {
            $parts = explode('.', $propertyName);
            if (count($parts) === 3 && str_starts_with($parts[2], 'p')) {
                $index = (int)$parts[1];
                $field = $parts[2];
                
                // Mutual exclusivity
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

                // Check if row is complete
                $row = $this->rows[$index];
                $hasP1 = $row['p1_mal'] || $row['p1_normal'] || $row['p1_bien'];
                $hasP2 = $row['p2_mal'] || $row['p2_normal'] || $row['p2_bien'];
                $hasP3 = $row['p3_mal'] || $row['p3_normal'] || $row['p3_bien'];
                $hasP4 = $row['p4_mal'] || $row['p4_normal'] || $row['p4_bien'];

                // If this is the last row AND it's complete AND we haven't reached the limit
                // AND personal field is not empty (it should be pre-filled)
                if ($index === count($this->rows) - 1 
                    && $hasP1 && $hasP2 && $hasP3 && $hasP4 
                    && count($this->rows) < $this->numPersonas) {
                    
                    // Add next row automatically with incremented ID
                    $nextId = $index + 2; // Index 0 -> ID 1. Next is Index 1 -> ID 2.
                    $this->addRow($nextId);
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
        // First: Check for duplicates within the form BEFORE Laravel validation
        $formDuplicates = [];
        $seenPersonal = [];
        
        foreach ($this->rows as $index => $row) {
            $personal = trim($row['personal'] ?? '');
            
            if (empty($personal)) continue;
            
            // Check if this personal name was already used in another row
            if (isset($seenPersonal[$personal])) {
                $formDuplicates[] = "Fila " . ($index + 1) . ": El operario '{$personal}' está duplicado en la fila {$seenPersonal[$personal]}";
            } else {
                $seenPersonal[$personal] = $index + 1;
            }
        }
        
        // If there are duplicates within the form, show error and stop
        if (!empty($formDuplicates)) {
            session()->flash('error', implode('<br>', $formDuplicates));
            return;
        }
        
        // Second: Validate basic fields with Laravel
        $this->validate([
            'fecha' => 'required|date',
            'rows.*.personal' => 'required|string',
        ], [
            'rows.*.personal.required' => 'El nombre del operario es obligatorio',
        ]);

        $errors = [];
        $duplicates = [];
        $count = 0;
        
        // Third: Validate and save
        foreach ($this->rows as $index => $row) {
            if (empty($row['personal'])) continue;
            
            // Validate that at least one checkbox is selected per question
            $hasP1 = $row['p1_mal'] || $row['p1_normal'] || $row['p1_bien'];
            $hasP2 = $row['p2_mal'] || $row['p2_normal'] || $row['p2_bien'];
            $hasP3 = $row['p3_mal'] || $row['p3_normal'] || $row['p3_bien'];
            $hasP4 = $row['p4_mal'] || $row['p4_normal'] || $row['p4_bien'];
            
            if (!$hasP1 || !$hasP2 || !$hasP3 || !$hasP4) {
                $errors[] = "Fila " . ($index + 1) . " ({$row['personal']}): Debe responder todas las preguntas";
                continue;
            }

            $data = array_merge($row, ['fecha' => $this->fecha]);
            
            // Check if this operario already has a response for this date in database
            $existingResponse = StaffSatisfactionResponse::where('personal', $row['personal'])
                ->whereDate('fecha', $this->fecha)
                ->first();
            
            if ($existingResponse) {
                $duplicates[] = "El operario '{$row['personal']}' ya tiene una encuesta registrada para la fecha {$this->fecha}";
                continue;
            }
            
            $hash = StaffSatisfactionResponse::generateHash($data);

            if (!StaffSatisfactionResponse::existsByHash($hash)) {
                $data['hash'] = $hash;
                
                try {
                    StaffSatisfactionResponse::create($data);
                    $count++;
                } catch (\Exception $e) {
                    \Log::error("Error al crear registro de satisfacción personal: " . $e->getMessage());
                    $errors[] = "Error al guardar {$row['personal']}: " . $e->getMessage();
                }
            }
        }

        // Show errors if any
        if (!empty($errors) || !empty($duplicates)) {
            $allErrors = array_merge($errors, $duplicates);
            session()->flash('error', implode('<br>', $allErrors));
            return;
        }

        // If no records were saved
        if ($count === 0) {
            session()->flash('error', 'No se guardó ningún registro. Verifica que todos los campos estén completos.');
            return;
        }

        // Recalculate global stats for the month
        try {
            app(StaffSatisfactionCalculator::class)->updateMonthlyStats(Carbon::parse($this->fecha)->format('Y-m'));
        } catch (\Exception $e) {
            \Log::error('Error al actualizar stats de satisfacción personal: ' . $e->getMessage());
        }

        // Show success modal
        $this->savedCount = $count;
        $this->showSuccessModal = true;
    }

    public function continueAdding()
    {
        $this->showSuccessModal = false;
        $this->reset(['rows']);
        $this->addRow();
    }

    public function goToHistory()
    {
        return redirect()->route('app.staff-satisfaction.surveys');
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
                        <!-- <a href="{{ route('staff-satisfaction.import') }}" class="inline-flex items-center px-4 py-2 bg-white text-orange-600 font-bold rounded-lg hover:bg-orange-50 transition-all shadow-md active:scale-95 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            Importar Excel
                        </a> -->
                        <a href="{{ route('app.staff-satisfaction.surveys') }}" class="inline-flex items-center px-4 py-2 bg-orange-700/30 text-white font-bold rounded-lg hover:bg-orange-700/40 transition-all border border-white/20 backdrop-blur-sm text-sm">
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
            
            <div class="mb-8 rounded-2xl bg-white shadow-lg border border-gray-50 overflow-hidden">
                <div class="p-6">
                    @if (session()->has('message'))
                        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-center text-green-700">
                            <div class="p-2 bg-green-100 rounded-lg mr-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                            <span class="font-bold">{{ session('message') }}</span>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 p-2 bg-red-100 rounded-lg mr-4">
                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-sm mb-1">Error al guardar</h3>
                                    <div class="text-sm">{!! session('error') !!}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @error('rows.*.personal')
                        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 flex items-center text-red-700">
                            <div class="p-2 bg-red-100 rounded-lg mr-4">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-bold">{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Fecha de Encuesta -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 ml-1">Fecha de Encuesta</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-orange-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <input type="date" wire:model="fecha" class="pl-11 block w-full bg-gray-50 border-gray-200 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm transition-all py-2.5">
                            </div>
                        </div>

                        <!-- Cantidad de Personas -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 ml-1">Cantidad de Personas</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-orange-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <input type="number" wire:model="numPersonas" min="1" max="100" 
                                       @if($isStarted) disabled @endif
                                       class="pl-11 block w-full bg-gray-50 border-gray-200 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm transition-all py-2.5 {{ $isStarted ? 'opacity-50 cursor-not-allowed' : '' }}">
                            </div>
                        </div>

                        <!-- Botón Iniciar -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 ml-1 invisible md:visible">Acción</label>
                            @if(!$isStarted)
                                <button wire:click="startSurvey" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-gradient-to-r from-orange-600 to-orange-500 text-white font-bold rounded-xl hover:from-orange-700 hover:to-orange-600 transition-all shadow-md active:scale-95">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Iniciar Encuestas
                                </button>
                            @else
                                <div class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-gray-100 text-gray-500 font-bold rounded-xl cursor-not-allowed">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    En Progreso...
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($isStarted)
                        <div class="mt-4 flex items-center justify-between bg-blue-50 p-3 rounded-lg border border-blue-100">
                            <div class="flex items-center space-x-2 text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-sm font-medium">Progreso: {{ count($rows) }} de {{ $numPersonas }} encuestas</span>
                            </div>
                            <div class="w-1/3 bg-blue-200 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ (count($rows) / max(1, $numPersonas)) * 100 }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-1 pb-60 overflow-x-auto min-h-[300px]">
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
                                        value="{{ $rows[$index]['personal'] }}"
                                        readonly
                                        class="block w-40 border-gray-200 bg-gray-50 rounded-md shadow-sm text-center font-bold text-gray-600 sm:text-xs focus:ring-0 cursor-default"
                                    >
                                </div>
                            </td>

                            <!-- P1 -->
                            <td class="px-1 py-2 text-center bg-blue-50 border-l border-blue-100"><input type="checkbox" wire:model.live="rows.{{ $index }}.p1_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-blue-50"><input type="checkbox" wire:model.live="rows.{{ $index }}.p1_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-blue-50"><input type="checkbox" wire:model.live="rows.{{ $index }}.p1_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <!-- P2 -->
                            <td class="px-1 py-2 text-center bg-green-50 border-l border-green-100"><input type="checkbox" wire:model.live="rows.{{ $index }}.p2_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-green-50"><input type="checkbox" wire:model.live="rows.{{ $index }}.p2_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-green-50"><input type="checkbox" wire:model.live="rows.{{ $index }}.p2_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <!-- P3 -->
                            <td class="px-1 py-2 text-center bg-yellow-50 border-l border-yellow-100"><input type="checkbox" wire:model.live="rows.{{ $index }}.p3_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-yellow-50"><input type="checkbox" wire:model.live="rows.{{ $index }}.p3_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-yellow-50"><input type="checkbox" wire:model.live="rows.{{ $index }}.p3_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <!-- P4 -->
                            <td class="px-1 py-2 text-center bg-purple-50 border-l border-purple-100"><input type="checkbox" wire:model.live="rows.{{ $index }}.p4_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-purple-50"><input type="checkbox" wire:model.live="rows.{{ $index }}.p4_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-purple-50"><input type="checkbox" wire:model.live="rows.{{ $index }}.p4_bien" class="rounded text-green-600 focus:ring-green-500"></td>

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

        <div class="m-4 flex justify-between">
            

            <button wire:click="save" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                Guardar Todo
            </button>
        </div>
    </div>

    <!-- Success Modal -->
    @if($showSuccessModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="continueAdding"></div>

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 px-6 pt-6 pb-4">
                        <div class="flex items-center justify-center">
                            <div class="flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-green-100 sm:h-20 sm:w-20">
                                <svg class="h-10 w-10 text-green-600 sm:h-12 sm:w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 pt-4 pb-6">
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2" id="modal-title">
                                ¡Guardado Exitoso!
                            </h3>
                            <div class="mt-3">
                                <p class="text-lg text-gray-600">
                                    Se {{ $savedCount === 1 ? 'guardó' : 'guardaron' }} 
                                    <span class="font-bold text-green-600">{{ $savedCount }}</span> 
                                    {{ $savedCount === 1 ? 'registro' : 'registros' }} correctamente
                                </p>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row gap-3">
                            <button 
                                wire:click="continueAdding" 
                                type="button" 
                                class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Continuar Agregando
                            </button>
                            
                            <button 
                                wire:click="goToHistory" 
                                type="button" 
                                class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Ver Historial
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
