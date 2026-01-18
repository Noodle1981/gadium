<?php

use Livewire\Volt\Component;
use App\Models\HourDetail;

new class extends Component {
    // 1. Datos Principales
    public $fecha = '';
    public $personal = '';
    public $funcion = '';
    public $proyecto = '';
    public $dia = '';

    // Autocomplete State
    public $personalSearchResults = [];
    public $showPersonalSearch = false;
    public $isNewPersonal = false;
    public $selectedPersonalHash = null;

    // 2. Métricas
    public $hs = 0;
    public $ponderador = 1;
    public $horas_ponderadas = 0;
    public $hs_comun = 0;
    public $hs_50 = 0;
    public $hs_100 = 0;
    public $hs_viaje = 0;
    public $hs_adeudadas = 0;
    public $hs_pernoctada = 'No';
    
    // 3. Otros
    public $vianda = '0';
    public $observacion = '';
    public $programacion = '';
    
    public $showMoreDetails = true;

    public function mount()
    {
        $this->fecha = date('Y-m-d');
        $this->updateDia();
    }
    
    public function updateDia()
    {
        if ($this->fecha) {
            setlocale(LC_TIME, 'es_ES.UTF-8');
            $this->dia = strtoupper(\Carbon\Carbon::parse($this->fecha)->locale('es')->dayName);
        }
    }

    public function updatedFecha()
    {
        $this->updateDia();
    }
    
    public function updatedHs()
    {
        $this->calculateWeighted();
    }
    
    public function updatedPonderador()
    {
        $this->calculateWeighted();
    }
    
    public function updatedPersonal($value)
    {
        $this->isNewPersonal = false;
        $this->selectedPersonalHash = null;

        if (strlen($value) > 1) {
            $names = HourDetail::select('personal')
                ->distinct()
                ->where('personal', 'like', '%' . $value . '%')
                ->limit(10)
                ->pluck('personal');
            
            $this->personalSearchResults = $names->map(function($name) {
                return [
                    'id' => md5($name),
                    'nombre' => $name,
                ];
            })->toArray();
            
            $this->showPersonalSearch = !empty($this->personalSearchResults);
        } else {
            $this->personalSearchResults = [];
            $this->showPersonalSearch = false;
        }
    }

    public function selectPersonal($name)
    {
        $this->personal = $name;
        $this->selectedPersonalHash = md5($name);
        $this->showPersonalSearch = false;
        $this->personalSearchResults = [];
        $this->isNewPersonal = false;
    }
    
    public function markAsNewPersonal()
    {
        $this->selectedPersonalHash = null;
        $this->isNewPersonal = true;
        $this->showPersonalSearch = false;
        $this->personalSearchResults = [];
    }

    public function calculateWeighted()
    {
        $hs = is_numeric($this->hs) ? (float)$this->hs : 0;
        $ponderador = is_numeric($this->ponderador) ? (float)$this->ponderador : 1;
        
        $this->horas_ponderadas = $hs * $ponderador;
    }

    public function rules()
    {
        return [
            'fecha' => 'required|date',
            'personal' => 'required|string|max:255',
            'funcion' => 'required|string|max:255',
            'proyecto' => 'required|string|max:255',
            'hs' => 'required|numeric|min:0',
            'ponderador' => 'required|numeric',
            'hs_comun' => 'nullable|numeric',
            'hs_50' => 'nullable|numeric',
            'hs_100' => 'nullable|numeric',
            'hs_viaje' => 'nullable|numeric',
            'hs_adeudadas' => 'nullable|numeric',
        ];
    }

    public function save()
    {
        $this->validate();

        // Check for duplicates
        $hash = HourDetail::generateHash($this->fecha, $this->personal, $this->proyecto, (float)$this->hs);

        if (HourDetail::existsByHash($hash)) {
            $this->addError('proyecto', 'Ya existe un registro idéntico (Fecha + Personal + Proyecto + Horas).');
            return;
        }

        $date = \Carbon\Carbon::parse($this->fecha);

        HourDetail::create([
            'dia' => $this->dia,
            'fecha' => $this->fecha,
            'ano' => $date->year,
            'mes' => $date->month,
            'personal' => $this->personal,
            'funcion' => $this->funcion,
            'proyecto' => $this->proyecto,
            'horas_ponderadas' => $this->horas_ponderadas,
            'ponderador' => $this->ponderador,
            'hs' => $this->hs,
            'hs_comun' => $this->hs_comun ?: 0,
            'hs_50' => $this->hs_50 ?: 0,
            'hs_100' => $this->hs_100 ?: 0,
            'hs_viaje' => $this->hs_viaje ?: 0,
            'hs_pernoctada' => $this->hs_pernoctada,
            'hs_adeudadas' => $this->hs_adeudadas ?: 0,
            'vianda' => $this->vianda,
            'observacion' => $this->observacion,
            'programacion' => $this->programacion,
            'hash' => $hash,
        ]);

        session()->flash('success', '¡Registro de horas creado exitosamente!');
        
        $this->reset(['personal', 'funcion', 'proyecto', 'hs', 'horas_ponderadas', 'hs_comun', 'hs_50', 'hs_100', 'hs_viaje', 'hs_adeudadas', 'observacion', 'programacion']);
        $this->vianda = '0';
        $this->hs_pernoctada = 'No';
        $this->ponderador = 1;
        $this->fecha = date('Y-m-d');
        $this->updateDia();
        $this->personalSuggestions = [];
    }
}; ?>

