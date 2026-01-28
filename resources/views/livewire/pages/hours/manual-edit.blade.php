<?php

use Livewire\Volt\Component;
use App\Models\HourDetail;

new class extends Component {
    public HourDetail $hourDetail;

    // 1. Datos Principales
    public $fecha = '';
    public $personal = '';
    public $funcion = '';
    public $proyecto = '';
    public $dia = '';

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

    public function mount(HourDetail $hourDetail)
    {
        $this->hourDetail = $hourDetail;
        
        $this->fecha = $hourDetail->fecha->format('Y-m-d');
        $this->personal = $hourDetail->personal;
        $this->funcion = $hourDetail->funcion;
        $this->proyecto = $hourDetail->proyecto;
        $this->dia = $hourDetail->dia;
        
        $this->hs = $hourDetail->hs;
        $this->ponderador = $hourDetail->ponderador;
        $this->horas_ponderadas = $hourDetail->horas_ponderadas;
        $this->hs_comun = $hourDetail->hs_comun;
        $this->hs_50 = $hourDetail->hs_50;
        $this->hs_100 = $hourDetail->hs_100;
        $this->hs_viaje = $hourDetail->hs_viaje;
        $this->hs_adeudadas = $hourDetail->hs_adeudadas;
        $this->hs_pernoctada = $hourDetail->hs_pernoctada;
        
        $this->vianda = $hourDetail->vianda;
        $this->observacion = $hourDetail->observacion;
        $this->programacion = $hourDetail->programacion;
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

        // Check for duplicates (excluding current)
        $hash = HourDetail::generateHash($this->fecha, $this->personal, $this->proyecto, (float)$this->hs);

        if ($hash !== $this->hourDetail->hash && HourDetail::existsByHash($hash)) {
            $this->addError('proyecto', 'Ya existe otro registro idéntico (Fecha + Personal + Proyecto + Horas).');
            return;
        }

        $date = \Carbon\Carbon::parse($this->fecha);

        $this->hourDetail->update([
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

        session()->flash('success', '¡Registro actualizado exitosamente!');
    }
    
    public function cancel()
    {
        return redirect()->route('app.hours.index');
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Registro de Horas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                @if (session()->has('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form wire:submit="save" class="space-y-8">
                    
                    <!-- 1. CAMPOS PRINCIPALES -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 border-b pb-2">Datos Principales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Fecha -->
                            <div>
                                <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha *</label>
                                <input type="date" wire:model.live="fecha" id="fecha" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Día: {{ $dia }}</p>
                                @error('fecha') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Personal -->
                            <div>
                                <label for="personal" class="block text-sm font-medium text-gray-700">Personal *</label>
                                <input type="text" wire:model="personal" id="personal" placeholder="Nombre completo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('personal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Función -->
                            <div>
                                <label for="funcion" class="block text-sm font-medium text-gray-700">Función *</label>
                                <input type="text" wire:model="funcion" id="funcion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('funcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Proyecto -->
                            <div class="md:col-span-2">
                                <label for="proyecto" class="block text-sm font-medium text-gray-700">Proyecto *</label>
                                <input type="text" wire:model="proyecto" id="proyecto" placeholder="Código o Nombre del Proyecto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('proyecto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 2. MÉTRICAS DE HORAS -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Métricas de Horas</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Hs Totales -->
                            <div>
                                <label for="hs" class="block text-sm font-medium text-gray-700">Hs Totales *</label>
                                <input type="number" step="0.01" wire:model.live="hs" id="hs" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
                                @error('hs') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Ponderador -->
                            <div>
                                <label for="ponderador" class="block text-sm font-medium text-gray-700">Ponderador</label>
                                <input type="number" step="0.0001" wire:model.live="ponderador" id="ponderador" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('ponderador') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                             <!-- Horas Ponderadas (Calculado) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Horas Ponderadas</label>
                                <div class="mt-1 p-2 bg-gray-100 rounded-md text-gray-700 border border-gray-300">
                                    {{ number_format((float)$horas_ponderadas, 4) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mt-6">
                            <div>
                                <label for="hs_comun" class="block text-xs font-medium text-gray-500">Hs Común</label>
                                <input type="number" step="0.01" wire:model="hs_comun" id="hs_comun" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="hs_50" class="block text-xs font-medium text-gray-500">Hs 50%</label>
                                <input type="number" step="0.01" wire:model="hs_50" id="hs_50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="hs_100" class="block text-xs font-medium text-gray-500">Hs 100%</label>
                                <input type="number" step="0.01" wire:model="hs_100" id="hs_100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="hs_viaje" class="block text-xs font-medium text-gray-500">Hs Viaje</label>
                                <input type="number" step="0.01" wire:model="hs_viaje" id="hs_viaje" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="hs_adeudadas" class="block text-xs font-medium text-gray-500">Hs Adeudadas</label>
                                <input type="number" step="0.01" wire:model="hs_adeudadas" id="hs_adeudadas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- 3. OTROS DETALLES -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 border-b pb-2">Información Adicional</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <div>
                                <label for="vianda" class="block text-sm font-medium text-gray-700">Vianda</label>
                                <select wire:model="vianda" id="vianda" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="hs_pernoctada" class="block text-sm font-medium text-gray-700">Pernoctada</label>
                                <select wire:model="hs_pernoctada" id="hs_pernoctada" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="No">No</option>
                                    <option value="Sí">Sí</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="programacion" class="block text-sm font-medium text-gray-700">Programación</label>
                                <input type="text" wire:model="programacion" id="programacion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="md:col-span-3">
                                <label for="observacion" class="block text-sm font-medium text-gray-700">Observación</label>
                                <textarea wire:model="observacion" id="observacion" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-4">
                        <button type="button" wire:click="cancel" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            Cancelar
                        </button>
                        <x-primary-button class="ml-4">
                            {{ __('Actualizar Registro') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
