<?php

use function Livewire\Volt\{state, layout, computed, rules};
use App\Models\WeightingFactor;
use Spatie\Permission\Models\Role;

layout('layouts.app');

state([
    'roleName' => '',
    'value' => '',
    'startDate' => now()->format('Y-m-d'),
    'endDate' => '',
    'isEditing' => false,
    'editingId' => null,
]);

$factors = computed(function () {
    return WeightingFactor::orderBy('role_name')
        ->orderByDesc('start_date')
        ->get();
});

$roles = computed(function () {
    return Role::orderBy('name')->get();
});

$saveFactor = function () {
    $this->validate([
        'roleName' => 'required|exists:roles,name',
        'value' => 'required|numeric|min:0.00000001',
        'startDate' => 'required|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
    ]);

    // Validación de solapamiento
    $overlap = WeightingFactor::where('role_name', $this->roleName)
        ->where('id', '!=', $this->editingId)
        ->where(function ($query) {
            $query->whereBetween('start_date', [$this->startDate, $this->endDate ?: '9999-12-31'])
                  ->orWhereBetween('end_date', [$this->startDate, $this->endDate ?: '9999-12-31'])
                  ->orWhere(function ($q) {
                      $q->where('start_date', '<=', $this->startDate)
                        ->where(function ($q2) {
                            $q2->whereNull('end_date')
                               ->orWhere('end_date', '>=', $this->endDate ?: '9999-12-31');
                        });
                  });
        })->exists();

    if ($overlap) {
        $this->addError('startDate', 'Existe un factor para este rol con fechas solapadas.');
        return;
    }

    $data = [
        'role_name' => $this->roleName,
        'value' => $this->value,
        'start_date' => $this->startDate,
        'end_date' => $this->endDate ?: null,
    ];

    if ($this->isEditing) {
        WeightingFactor::find($this->editingId)->update($data);
        session()->flash('status', 'Factor actualizado exitosamente.');
    } else {
        WeightingFactor::create($data);
        session()->flash('status', 'Factor creado exitosamente.');
    }

    $this->reset(['roleName', 'value', 'startDate', 'endDate', 'isEditing', 'editingId']);
};

$editFactor = function ($id) {
    $factor = WeightingFactor::find($id);
    $this->roleName = $factor->role_name;
    $this->value = $factor->value;
    $this->startDate = $factor->start_date->format('Y-m-d');
    $this->endDate = $factor->end_date ? $factor->end_date->format('Y-m-d') : '';
    $this->isEditing = true;
    $this->editingId = $id;
};

$deleteFactor = function ($id) {
    WeightingFactor::find($id)->delete();
    session()->flash('status', 'Factor eliminado correctamente.');
};

?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Factores de Ponderación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Formulario -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ $isEditing ? 'Editar Factor' : 'Nuevo Factor de Ponderación' }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Defina los factores con precisión de 8 decimales para cada rol.
                        </p>
                    </header>

                    <form wire:submit="saveFactor" class="mt-6 space-y-6">
                        @if (session('status'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div>
                            <x-input-label for="roleName" :value="__('Rol')" />
                            <select id="roleName" wire:model="roleName" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Seleccione un rol...</option>
                                @foreach($this->roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('roleName')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="value" :value="__('Valor del Factor (ej: 1.94390399)')" />
                            <x-text-input id="value" type="number" step="0.00000001" class="mt-1 block w-full" wire:model="value" required />
                            <x-input-error :messages="$errors->get('value')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="startDate" :value="__('Fecha Inicio')" />
                                <x-text-input id="startDate" type="date" class="mt-1 block w-full" wire:model="startDate" required />
                                <x-input-error :messages="$errors->get('startDate')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="endDate" :value="__('Fecha Fin (Opcional)')" />
                                <x-text-input id="endDate" type="date" class="mt-1 block w-full" wire:model="endDate" />
                                <x-input-error :messages="$errors->get('endDate')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                            
                            @if($isEditing)
                                <button type="button" wire:click="$reset(['roleName', 'value', 'startDate', 'endDate', 'isEditing', 'editingId'])" class="text-sm text-gray-600 hover:text-gray-900">
                                    {{ __('Cancelar') }}
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900">Historial de Factores</h2>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inicio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->factors as $factor)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $factor->role_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-orange-600 font-bold">{{ number_format($factor->value, 8) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $factor->start_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $factor->end_date ? $factor->end_date->format('d/m/Y') : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($factor->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button wire:click="editFactor({{ $factor->id }})" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                                        <button 
                                            wire:click="deleteFactor({{ $factor->id }})" 
                                            wire:confirm="¿Está seguro de eliminar este factor?"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
