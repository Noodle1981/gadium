<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Services\ExcelImportService;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithFileUploads;

    // State
    public $step = 1;
    public $file;
    
    #[\Livewire\Attributes\Locked]
    public $storedFilePath; 
    
    public $type = 'board_detail'; // Changed type
    
    // Analysis Results
    public $totalRows = 0;
    public $validRows = 0;
    public $validationErrors = [];
    
    // UI Helpers
    public $processing = false;
    public $analyzing = false;
    
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
        
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $service = app(ExcelImportService::class);
        
        try {
            if (!$this->file) {
                throw new \Exception('No se ha seleccionado ningún archivo.');
            }
            
            // Generate unique filename
            $extension = $this->file->getClientOriginalExtension();
            $filename = uniqid('boards_import_') . '.' . $extension; // Changed prefix
            
            // Store file
            $path = $this->file->storeAs('imports', $filename);
            
            if (!$path) {
                throw new \Exception('Error al guardar el archivo.');
            }
            
            // Build correct path
            $this->storedFilePath = storage_path('app/private') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
            
            if (!file_exists($this->storedFilePath)) {
                 throw new \Exception("El archivo no se guardó correctamente.");
            }
            
            // Analyze
            $analysis = $service->validateAndAnalyze($this->storedFilePath, $this->type);
            
            $this->totalRows = $analysis['total_rows'] ?? 0;
            $this->validRows = $analysis['valid_rows'] ?? 0;
            $this->validationErrors = $analysis['errors'] ?? [];
            
            $this->file = null;
            
            if (!empty($this->validationErrors)) {
                $this->analyzing = false;
                return;
            }
            
            // Skip resolution step, go directly to confirmation
            $this->step = 3; 
            
            $this->analyzing = false;

        } catch (\Exception $e) {
            $this->analyzing = false;
            $this->file = null;
            $this->addError('file', $e->getMessage());
        }
    }

    public $importedCount = 0;
    public $skippedCount = 0;

    public function startImport()
    {
        $this->processing = true;
        
        if (!$this->storedFilePath || !file_exists($this->storedFilePath)) {
            $this->addError('file', 'Archivo no encontrado. Por favor vuelva a cargar el archivo.');
            $this->processing = false;
            return;
        }

        try {
            $service = app(ExcelImportService::class);
            
            // Read Excel rows
            $rows = $service->readExcelRows($this->storedFilePath);
            
            // Process rows in chunks
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
            
            // Process remaining
            if (!empty($chunk)) {
                $stats = $service->importChunk($chunk, $this->type);
                $this->importedCount += $stats['inserted'];
                $this->skippedCount += $stats['skipped'];
            }
            
            // Clean up file
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
        
        $this->reset(['file', 'storedFilePath', 'step', 'totalRows', 'validRows', 'validationErrors', 'processing', 'analyzing', 'importedCount', 'skippedCount']);
    }
}; ?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    
    <!-- Stepper -->
    <div class="mb-8">
        <div class="flex items-center justify-between w-full">
            @foreach([1 => 'Carga', 3 => 'Confirmación', 4 => 'Resultado'] as $s => $label)
                <div class="flex flex-col items-center {{ $step >= $s ? 'text-indigo-600' : 'text-gray-400' }}">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 
                        {{ $step >= $s ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300' }} font-bold">
                        {{ $s === 3 ? 2 : ($s === 4 ? 3 : 1) }}
                    </div>
                    <span class="text-sm mt-1">{{ $label }}</span>
                </div>
                @if($s < 4) <div class="flex-1 h-1 bg-gray-200 mx-4 {{ $step > $s ? 'bg-indigo-600' : '' }}"></div> @endif
            @endforeach
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        
        <!-- Step 1: Upload -->
        @if($step === 1)
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Importación</label>
                    <div class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 bg-gray-50 text-gray-500 rounded-md">
                        Tableros de Control
                    </div>
                </div>

                <div 
                    x-data="{ isDropping: false, progress: 0 }"
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
                        <p class="text-xs text-gray-500">
                            Excel (.xlsx, .xls) hasta 10MB
                        </p>
                    </div>

                    <!-- Loading State -->
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

        <!-- Step 3: Confirmation -->
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
                    <button wire:click="startImport" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="startImport">Iniciar Importación</span>
                        <span wire:loading wire:target="startImport">Procesando...</span>
                    </button>
                </div>
            </div>
        @endif

        <!-- Step 4: Results -->
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
                
                <div class="flex justify-center space-x-4 mt-6">
                    <button wire:click="resetWizard" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none">
                        Importar otro archivo
                    </button>
                    <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.historial.tableros' : (auth()->user()->hasRole('Gestor de Tableros') ? 'boards.historial.importacion' : 'admin.historial.tableros')) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                        Ver Historial
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>