<div>


    <x-slot name="header">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-lg overflow-hidden -mx-6 sm:-mx-8">
                <div class="px-8 py-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2 tracking-tight">Cargar Horas Manualmente</h1>
                        <p class="text-orange-100 text-lg font-medium opacity-90">Registro detallado de actividad y productividad</p>
                    </div>
                     <!-- Botón Importación Automática -->
                     <div class="hidden md:flex items-center">
                        <a href="{{ route('hours.import') }}" 
                           class="inline-flex items-center px-4 py-2 bg-white text-orange-600 rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-orange-600 transition ease-in-out duration-150 shadow-lg transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Importación Automática Excel
                        </a>
                    </div>
                </div>
                </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Botón CTA Importación Automática (Mobile Fallback) -->
             <div class="mb-8 flex justify-end md:hidden">
                <a href="{{ route('hours.import') }}" 
                   class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Importación Automática Excel
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-t-4 border-orange-500">
                
                @if (session()->has('success'))
                    <div class="mb-6 bg-orange-50 border-l-4 border-orange-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-orange-700 font-bold">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form wire:submit="save" class="space-y-8">
                    
                    <!-- 1. CAMPOS PRINCIPALES -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-orange-400"></div>
                        <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2 flex items-center">
                            <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">1</span>
                            Datos Principales
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Fecha -->
                            <div class="relative group">
                                <label for="fecha" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-orange-600 transition-colors">Fecha *</label>
                                <input type="date" wire:model.live="fecha" id="fecha" 
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm transition-shadow">
                                <p class="mt-1 text-xs text-orange-600 font-medium">Día: {{ $dia }}</p>
                                @error('fecha') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Personal (Autocomplete) -->
                            <div class="relative group">
                                <label for="personal" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-orange-600 transition-colors">Personal *</label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="personal" id="personal" placeholder="Nombre completo" autocomplete="off"
                                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm transition-shadow {{ $selectedPersonalHash ? 'bg-green-50 border-green-300 text-green-800' : '' }}">
                                    
                                    <!-- Badge: Existente -->
                                    @if($selectedPersonalHash)
                                        <span class="absolute right-2 top-3 px-2 py-1 bg-green-100 text-green-800 text-xs rounded font-medium">
                                            ✓ Existente
                                        </span>
                                    @endif
                                    
                                    <!-- Badge: Nuevo -->
                                    @if($isNewPersonal)
                                        <span class="absolute right-2 top-3 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded font-medium">
                                            ⊕ Nuevo
                                        </span>
                                    @endif

                                    @if($showPersonalSearch)
                                        <div class="absolute z-50 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200">
                                            <ul class="max-h-60 overflow-auto py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                                                @if(!empty($personalSearchResults))
                                                    @foreach($personalSearchResults as $result)
                                                        <li wire:click="selectPersonal('{{ $result['nombre'] }}')" 
                                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-orange-50 text-gray-900 group-result">
                                                            <span class="block truncate font-medium">
                                                                {{ $result['nombre'] }}
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                @else
                                                     <li class="py-3 px-4 text-sm text-gray-500 text-center">
                                                        No se encontraron coincidencias
                                                    </li>
                                                @endif
                                                
                                                <!-- Opción: Crear nuevo -->
                                                <li wire:click="markAsNewPersonal"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 bg-blue-50 hover:bg-blue-100 border-t border-blue-200 text-blue-700 font-medium">
                                                    <span class="block">⊕ Crear nuevo: "{{ $personal }}"</span>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Escriba para buscar existentes</p>
                                @error('personal') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Función -->
                            <div class="relative group">
                                <label for="funcion" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-orange-600 transition-colors">Función *</label>
                                <input type="text" wire:model="funcion" id="funcion" 
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm transition-shadow">
                                @error('funcion') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Proyecto -->
                            <div class="md:col-span-3 relative group">
                                <label for="proyecto" class="block text-sm font-semibold text-gray-700 mb-1 group-hover:text-orange-600 transition-colors">Proyecto/Obra *</label>
                                <input type="text" wire:model="proyecto" id="proyecto" placeholder="Código o Nombre del Proyecto" 
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm transition-shadow">
                                @error('proyecto') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 2. MÉTRICAS DE HORAS -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-orange-400"></div>
                        <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2 flex items-center">
                            <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">2</span>
                            Métricas de Horas
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Hs Totales -->
                            <div>
                                <label for="hs" class="block text-sm font-semibold text-gray-700 mb-1">Hs Totales *</label>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" step="0.01" wire:model.live="hs" id="hs" 
                                           class="block w-full rounded-lg border-gray-300 pl-4 focus:border-orange-500 focus:ring-orange-500 sm:text-sm bg-white font-bold text-gray-900">
                                </div>
                                @error('hs') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Ponderador -->
                            <div>
                                <label for="ponderador" class="block text-sm font-semibold text-gray-700 mb-1">Ponderador</label>
                                <input type="number" step="0.0001" wire:model.live="ponderador" id="ponderador" 
                                       class="block w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                @error('ponderador') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                            </div>
                            
                             <!-- Horas Ponderadas (Calculado) -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Horas Ponderadas</label>
                                <div class="mt-1 p-2 bg-orange-50 rounded-lg text-orange-700 border border-orange-200 font-mono font-bold text-right shadow-inner">
                                    {{ number_format((float)$horas_ponderadas, 4) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200">
                             <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Desglose Opcional</p>
                             <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                <div>
                                    <label for="hs_comun" class="block text-xs font-medium text-gray-500">Hs Común</label>
                                    <input type="number" step="0.01" wire:model="hs_comun" id="hs_comun" class="mt-1 block w-full rounded-md border-gray-300 text-xs focus:border-orange-500 focus:ring-orange-500">
                                </div>
                                <div>
                                    <label for="hs_50" class="block text-xs font-medium text-gray-500">Hs 50%</label>
                                    <input type="number" step="0.01" wire:model="hs_50" id="hs_50" class="mt-1 block w-full rounded-md border-gray-300 text-xs focus:border-orange-500 focus:ring-orange-500">
                                </div>
                                <div>
                                    <label for="hs_100" class="block text-xs font-medium text-gray-500">Hs 100%</label>
                                    <input type="number" step="0.01" wire:model="hs_100" id="hs_100" class="mt-1 block w-full rounded-md border-gray-300 text-xs focus:border-orange-500 focus:ring-orange-500">
                                </div>
                                <div>
                                    <label for="hs_viaje" class="block text-xs font-medium text-gray-500">Hs Viaje</label>
                                    <input type="number" step="0.01" wire:model="hs_viaje" id="hs_viaje" class="mt-1 block w-full rounded-md border-gray-300 text-xs focus:border-orange-500 focus:ring-orange-500">
                                </div>
                                <div>
                                    <label for="hs_adeudadas" class="block text-xs font-medium text-gray-500">Hs Adeudadas</label>
                                    <input type="number" step="0.01" wire:model="hs_adeudadas" id="hs_adeudadas" class="mt-1 block w-full rounded-md border-gray-300 text-xs focus:border-orange-500 focus:ring-orange-500">
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- 3. OTROS DETALLES -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-orange-400"></div>
                        <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-200 pb-2 flex items-center">
                            <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">3</span>
                            Información Adicional
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <div>
                                <label for="vianda" class="block text-sm font-semibold text-gray-700 mb-1">Vianda</label>
                                <select wire:model="vianda" id="vianda" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="hs_pernoctada" class="block text-sm font-semibold text-gray-700 mb-1">Pernoctada</label>
                                <select wire:model="hs_pernoctada" id="hs_pernoctada" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="No">No</option>
                                    <option value="Sí">Sí</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="programacion" class="block text-sm font-semibold text-gray-700 mb-1">Programación</label>
                                <input type="text" wire:model="programacion" id="programacion" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>

                            <div class="md:col-span-3">
                                <label for="observacion" class="block text-sm font-semibold text-gray-700 mb-1">Observación</label>
                                <textarea wire:model="observacion" id="observacion" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4">
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-3 bg-orange-600 border border-transparent rounded-lg font-bold text-base text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-lg transform hover:-translate-y-0.5 transition-all duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Guardar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
