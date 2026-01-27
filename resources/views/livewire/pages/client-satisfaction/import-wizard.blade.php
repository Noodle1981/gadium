<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Services\ExcelImportService;
use App\Models\Client;
use App\Models\ClientSatisfactionResponse;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithFileUploads;

    // State
    public $step = 1;
    public $file;
    
    #[\Livewire\Attributes\Locked]
    public $storedFilePath;
    
    public $type = 'client_satisfaction';
    
    // Analysis Results
    public $totalRows = 0;
    public $validRows = 0;
    public $validationErrors = [];
    public $unknownClients = [];
    public $resolutions = [];
    
    // UI Helpers
    public $processing = false;
    public $analyzing = false;
    
    // Import result stats
    public $importedCount = 0;
    public $skippedCount = 0;

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ];
    }

    public function updatedFile()
    {
        if ($this->analyzing) return;
        $this->validateOnly('file');
        $this->analyzeFile();
    }

    public function analyzeFile()
    {
        if ($this->analyzing) return;
        $this->analyzing = true;
        
        $this->validate();

        $service = app(ExcelImportService::class);
        
        try {
            if (!$this->file) throw new \Exception('No se ha seleccionado ningún archivo.');
            
            $extension = $this->file->getClientOriginalExtension();
            $filename = uniqid('import_cs_') . '.' . $extension;
            $path = $this->file->storeAs('imports', $filename);
            
            if (!$path) throw new \Exception('Error al guardar el archivo.');
            
            $this->storedFilePath = storage_path('app/private') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
            
            if (!file_exists($this->storedFilePath)) {
                 throw new \Exception("El archivo no se guardó correctamente.");
            }
            
            $analysis = $service->validateAndAnalyze($this->storedFilePath, $this->type);
            
            $this->totalRows = $analysis['total_rows'] ?? 0;
            $this->validRows = $analysis['valid_rows'] ?? 0;
            $this->validationErrors = $analysis['errors'] ?? [];
            $this->unknownClients = $analysis['unknown_clients'] ?? [];
            
            $this->file = null;
            
            if (!empty($this->validationErrors)) {
                $this->analyzing = false;
                return;
            }
            
            if (!empty($this->unknownClients)) {
                $this->step = 2; // Resolution
            } else {
                $this->step = 3; // Confirmation
            }
            
            $this->analyzing = false;

        } catch (\Exception $e) {
            $this->analyzing = false;
            $this->file = null;
            $this->addError('file', $e->getMessage());
        }
    }

    public function resolveClient($name, $selection)
    {
        if (!$selection) return;

        if ($selection === 'new') {
            $this->createNewClient($name);
            return;
        }

        $service = app(\App\Services\ClientNormalizationService::class);
        $service->createAlias((int)$selection, $name);
        $this->markResolved($name, (int)$selection);
    }

    public function createNewClient($name)
    {
        $client = Client::create(['nombre' => $name]);
        $this->markResolved($name, $client->id);
    }

    protected function markResolved($name, $clientId)
    {
        unset($this->unknownClients[array_search($name, $this->unknownClients)]);
        $this->resolutions[$name] = $clientId;
    }

    public function nextStep()
    {
        if ($this->step === 2) {
            if (!empty($this->unknownClients)) {
                $this->addError('resolution', 'Por favor resuelva todos los clientes desconocidos antes de continuar.');
                return;
            }
            $this->step = 3;
        }
    }

    public function startImport()
    {
        $this->processing = true;
        
        if (!$this->storedFilePath || !file_exists($this->storedFilePath)) {
            $this->addError('file', 'Archivo no encontrado.');
            $this->processing = false;
            return;
        }

        try {
            $service = app(ExcelImportService::class);
            $rows = $service->readExcelRows($this->storedFilePath);
            
            $chunk = [];
            $chunkSize = 1000;
            
            foreach ($rows as $row) {
                $chunk[] = $row;
                if (count($chunk) >= $chunkSize) {
                    $stats = $service->importChunk($chunk, $this->type);
                    $this->importedCount += $stats['inserted'];
                    $this->skippedCount += $stats['skipped'];
                    $chunk = [];
                }
            }
            
            if (!empty($chunk)) {
                $stats = $service->importChunk($chunk, $this->type);
                $this->importedCount += $stats['inserted'];
                $this->skippedCount += $stats['skipped'];
            }
            
            @unlink($this->storedFilePath);
            $this->step = 4;
            $this->processing = false;
            
        } catch (\Exception $e) {
            $this->processing = false;
            $this->addError('file', 'Error al importar: ' . $e->getMessage());
        }
    }
    
    public function resetWizard()
    {
        if ($this->storedFilePath && file_exists($this->storedFilePath)) {
            @unlink($this->storedFilePath);
        }
        $this->reset(['file', 'storedFilePath', 'step', 'totalRows', 'validRows', 'validationErrors', 'unknownClients', 'resolutions', 'processing', 'analyzing']);
    }

    public function with()
    {
        return [
            'clients' => Client::orderBy('nombre')->get(),
        ];
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
                            <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/></svg>
                            Importación Masiva
                        </div>
                        <h1 class="text-2xl font-bold text-white mb-1">Importar Satisfacción Clientes</h1>
                        <p class="text-orange-100 text-sm">Carga automática de encuestas desde archivos Excel</p>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.client-satisfaction.index' : (auth()->user()->hasRole('Gestor de Satisfacción Clientes') ? 'client-satisfaction.historial.importacion' : 'admin.client-satisfaction.index')) }}" class="inline-flex items-center px-4 py-2 bg-orange-700/30 text-white font-bold rounded-lg hover:bg-orange-700/40 transition-all border border-white/20 backdrop-blur-sm text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Ver Historial
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100">
                    <div class="flex items-center justify-between w-full">
                        @foreach([1 => 'Carga', 2 => 'Resolución', 3 => 'Confirmación', 4 => 'Resultado'] as $s => $label)
                            <div class="flex flex-col items-center {{ $step >= $s ? 'text-orange-600' : 'text-gray-400' }}">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 
                                    {{ $step >= $s ? 'border-orange-600 bg-orange-50' : 'border-gray-300 shadow-inner' }} font-bold transition-all duration-300">
                                    {{ $s }}
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-widest mt-2">{{ $label }}</span>
                            </div>
                            @if($s < 4) <div class="flex-1 h-0.5 bg-gray-100 mx-4 {{ $step > $s ? 'bg-orange-600' : '' }} transition-all duration-500"></div> @endif
                        @endforeach
                    </div>
                </div>

                <div class="p-8">
        
        @if($step === 1)
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Archivo Excel</label>
                    <p class="text-sm text-gray-500 mb-2">Formato esperado: Fecha, Cliente, Proyecto, Preguntas de Satisfacción (Tablas 1, 2, 3)</p>
                </div>

                <div 
                    x-data="{ isDropping: false }"
                    x-on:dragover.prevent="isDropping = true"
                    x-on:dragleave.prevent="isDropping = false"
                    x-on:drop.prevent="isDropping = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md relative transition-colors duration-200"
                    :class="{ 'border-indigo-500 bg-indigo-50': isDropping }"
                >
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span>Subir un archivo</span>
                                <input id="file-upload" x-ref="fileInput" wire:model="file" type="file" class="sr-only" accept=".xlsx,.xls">
                            </label>
                            <p class="pl-1">o arrastrar y soltar</p>
                        </div>
                        <p class="text-xs text-gray-500">Excel (.xlsx, .xls) hasta 10MB</p>
                    </div>

                    <div wire:loading wire:target="file" class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center">
                        <div class="flex items-center space-x-2 text-indigo-600">
                            <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Analizando archivo...</span>
                        </div>
                    </div>
                </div>

                @error('file') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

                @if(!empty($validationErrors))
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Se encontraron errores bloqueantes</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach(array_slice($validationErrors, 0, 5) as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                        @if(count($validationErrors) > 5)
                                            <li>... y {{ count($validationErrors) - 5 }} errores más.</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if($step === 2)
            <div class="space-y-6">
                <!-- Resolution Step -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <p class="text-sm text-yellow-700">
                        Hemos detectado <span class="font-bold">{{ count($unknownClients) }}</span> clientes desconocidos.
                        Vincúlelos a un cliente existente o cree uno nuevo.
                    </p>
                </div>
                
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre en Excel</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($unknownClients as $index => $name)
                            <tr wire:key="unknown-{{ $index }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <select class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                            wire:change="resolveClient('{{ $name }}', $event.target.value)">
                                        <option value="">Seleccionar Acción...</option>
                                        <option value="new" class="font-bold text-indigo-600">+ Crear como Nuevo Cliente</option>
                                        <optgroup label="Vincular a Existente">
                                            @foreach($clients as $c)
                                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="flex justify-end">
                    <button wire:click="nextStep" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl shadow-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none transition-all hover:scale-105 active:scale-95">
                        Continuar
                    </button>
                </div>
                @error('resolution') <span class="text-red-600 block mt-2">{{ $message }}</span> @enderror
            </div>
        @endif

        @if($step === 3)
            <div class="text-center space-y-6">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">¡Todo listo!</h3>
                <div class="mt-2 text-sm text-gray-500">
                    <p>Archivo válidado correctamente.</p>
                    <ul class="mt-4 list-disc list-inside text-left mx-auto max-w-sm">
                        <li>Filas Totales: <strong>{{ $totalRows }}</strong></li>
                        <li>Filas Válidas: <strong>{{ $validRows }}</strong></li>
                    </ul>
                </div>
                
                <div class="mt-6">
                    <button wire:click="startImport" class="inline-flex items-center px-8 py-4 border border-transparent text-base font-bold rounded-2xl shadow-xl text-white bg-orange-600 hover:bg-orange-700 focus:outline-none transition-all hover:scale-105 active:scale-95">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Iniciar Importación
                    </button>
                </div>
            </div>
        @endif

        @if($step === 4)
             <div class="text-center space-y-6">
                 <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">¡Importación Completada!</h3>
                
                <div class="mt-4 bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-3 gap-4">
                        <div class="text-center border-r border-gray-200">
                            <dt class="text-sm font-medium text-gray-500">Total Procesados</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $importedCount + $skippedCount }}</dd>
                        </div>
                        <div class="text-center border-r border-gray-200">
                            <dt class="text-sm font-medium text-gray-500">Nuevos</dt>
                            <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $importedCount }}</dd>
                        </div>
                        <div class="text-center">
                            <dt class="text-sm font-medium text-gray-500">Duplicados Omitidos</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-600">{{ $skippedCount }}</dd>
                        </div>
                    </dl>
                </div>
                
                <div class="mt-6 flex justify-center space-x-4">
                    <button wire:click="resetWizard" class="inline-flex items-center px-4 py-2 border border-orange-200 text-sm font-bold rounded-xl text-orange-700 bg-orange-50 hover:bg-orange-100 transition-all">
                        Importar otro archivo
                    </button>
                    <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.client-satisfaction.index' : (auth()->user()->hasRole('Gestor de Satisfacción Clientes') ? 'client-satisfaction.historial.importacion' : 'admin.client-satisfaction.index')) }}" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-bold rounded-xl text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-all hover:scale-105">
                        Ir al Historial
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
