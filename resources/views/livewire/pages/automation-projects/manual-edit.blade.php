<?php

use Livewire\Volt\Component;
use App\Models\AutomationProject;

new class extends Component {
    public AutomationProject $automationProject;
    
    public $proyecto_id = '';
    public $cliente = '';
    public $proyecto_descripcion = '';
    public $fat = 'NO';
    public $pem = 'NO';

    public function mount(AutomationProject $automationProject)
    {
        $this->automationProject = $automationProject;
        $this->proyecto_id = $automationProject->proyecto_id;
        $this->cliente = $automationProject->cliente;
        $this->proyecto_descripcion = $automationProject->proyecto_descripcion;
        $this->fat = $automationProject->fat;
        $this->pem = $automationProject->pem;
    }

    public function rules()
    {
        return [
            'proyecto_id' => 'required|string|max:255',
            'cliente' => 'required|string|max:255',
            'proyecto_descripcion' => 'required|string',
            'fat' => 'required|in:SI,NO',
            'pem' => 'required|in:SI,NO',
        ];
    }

    public function save()
    {
        $this->validate();

        // Check for duplicates excluding current record
        $hash = AutomationProject::generateHash([
            'proyecto_id' => $this->proyecto_id,
            'cliente' => $this->cliente,
            'proyecto_descripcion' => $this->proyecto_descripcion,
        ]);

        $duplicate = AutomationProject::byHash($hash)->where('id', '!=', $this->automationProject->id)->first();
        
        if ($duplicate) {
            $this->addError('proyecto_id', 'Ya existe otro proyecto con estos datos.');
            return;
        }

        $this->automationProject->update([
            'proyecto_id' => $this->proyecto_id,
            'cliente' => $this->cliente,
            'proyecto_descripcion' => $this->proyecto_descripcion,
            'fat' => $this->fat,
            'pem' => $this->pem,
            'hash' => $hash,
        ]);

        session()->flash('success', 'Proyecto actualizado exitosamente.');
        return $this->redirect(route('automation_projects.historial.importacion'), navigate: true);
    }
}; ?>

<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Editar Proyecto de Automatización</h2>

        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Proyecto ID -->
            <div>
                <label for="proyecto_id" class="block text-sm font-medium text-gray-700">ID del Proyecto *</label>
                <input type="text" wire:model="proyecto_id" id="proyecto_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('proyecto_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Cliente -->
            <div>
                <label for="cliente" class="block text-sm font-medium text-gray-700">Cliente *</label>
                <input type="text" wire:model="cliente" id="cliente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('cliente') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Descripción del Proyecto -->
            <div>
                <label for="proyecto_descripcion" class="block text-sm font-medium text-gray-700">Descripción del Proyecto *</label>
                <textarea wire:model="proyecto_descripcion" id="proyecto_descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                @error('proyecto_descripcion') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- FAT -->
            <div>
                <label for="fat" class="block text-sm font-medium text-gray-700">FAT *</label>
                <select wire:model="fat" id="fat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="NO">NO</option>
                    <option value="SI">SI</option>
                </select>
                @error('fat') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- PEM -->
            <div>
                <label for="pem" class="block text-sm font-medium text-gray-700">PEM *</label>
                <select wire:model="pem" id="pem" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="NO">NO</option>
                    <option value="SI">SI</option>
                </select>
                @error('pem') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('automation_projects.historial.importacion') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none" wire:navigate>
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    Actualizar Proyecto
                </button>
            </div>
        </form>

        @if(session()->has('success'))
            <div class="mt-4 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
