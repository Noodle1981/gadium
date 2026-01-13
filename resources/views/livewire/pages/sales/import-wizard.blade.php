<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Services\CsvImportService;
use App\Models\Client;
use App\Models\ClientAlias;
use App\Jobs\ProcessImportJob;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithFileUploads;

    // State
    public $step = 1;
    public $file;
    
    #[\Livewire\Attributes\Locked]
    public $storedFilePath; // Store path instead of file object
    
    public $type = 'sale'; // 'sale' or 'budget'
    
    // Analysis Results - simplified
    public $totalRows = 0;
    public $validRows = 0;
    public $validationErrors = []; // Array of validation error messages
    public $unknownClients = []; // list of names
    public $resolutions = []; // ['unknown_name' => client_id]
    
    // UI Helpers
    public $processing = false;
    public $analyzing = false;
    
    // Dependency
    // Note: Services are injected in methods usually

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'type' => 'required|in:sale,budget',
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
        if ($this->analyzing) return; // Prevent re-entry
        
        $this->analyzing = true;
        
        $this->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB
            'type' => 'required|in:sale,budget',
        ]);

        $service = app(CsvImportService::class);
        
        try {
            // Validate file exists
            if (!$this->file) {
                throw new \Exception('No se ha seleccionado ningún archivo.');
            }
            
            // Generate unique filename
            $filename = uniqid('import_') . '.csv';
            
            // Store file with explicit filename (goes to storage/app/private/imports)
            $path = $this->file->storeAs('imports', $filename);
            
            if (!$path) {
                throw new \Exception('Error al guardar el archivo. Verifique los permisos del directorio storage/app/private/imports.');
            }
            
            // Build correct path (local disk uses storage/app/private as root)
            $this->storedFilePath = storage_path('app/private') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
            
            // Verify file was actually saved
            if (!file_exists($this->storedFilePath)) {
                // Try to list what's in the directory for debugging
                $files = @scandir(storage_path('app/imports'));
                $fileList = $files ? implode(', ', array_slice($files, 2)) : 'no se pudo leer';
                throw new \Exception("El archivo no se guardó. Archivos en imports: {$fileList}. Ruta esperada: {$this->storedFilePath}");
            }
            
            // Analyze from stored path
            $analysis = $service->validateAndAnalyze($this->storedFilePath, $this->type);
            
            // Store results in simple properties
            $this->totalRows = $analysis['total_rows'] ?? 0;
            $this->validRows = $analysis['valid_rows'] ?? 0;
            $this->validationErrors = $analysis['errors'] ?? [];
            $this->unknownClients = $analysis['unknown_clients'] ?? [];
            
            // Debug logging
            \Log::info('Analysis completed', [
                'total_rows' => $this->totalRows,
                'valid_rows' => $this->validRows,
                'errors_count' => count($this->validationErrors),
                'unknown_clients_count' => count($this->unknownClients),
            ]);
            
            // Clear file property to prevent Livewire serialization errors
            $this->file = null;
            
            if (!empty($this->validationErrors)) {
                // Stay on Step 1, show errors
                $this->analyzing = false;
                return;
            }
            
            if (!empty($this->unknownClients)) {
                $this->step = 2; // Go to Resolution
            } else {
                $this->step = 3; // Go to Confirmation
            }
            
            $this->analyzing = false;

        } catch (\Exception $e) {
            $this->analyzing = false;
            $this->file = null; // Clear file on error too
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

        // Create Alias immediately for existing client
        $service = app(\App\Services\ClientNormalizationService::class);
        $service->createAlias((int)$selection, $name);

        // Update local state
        $this->markResolved($name, (int)$selection);
    }

    public function createNewClient($name)
    {
        // Create the new client
        $client = Client::create(['nombre' => $name]);
        
        // Mark as resolved
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

    public $importedCount = 0;
    public $skippedCount = 0;

    public function startImport()
    {
        $this->processing = true;
        
        // Use the already stored file path
        if (!$this->storedFilePath || !file_exists($this->storedFilePath)) {
            $this->addError('file', 'Archivo no encontrado. Por favor vuelva a cargar el archivo.');
            $this->processing = false;
            return;
        }

        try {
            $service = app(CsvImportService::class);
            
            // Read and process file
            $handle = fopen($this->storedFilePath, 'r');
            
            // Detect delimiter
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = $this->detectDelimiter($firstLine);
            
            // Read headers
            $headers = fgetcsv($handle, 1000, $delimiter);
            $headers = array_map('trim', $headers);
            if (isset($headers[0])) {
                $headers[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $headers[0]);
            }
            
            // Process rows in chunks
            $chunk = [];
            $chunkSize = 1000;
            
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (empty(array_filter($data, function($val) { return $val !== '' && $val !== null; }))) {
                    continue;
                }
                
                if (count($data) === count($headers)) {
                    $chunk[] = array_combine($headers, $data);
                }
                
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
            
            fclose($handle);
            
            // Clean up file
            @unlink($this->storedFilePath);
            
            $this->step = 4;
            $this->processing = false;
            
        } catch (\Exception $e) {
            $this->processing = false;
            $this->addError('file', 'Error al importar: ' . $e->getMessage());
        }
    }
    
    protected function detectDelimiter(string $line): string
    {
        $commaCount = substr_count($line, ',');
        $semicolonCount = substr_count($line, ';');
        
        return $semicolonCount > $commaCount ? ';' : ',';
    }
    
    public function resetWizard()
    {
        // Clean up stored file if exists
        if ($this->storedFilePath && file_exists($this->storedFilePath)) {
            @unlink($this->storedFilePath);
        }
        
        $this->reset(['file', 'storedFilePath', 'step', 'totalRows', 'validRows', 'validationErrors', 'unknownClients', 'resolutions', 'processing', 'analyzing']);
    }

    public function suggestClients($name)
    {
        $service = app(\App\Services\ClientNormalizationService::class);
        return $service->findSimilarClients($name)->take(5);
    }
    
    public function with()
    {
        return [
            'clients' => Client::orderBy('nombre')->get(), // For select dropdown
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    
    <!-- Stepper -->
    <div class="mb-8">
        <div class="flex items-center justify-between w-full">
            @foreach([1 => 'Carga', 2 => 'Resolución', 3 => 'Confirmación', 4 => 'Resultado'] as $s => $label)
                <div class="flex flex-col items-center {{ $step >= $s ? 'text-indigo-600' : 'text-gray-400' }}">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 
                        {{ $step >= $s ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300' }} font-bold">
                        {{ $s }}
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
                    <select wire:model.live="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="sale">Ventas</option>
                        <option value="budget">Presupuestos</option>
                    </select>
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
                                <input id="file-upload" x-ref="fileInput" wire:model="file" type="file" class="sr-only" accept=".csv">
                            </label>
                            <p class="pl-1">o arrastrar y soltar</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            CSV hasta 10MB
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

        <!-- Step 2: Resolve -->
        @if($step === 2)
            <div class="space-y-6">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Hemos detectado <span class="font-bold">{{ count($unknownClients) }}</span> clientes que no coinciden exactamente con nuestros registros.
                                Por favor, vincúlelos a un cliente existente o cree uno nuevo.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre en CSV</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($unknownClients as $index => $name)
                                            <tr wire:key="unknown-{{ $index }}">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <!-- Simple client selector - Could be improved with searchable select -->
                            <select 
                                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                                        wire:change="resolveClient('{{ $name }}', $event.target.value)"
                                                    >
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
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button wire:click="nextStep" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none disabled:opacity-50">
                        Continuar
                    </button>
                </div>
                @error('resolution') <span class="text-red-600 block mt-2">{{ $message }}</span> @enderror
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
                    <button wire:click="startImport" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        Iniciar Importación
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
                
                <button wire:click="resetWizard" class="mt-6 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none">
                    Importar otro archivo
                </button>
            </div>
        @endif

    </div>
</div>
