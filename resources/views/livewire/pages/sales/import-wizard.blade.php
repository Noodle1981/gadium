<?php

use function Livewire\Volt\{state, usesFileUploads};
use App\Imports\SalesImport;
use App\Services\ClientNormalizationService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

usesFileUploads();

state([
    'step' => 1,
    'file' => null,
    'tempPath' => null,
    'preview' => [],
    'totalRows' => 0,
    'filename' => '',
    'importStats' => [],
    'errorMessage' => null,
]);

$upload = function () {
    $this->validate([
        'file' => 'required|file|mimes:csv,txt|max:10240',
    ]);

    try {
        $this->filename = $this->file->getClientOriginalName();
        $this->tempPath = $this->file->store('temp', 'local');

        // Leer datos para preview
        $data = Excel::toArray(new SalesImport(), $this->tempPath, 'local');
        
        if (empty($data) || empty($data[0])) {
            $this->errorMessage = 'El archivo está vacío o no tiene el formato correcto.';
            return;
        }

        $this->preview = array_slice($data[0], 0, 10);
        $this->totalRows = count($data[0]);

        // Validar columnas mínimas
        $firstRow = $this->preview[0];
        $requiredColumns = ['fecha', 'cliente', 'monto', 'comprobante'];
        
        foreach ($requiredColumns as $column) {
            if (!array_key_exists($column, $firstRow)) {
                $this->errorMessage = "Error: Columna '$column' no encontrada en la cabecera.";
                return;
            }
        }

        $this->step = 2;
        $this->errorMessage = null;

    } catch (\Exception $e) {
        $this->errorMessage = 'Error al procesar el archivo: ' . $e->getMessage();
    }
};

$process = function () {
    if (!$this->tempPath) {
        $this->step = 1;
        return;
    }

    try {
        $import = new SalesImport();
        // Usar disk explícito para evitar problemas con Storage::fake
        Excel::import($import, $this->tempPath, 'local');
        
        $this->importStats = $import->getStats();
        
        // Limpiar archivo temporal
        Storage::disk('local')->delete($this->tempPath);
        $this->tempPath = null;
        
        $this->step = 3;

    } catch (\Exception $e) {
        $this->errorMessage = 'Error durante la importación: ' . $e->getMessage();
    }
};

$resetWizard = function () {
    $this->step = 1;
    $this->file = null;
    $this->tempPath = null;
    $this->preview = [];
    $this->importStats = [];
    $this->errorMessage = null;
};

?>

<div class="py-6">
    <!-- Header/Breadcrumbs -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Asistente de Importación de Ventas</h2>
        <p class="text-gray-600 dark:text-gray-400">Paso {{ $step }} de 3</p>
    </div>

    <!-- Error Alert -->
    @if($errorMessage)
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 dark:bg-red-900/30 dark:text-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ $errorMessage }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button wire:click="$set('errorMessage', null)" class="text-red-700 dark:text-red-400 hover:text-red-900">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Wizard Steps -->
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            
            <!-- Step 1: Upload -->
            @if($step == 1)
                <div class="max-w-xl mx-auto py-8">
                    <div class="text-center mb-8">
                        <div class="mx-auto h-12 w-12 text-blue-500 mb-4">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium">Subir archivo de ventas</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Formatos permitidos: CSV, TXT (Max 10MB)</p>
                    </div>

                    <form wire:submit.prevent="upload" class="space-y-4">
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-700 dark:bg-gray-800 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                        <span class="font-semibold">Click para subir</span> o arrastrar y soltar
                                    </p>
                                    @if($file)
                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Archivo seleccionado: {{ $file->getClientOriginalName() }}</p>
                                    @endif
                                </div>
                                <input wire:model="file" type="file" class="hidden" />
                            </label>
                        </div>
                        
                        <div wire:loading wire:target="file" class="text-sm text-blue-600 font-medium">Subiendo archivo...</div>

                        <div class="flex justify-end pt-4">
                            <button 
                                type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none transition-colors"
                                @if(!$file) disabled @endif
                            >
                                Siguiente: Vista Previa
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Step 2: Preview -->
            @if($step == 2)
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Vista previa: {{ $filename }}</h3>
                            <p class="text-sm text-gray-500">Se detectaron {{ $totalRows }} registros en total.</p>
                        </div>
                        <div class="flex space-x-3">
                            <button wire:click="resetWizard" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancelar</button>
                            <button wire:click="process" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm font-medium">
                                Iniciar Importación
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    @foreach(array_keys($preview[0]) as $header)
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ $header }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($preview as $row)
                                    <tr>
                                        @foreach($row as $value)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $value }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Step 3: Result -->
            @if($step == 3)
                <div class="text-center py-8">
                    <div class="mx-auto h-16 w-16 text-green-500 mb-6">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold mb-4">Importación Finalizada</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 max-w-4xl mx-auto mb-8">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600">
                            <div class="text-sm text-gray-500 mb-1 uppercase">Total</div>
                            <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ $importStats['total'] }}</div>
                        </div>
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                            <div class="text-sm text-blue-600 mb-1 uppercase">Nuevos</div>
                            <div class="text-2xl font-bold text-blue-800 dark:text-blue-300">{{ $importStats['new'] }}</div>
                        </div>
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800">
                            <div class="text-sm text-yellow-600 mb-1 uppercase">Duplicados</div>
                            <div class="text-2xl font-bold text-yellow-800 dark:text-yellow-300">{{ $importStats['duplicates'] }}</div>
                        </div>
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                            <div class="text-sm text-red-600 mb-1 uppercase">Errores</div>
                            <div class="text-2xl font-bold text-red-800 dark:text-red-300">{{ $importStats['errors'] }}</div>
                        </div>
                    </div>

                    @if(!empty($importStats['error_messages']))
                        <div class="max-w-4xl mx-auto text-left mb-8">
                            <h3 class="text-lg font-medium mb-3">Detalle de errores:</h3>
                            <ul class="space-y-2 bg-red-50 dark:bg-red-900/10 p-4 rounded-lg border border-red-100 dark:border-red-900/30">
                                @foreach($importStats['error_messages'] as $error)
                                    <li class="text-sm text-red-700 dark:text-red-400 flex items-start">
                                        <span class="mr-2">•</span> {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex justify-center space-x-4">
                        <button wire:click="resetWizard" class="px-6 py-2 border border-gray-300 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Importar otro archivo
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
