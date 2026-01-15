<?php

use Livewire\Volt\Component;
use App\Models\BoardDetail;

new class extends Component {
    public BoardDetail $boardDetail;

    public $ano;
    public $proyecto_numero;
    public $cliente;
    public $descripcion_proyecto;
    public $columnas;
    public $gabinetes;
    public $potencia;
    public $pot_control;
    public $control;
    public $intervencion;
    public $documento_correccion_fallas;

    public function mount(BoardDetail $boardDetail)
    {
        $this->boardDetail = $boardDetail;
        
        $this->ano = $boardDetail->ano;
        $this->proyecto_numero = $boardDetail->proyecto_numero;
        $this->cliente = $boardDetail->cliente;
        $this->descripcion_proyecto = $boardDetail->descripcion_proyecto;
        $this->columnas = $boardDetail->columnas;
        $this->gabinetes = $boardDetail->gabinetes;
        $this->potencia = $boardDetail->potencia;
        $this->pot_control = $boardDetail->pot_control;
        $this->control = $boardDetail->control;
        $this->intervencion = $boardDetail->intervencion;
        $this->documento_correccion_fallas = $boardDetail->documento_correccion_fallas;
    }

    public function update()
    {
        $validated = $this->validate([
            'ano' => 'required|integer|min:2000|max:2100',
            'proyecto_numero' => 'required|string|max:255',
            'cliente' => 'required|string|max:255',
            'descripcion_proyecto' => 'required|string',
            'columnas' => 'required|integer|min:0',
            'gabinetes' => 'required|integer|min:0',
            'potencia' => 'required|integer|min:0',
            'pot_control' => 'required|integer|min:0',
            'control' => 'required|integer|min:0',
            'intervencion' => 'required|integer|min:0',
            'documento_correccion_fallas' => 'required|integer|min:0',
        ]);

        // Generar hash para verificar duplicados (excluyendo el actual)
        $hash = BoardDetail::generateHash(
            $this->ano,
            $this->proyecto_numero,
            $this->cliente,
            $this->descripcion_proyecto
        );

        if ($hash !== $this->boardDetail->hash && BoardDetail::existsByHash($hash)) {
             $this->addError('proyecto_numero', 'Ya existe otro registro idéntico (Año, Proyecto, Cliente, Descripción).');
             return;
        }

        $this->boardDetail->update(array_merge($validated, ['hash' => $hash]));

        session()->flash('message', 'Registro actualizado correctamente.');
        $this->redirect(route(auth()->user()->hasRole('Manager') ? 'manager.historial.tableros' : (auth()->user()->hasRole('Gestor de Tableros') ? 'boards.historial.importacion' : 'admin.historial.tableros')));
    }

    public function destroy()
    {
        $this->boardDetail->delete();
        session()->flash('message', 'Registro eliminado correctamente.');
        $this->redirect(route(auth()->user()->hasRole('Manager') ? 'manager.historial.tableros' : (auth()->user()->hasRole('Gestor de Tableros') ? 'boards.historial.importacion' : 'admin.historial.tableros')));
    }
}; ?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Editar Tablero</h2>
            <button wire:confirm="¿Estás seguro de que deseas eliminar este registro?" wire:click="destroy" class="text-red-600 hover:text-red-900 font-medium text-sm">
                Eliminar Registro
            </button>
        </div>

        <form wire:submit="update" class="space-y-6">
            <!-- Bloque 1: Datos del Proyecto -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Datos Principales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Año</label>
                        <input type="number" wire:model="ano" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('ano') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nro Proyecto</label>
                        <input type="text" wire:model="proyecto_numero" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('proyecto_numero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                     <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Cliente</label>
                        <input type="text" wire:model="cliente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('cliente') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                     <div class="col-span-4">
                        <label class="block text-sm font-medium text-gray-700">Descripción Proyecto</label>
                        <textarea wire:model="descripcion_proyecto" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        @error('descripcion_proyecto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Bloque 2: Cantidades -->
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h3 class="text-lg font-medium text-blue-800 mb-4">Cantidades Fabricadas</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Columnas</label>
                        <input type="number" wire:model="columnas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('columnas') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gabinetes</label>
                        <input type="number" wire:model="gabinetes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('gabinetes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Potencia</label>
                        <input type="number" wire:model="potencia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('potencia') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pot/Control</label>
                        <input type="number" wire:model="pot_control" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('pot_control') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Control</label>
                        <input type="number" wire:model="control" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('control') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Intervención</label>
                        <input type="number" wire:model="intervencion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('intervencion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 text-xs">Doc. Corr. Fallas</label>
                        <input type="number" wire:model="documento_correccion_fallas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('documento_correccion_fallas') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.historial.tableros' : (auth()->user()->hasRole('Gestor de Tableros') ? 'boards.historial.importacion' : 'admin.historial.tableros')) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Actualizar Registro
                </button>
            </div>
        </form>
    </div>
</div>
