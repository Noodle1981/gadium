<?php

use Livewire\Volt\Component;
use App\Models\StaffSatisfactionResponse;
use App\Services\StaffSatisfactionCalculator;
use Carbon\Carbon;

new class extends Component {
    public $rows = [];
    public $fecha;

    public function mount()
    {
        $this->fecha = now()->format('Y-m-d');
        $this->addRow();
    }

    public function addRow()
    {
        $this->rows[] = [
            'personal' => '',
            // P1
            'p1_mal' => false, 'p1_normal' => false, 'p1_bien' => false,
            // P2
            'p2_mal' => false, 'p2_normal' => false, 'p2_bien' => false,
            // P3
            'p3_mal' => false, 'p3_normal' => false, 'p3_bien' => false,
            // P4
            'p4_mal' => false, 'p4_normal' => false, 'p4_bien' => false,
        ];
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function save()
    {
        $this->validate([
            'fecha' => 'required|date',
            'rows.*.personal' => 'required|string',
        ]);

        $count = 0;
        foreach ($this->rows as $row) {
            if (empty($row['personal'])) continue;
            
            // Ensure single select per question logic if needed? 
            // The excel allows X in one box per question usually.
            // We'll proceed with saving as is.

            $data = array_merge($row, ['fecha' => $this->fecha]);
            
            $hash = StaffSatisfactionResponse::generateHash($data);

            if (!StaffSatisfactionResponse::existsByHash($hash)) {
                $data['hash'] = $hash;
                StaffSatisfactionResponse::create($data);
                $count++;
            }
        }

        // Recalculate global stats for the month
        app(StaffSatisfactionCalculator::class)->updateMonthlyStats(Carbon::parse($this->fecha)->format('Y-m'));

        session()->flash('message', "Se guardaron {$count} registros correctamente.");
        $this->reset(['rows']);
        $this->addRow();
    }
}; ?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Ingreso Manual: Satisfacción Operarios</h2>
        
        @if (session()->has('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('message') }}
            </div>
        @endif

        <div class="mb-4 w-64">
            <label class="block text-sm font-medium text-gray-700">Fecha de Encuesta</label>
            <input type="date" wire:model="fecha" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">Personal</th>
                        
                        <th colspan="3" class="px-1 py-1 text-center text-xs font-bold bg-blue-50 border-l border-blue-200">1. Trato Jefe</th>
                        <th colspan="3" class="px-1 py-1 text-center text-xs font-bold bg-green-50 border-l border-green-200">2. Trato Comp.</th>
                        <th colspan="3" class="px-1 py-1 text-center text-xs font-bold bg-yellow-50 border-l border-yellow-200">3. Clima</th>
                        <th colspan="3" class="px-1 py-1 text-center text-xs font-bold bg-purple-50 border-l border-purple-200">4. Comodidad</th>
                        
                        <th class="relative px-3 py-2"><span class="sr-only">Borrar</span></th>
                    </tr>
                    <tr>
                        <th class="px-3 py-1 sticky left-0 bg-gray-50 z-10"></th>
                        
                        <th class="text-[10px] text-center text-red-600 bg-blue-50 border-l border-blue-200">Mal</th>
                        <th class="text-[10px] text-center text-yellow-600 bg-blue-50">Norm</th>
                        <th class="text-[10px] text-center text-green-600 bg-blue-50">Bien</th>

                        <th class="text-[10px] text-center text-red-600 bg-green-50 border-l border-green-200">Mal</th>
                        <th class="text-[10px] text-center text-yellow-600 bg-green-50">Norm</th>
                        <th class="text-[10px] text-center text-green-600 bg-green-50">Bien</th>

                        <th class="text-[10px] text-center text-red-600 bg-yellow-50 border-l border-yellow-200">Mal</th>
                        <th class="text-[10px] text-center text-yellow-600 bg-yellow-50">Norm</th>
                        <th class="text-[10px] text-center text-green-600 bg-yellow-50">Bien</th>

                        <th class="text-[10px] text-center text-red-600 bg-purple-50 border-l border-purple-200">Incó</th>
                        <th class="text-[10px] text-center text-yellow-600 bg-purple-50">Norm</th>
                        <th class="text-[10px] text-center text-green-600 bg-purple-50">Cóm</th>
                        
                        <th></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rows as $index => $row)
                        <tr>
                            <td class="px-3 py-2 sticky left-0 bg-white z-10 shadow-sm">
                                <input type="text" wire:model="rows.{{ $index }}.personal" placeholder="Nombre Operario" class="block w-40 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs">
                            </td>

                            <!-- P1 -->
                            <td class="px-1 py-2 text-center bg-blue-50 border-l border-blue-100"><input type="checkbox" wire:model="rows.{{ $index }}.p1_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-blue-50"><input type="checkbox" wire:model="rows.{{ $index }}.p1_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-blue-50"><input type="checkbox" wire:model="rows.{{ $index }}.p1_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <!-- P2 -->
                            <td class="px-1 py-2 text-center bg-green-50 border-l border-green-100"><input type="checkbox" wire:model="rows.{{ $index }}.p2_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-green-50"><input type="checkbox" wire:model="rows.{{ $index }}.p2_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-green-50"><input type="checkbox" wire:model="rows.{{ $index }}.p2_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <!-- P3 -->
                            <td class="px-1 py-2 text-center bg-yellow-50 border-l border-yellow-100"><input type="checkbox" wire:model="rows.{{ $index }}.p3_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-yellow-50"><input type="checkbox" wire:model="rows.{{ $index }}.p3_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-yellow-50"><input type="checkbox" wire:model="rows.{{ $index }}.p3_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <!-- P4 -->
                            <td class="px-1 py-2 text-center bg-purple-50 border-l border-purple-100"><input type="checkbox" wire:model="rows.{{ $index }}.p4_mal" class="rounded text-red-600 focus:ring-red-500"></td>
                            <td class="px-1 py-2 text-center bg-purple-50"><input type="checkbox" wire:model="rows.{{ $index }}.p4_normal" class="rounded text-yellow-600 focus:ring-yellow-500"></td>
                            <td class="px-1 py-2 text-center bg-purple-50"><input type="checkbox" wire:model="rows.{{ $index }}.p4_bien" class="rounded text-green-600 focus:ring-green-500"></td>

                            <td class="px-3 py-2 text-center">
                                <button wire:click="removeRow({{ $index }})" class="text-red-600 hover:text-red-900">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-between">
            <button wire:click="addRow" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                + Agregar Fila
            </button>

            <button wire:click="save" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                Guardar Todo
            </button>
        </div>
    </div>
</div>
