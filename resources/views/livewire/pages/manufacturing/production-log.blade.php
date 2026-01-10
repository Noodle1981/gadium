<?php

use function Livewire\Volt\{state, layout, rules, computed};
use App\Models\Project;
use App\Models\Client;
use App\Models\ManufacturingLog;
use Illuminate\Support\Facades\Auth;

layout('layouts.app');

state([
    'projectId' => '',
    'projectName' => '',
    'clientId' => '',
    'unitsProduced' => '',
    'correctionDocuments' => 0,
    'hoursClock' => 0,
    'recordedAt' => now()->format('Y-m-d'),
    'showCreateProject' => false,
    'search' => '',
]);

$projects = computed(function () {
    return Project::where('id', 'like', "%{$this->search}%")
        ->orWhere('name', 'like', "%{$this->search}%")
        ->take(10)
        ->get();
});

$clients = computed(function () {
    return Client::orderBy('nombre')->get();
});

$selectProject = function ($id) {
    $project = Project::find($id);
    if ($project) {
        $this->projectId = $project->id;
        $this->projectName = $project->name;
        $this->search = $project->id . ' - ' . $project->name;
    }
};

$createNewProject = function () {
    $this->validate([
        'projectId' => 'required|unique:projects,id',
        'projectName' => 'required|min:3',
        'clientId' => 'required|exists:clients,id',
    ]);

    Project::create([
        'id' => $this->projectId,
        'name' => $this->projectName,
        'client_id' => $this->clientId,
    ]);

    $this->showCreateProject = false;
    $this->search = $this->projectId . ' - ' . $this->projectName;
    session()->flash('status', 'Proyecto creado exitosamente.');
};

$saveLog = function () {
    $this->validate([
        'projectId' => 'required|exists:projects,id',
        'unitsProduced' => 'required|integer|min:1',
        'hoursClock' => 'required|numeric|min:0',
        'correctionDocuments' => 'required|integer|min:0|lte:unitsProduced',
        'recordedAt' => 'required|date',
    ], [
        'correctionDocuments.lte' => 'Los documentos de corrección no pueden ser mayores a las unidades producidas.',
    ]);

    // Obtener factor de ponderación vigente para el usuario actual
    $user = Auth::user();
    $roleName = $user->getRoleNames()->first() ?: 'Viewer';
    
    $factor = \App\Models\WeightingFactor::vigente($roleName, $this->recordedAt)->first();
    $factorValue = $factor ? $factor->value : 1.0;
    
    $hoursWeighted = $this->hoursClock * $factorValue;

    $log = ManufacturingLog::create([
        'project_id' => $this->projectId,
        'user_id' => Auth::id(),
        'units_produced' => $this->unitsProduced,
        'correction_documents' => $this->correctionDocuments,
        'hours_clock' => $this->hoursClock,
        'hours_weighted' => $hoursWeighted,
        'recorded_at' => $this->recordedAt,
    ]);

    // Disparar evento para monitoreo de calidad
    \App\Events\ProductionLogCreated::dispatch($log);

    // Reset fields
    $this->unitsProduced = '';
    $this->correctionDocuments = 0;
    
    session()->flash('status', 'Registro de producción guardado exitosamente.');
};

?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registro de Producción Diaria') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form wire:submit="saveLog" class="space-y-6">
                        @if (session('status'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('status') }}</span>
                            </div>
                        @endif

                        <!-- Búsqueda de Proyecto -->
                        <div class="relative" x-data="{ open: false }">
                            <x-input-label for="search" :value="__('Proyecto (ID o Nombre)')" />
                            <x-text-input 
                                id="search" 
                                type="text" 
                                class="mt-1 block w-full" 
                                wire:model.live="search"
                                @click="open = true"
                                @click.away="open = false"
                                placeholder="Buscar proyecto..."
                                autocomplete="off"
                            />
                            
                            @if(!empty($search) && $projectId == '')
                                <div x-show="open" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1">
                                    @forelse($this->projects as $project)
                                        <button 
                                            type="button"
                                            wire:click="selectProject('{{ $project->id }}')"
                                            class="w-full text-left px-4 py-2 hover:bg-gray-100 focus:outline-none"
                                        >
                                            <div class="font-bold text-orange-600">{{ $project->id }}</div>
                                            <div class="text-sm text-gray-600">{{ $project->name }}</div>
                                        </button>
                                    @empty
                                        <div class="px-4 py-2 text-gray-500 italic">
                                            No se encontró el proyecto. 
                                            <button 
                                                type="button" 
                                                wire:click="$set('showCreateProject', true)"
                                                class="text-orange-600 font-bold hover:underline"
                                            >
                                                Crear nuevo proyecto "{{ $search }}"
                                            </button>
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                            <x-input-error :messages="$errors->get('projectId')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Unidades Producidas -->
                            <div>
                                <x-input-label for="unitsProduced" :value="__('Unidades Producidas')" />
                                <x-text-input id="unitsProduced" type="number" class="mt-1 block w-full" wire:model="unitsProduced" required min="1" />
                                <x-input-error :messages="$errors->get('unitsProduced')" class="mt-2" />
                            </div>

                            <!-- Horas Reloj -->
                            <div>
                                <x-input-label for="hoursClock" :value="__('Horas Reloj (Simples)')" />
                                <x-text-input id="hoursClock" type="number" step="0.25" class="mt-1 block w-full" wire:model="hoursClock" required min="0" />
                                <x-input-error :messages="$errors->get('hoursClock')" class="mt-2" />
                            </div>

                            <!-- Documentos de Corrección -->
                            <div>
                                <x-input-label for="correctionDocuments" :value="__('Documentos de Corrección')" />
                                <x-text-input id="correctionDocuments" type="number" class="mt-1 block w-full" wire:model="correctionDocuments" required min="0" />
                                <x-input-error :messages="$errors->get('correctionDocuments')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Fecha -->
                        <div>
                            <x-input-label for="recordedAt" :value="__('Fecha de Producción')" />
                            <x-text-input id="recordedAt" type="date" class="mt-1 block w-full" wire:model="recordedAt" required />
                            <x-input-error :messages="$errors->get('recordedAt')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4 bg-orange-600 hover:bg-orange-700">
                                {{ __('Guardar Registro') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Proyecto On-the-fly -->
    @if($showCreateProject)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-20 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                        Crear Nuevo Proyecto
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="new_projectId" :value="__('ID de Proyecto (ej. 3400)')" />
                            <x-text-input id="new_projectId" type="text" class="mt-1 block w-full uppercase" wire:model="projectId" placeholder="3400" />
                            <x-input-error :messages="$errors->get('projectId')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="new_projectName" :value="__('Nombre del Proyecto')" />
                            <x-text-input id="new_projectName" type="text" class="mt-1 block w-full" wire:model="projectName" placeholder="Manga Retráctil Calidra" />
                            <x-input-error :messages="$errors->get('projectName')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="new_clientId" :value="__('Cliente')" />
                            <select id="new_clientId" wire:model="clientId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Seleccione un cliente...</option>
                                @foreach($this->clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->nombre }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('clientId')" class="mt-2" />
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="createNewProject" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Crear Proyecto
                    </button>
                    <button type="button" wire:click="$set('showCreateProject', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
