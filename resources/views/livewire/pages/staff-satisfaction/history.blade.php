<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\StaffSatisfactionResponse;
use App\Services\StaffSatisfactionCalculator;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    public $showEditModal = false;
    public $editingResponseId = null;
    public $editingData = [];
    
    protected $rules = [
        'editingData.personal' => 'required|string',
        'editingData.fecha' => 'required|date',
        'editingData.p1_mal' => 'boolean', 'editingData.p1_normal' => 'boolean', 'editingData.p1_bien' => 'boolean',
        'editingData.p2_mal' => 'boolean', 'editingData.p2_normal' => 'boolean', 'editingData.p2_bien' => 'boolean',
        'editingData.p3_mal' => 'boolean', 'editingData.p3_normal' => 'boolean', 'editingData.p3_bien' => 'boolean',
        'editingData.p4_mal' => 'boolean', 'editingData.p4_normal' => 'boolean', 'editingData.p4_bien' => 'boolean',
    ];

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'editingData.p')) {
            $field = str_replace('editingData.', '', $propertyName);
            if ($this->editingData[$field] === true) {
                $group = substr($field, 0, 2); // p1, p2, p3, p4
                $options = ['mal', 'normal', 'bien'];
                foreach ($options as $opt) {
                    $otherKey = $group . '_' . $opt;
                    if ($otherKey !== $field) {
                        $this->editingData[$otherKey] = false;
                    }
                }
            }
        }
    }

    public function edit($id)
    {
        $response = StaffSatisfactionResponse::findOrFail($id);
        $this->editingResponseId = $id;
        $this->editingData = [
            'personal' => $response->personal,
            'fecha' => $response->fecha ? $response->fecha->format('Y-m-d') : now()->format('Y-m-d'),
            'p1_mal' => (bool)$response->p1_mal, 'p1_normal' => (bool)$response->p1_normal, 'p1_bien' => (bool)$response->p1_bien,
            'p2_mal' => (bool)$response->p2_mal, 'p2_normal' => (bool)$response->p2_normal, 'p2_bien' => (bool)$response->p2_bien,
            'p3_mal' => (bool)$response->p3_mal, 'p3_normal' => (bool)$response->p3_normal, 'p3_bien' => (bool)$response->p3_bien,
            'p4_mal' => (bool)$response->p4_mal, 'p4_normal' => (bool)$response->p4_normal, 'p4_bien' => (bool)$response->p4_bien,
        ];
        $this->showEditModal = true;
    }

    public function update()
    {
        $this->validate();
        
        $response = StaffSatisfactionResponse::findOrFail($this->editingResponseId);
        $oldPeriodo = $response->fecha ? $response->fecha->format('Y-m') : null;
        
        $response->update($this->editingData);
        
        $newPeriodo = Carbon::parse($this->editingData['fecha'])->format('Y-m');
        
        // Recalcular stats
        $calculator = app(StaffSatisfactionCalculator::class);
        $calculator->updateMonthlyStats($newPeriodo);
        if ($oldPeriodo && $oldPeriodo !== $newPeriodo) {
            $calculator->updateMonthlyStats($oldPeriodo);
        }
        
        $this->showEditModal = false;
        session()->flash('message', 'Registro actualizado correctamente.');
    }

    public function delete($id)
    {
        $response = StaffSatisfactionResponse::find($id);
        if ($response) {
            $periodo = $response->fecha ? $response->fecha->format('Y-m') : null;
            $response->delete();
            
            if ($periodo) {
                app(StaffSatisfactionCalculator::class)->updateMonthlyStats($periodo);
            }
            session()->flash('message', 'Registro eliminado.');
        }
    }

    public function with()
    {
        return [
            'responses' => StaffSatisfactionResponse::orderByDesc('fecha')->orderByDesc('created_at')->paginate(20),
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
                            <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13zM7 13a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13z" clip-rule="evenodd"/></svg>
                            Módulo de Satisfacción
                        </div>
                        <h1 class="text-2xl font-bold text-white mb-1">Historial de Operarios</h1>
                        <p class="text-orange-100 text-sm">Histórico de encuestas realizadas al personal operativo</p>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('staff-satisfaction.create') }}" class="inline-flex items-center px-4 py-2 bg-white text-orange-600 font-bold rounded-lg hover:bg-orange-50 transition-all shadow-md active:scale-95 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nueva Encuesta
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="px-8 py-3 bg-orange-900/10 border-t border-white/10 flex flex-wrap gap-6 items-center">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-400 shadow-sm shadow-green-200"></span>
                    <span class="text-[10px] font-bold text-orange-50 uppercase tracking-wider">Bien</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 shadow-sm shadow-yellow-200"></span>
                    <span class="text-[10px] font-bold text-orange-50 uppercase tracking-wider">Normal</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-400 shadow-sm shadow-red-200"></span>
                    <span class="text-[10px] font-bold text-orange-50 uppercase tracking-wider">Mal / Incómodo</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if (session()->has('message'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-center text-green-700 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="p-2 bg-green-100 rounded-lg mr-4">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <span class="font-bold">{{ session('message') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Personal / Fecha</th>
                            <th class="px-3 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Trato Jefe</th>
                            <th class="px-3 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Trato Comp.</th>
                            <th class="px-3 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Clima</th>
                            <th class="px-3 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Comodidad</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($responses as $response)
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900">{{ $response->personal }}</span>
                                        <span class="text-xs font-medium text-gray-500">{{ $response->fecha ? $response->fecha->format('d/m/Y') : '-' }}</span>
                                    </div>
                                </td>
                                
                                <!-- P1 -->
                                <td class="px-3 py-4 text-center">
                                    <div class="flex justify-center">
                                        @if($response->p1_bien) <span class="w-6 h-6 rounded-full bg-green-500 border-4 border-green-100 shadow-sm" title="Bien"></span>
                                        @elseif($response->p1_normal) <span class="w-6 h-6 rounded-full bg-yellow-500 border-4 border-yellow-100 shadow-sm" title="Normal"></span>
                                        @elseif($response->p1_mal) <span class="w-6 h-6 rounded-full bg-red-500 border-4 border-red-100 shadow-sm" title="Mal"></span>
                                        @else <span class="text-gray-300">-</span> @endif
                                    </div>
                                </td>

                                <!-- P2 -->
                                <td class="px-3 py-4 text-center">
                                    <div class="flex justify-center">
                                        @if($response->p2_bien) <span class="w-6 h-6 rounded-full bg-green-500 border-4 border-green-100 shadow-sm" title="Bien"></span>
                                        @elseif($response->p2_normal) <span class="w-6 h-6 rounded-full bg-yellow-500 border-4 border-yellow-100 shadow-sm" title="Normal"></span>
                                        @elseif($response->p2_mal) <span class="w-6 h-6 rounded-full bg-red-500 border-4 border-red-100 shadow-sm" title="Mal"></span>
                                        @else <span class="text-gray-300">-</span> @endif
                                    </div>
                                </td>

                                <!-- P3 -->
                                <td class="px-3 py-4 text-center">
                                    <div class="flex justify-center">
                                        @if($response->p3_bien) <span class="w-6 h-6 rounded-full bg-green-500 border-4 border-green-100 shadow-sm" title="Buen Clima"></span>
                                        @elseif($response->p3_normal) <span class="w-6 h-6 rounded-full bg-yellow-500 border-4 border-yellow-100 shadow-sm" title="Clima Normal"></span>
                                        @elseif($response->p3_mal) <span class="w-6 h-6 rounded-full bg-red-500 border-4 border-red-100 shadow-sm" title="Mal Clima"></span>
                                        @else <span class="text-gray-300">-</span> @endif
                                    </div>
                                </td>

                                <!-- P4 -->
                                <td class="px-3 py-4 text-center">
                                    <div class="flex justify-center">
                                        @if($response->p4_bien) <span class="w-6 h-6 rounded-full bg-green-500 border-4 border-green-100 shadow-sm" title="Cómodo"></span>
                                        @elseif($response->p4_normal) <span class="w-6 h-6 rounded-full bg-yellow-500 border-4 border-yellow-100 shadow-sm" title="Normal"></span>
                                        @elseif($response->p4_mal) <span class="w-6 h-6 rounded-full bg-red-500 border-4 border-red-100 shadow-sm" title="Incómodo"></span>
                                        @else <span class="text-gray-300">-</span> @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="edit({{ $response->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="delete({{ $response->id }})" wire:confirm="¿Seguro que deseas eliminar este registro?" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($responses->hasPages())
                <div class="p-6 bg-gray-50/50 border-t border-gray-100">
                    {{ $responses->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                    <div class="bg-gradient-to-r from-orange-600 to-amber-500 px-8 py-6">
                        <h3 class="text-2xl font-bold text-white">Editar Encuesta</h3>
                        <p class="text-orange-100 font-medium">Actualiza los datos del operario</p>
                    </div>

                    <form wire:submit="update" class="p-8 space-y-8">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Personal</label>
                                <input type="text" wire:model="editingData.personal" class="w-full h-12 px-4 rounded-2xl bg-gray-50 border-gray-200 focus:ring-orange-500 focus:border-orange-500 transition-all font-medium">
                                @error('editingData.personal') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Fecha</label>
                                <input type="date" wire:model="editingData.fecha" class="w-full h-12 px-4 rounded-2xl bg-gray-50 border-gray-200 focus:ring-orange-500 focus:border-orange-500 transition-all font-medium">
                                @error('editingData.fecha') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Cuestionario en Modal -->
                        <div class="space-y-4">
                            <label class="block text-sm font-bold text-gray-700">Respuestas</label>
                            
                            <!-- P1 -->
                            <div class="flex items-center justify-between p-4 bg-blue-50/50 rounded-2xl border border-blue-100">
                                <span class="text-xs font-bold text-blue-900">1. Trato Jefe</span>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p1_mal" class="rounded text-red-600 focus:ring-red-500"><span class="text-[10px] text-gray-600 font-bold uppercase">M</span></label>
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p1_normal" class="rounded text-yellow-600 focus:ring-yellow-500"><span class="text-[10px] text-gray-600 font-bold uppercase">N</span></label>
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p1_bien" class="rounded text-green-600 focus:ring-green-500"><span class="text-[10px] text-gray-600 font-bold uppercase">B</span></label>
                                </div>
                            </div>

                            <!-- P2 -->
                            <div class="flex items-center justify-between p-4 bg-green-50/50 rounded-2xl border border-green-100">
                                <span class="text-xs font-bold text-green-900">2. Trato Comp.</span>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p2_mal" class="rounded text-red-600 focus:ring-red-500"><span class="text-[10px] text-gray-600 font-bold uppercase">M</span></label>
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p2_normal" class="rounded text-yellow-600 focus:ring-yellow-500"><span class="text-[10px] text-gray-600 font-bold uppercase">N</span></label>
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p2_bien" class="rounded text-green-600 focus:ring-green-500"><span class="text-[10px] text-gray-600 font-bold uppercase">B</span></label>
                                </div>
                            </div>

                            <!-- P3 -->
                            <div class="flex items-center justify-between p-4 bg-yellow-50/50 rounded-2xl border border-yellow-101">
                                <span class="text-xs font-bold text-yellow-900">3. Clima</span>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p3_mal" class="rounded text-red-600 focus:ring-red-500"><span class="text-[10px] text-gray-600 font-bold uppercase">M</span></label>
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p3_normal" class="rounded text-yellow-600 focus:ring-yellow-500"><span class="text-[10px] text-gray-600 font-bold uppercase">N</span></label>
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p3_bien" class="rounded text-green-600 focus:ring-green-500"><span class="text-[10px] text-gray-600 font-bold uppercase">B</span></label>
                                </div>
                            </div>

                            <!-- P4 -->
                            <div class="flex items-center justify-between p-4 bg-purple-50/50 rounded-2xl border border-purple-100">
                                <span class="text-xs font-bold text-purple-900">4. Comodidad</span>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p4_mal" class="rounded text-red-600 focus:ring-red-500"><span class="text-[10px] text-gray-600 font-bold uppercase">I</span></label>
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p4_normal" class="rounded text-yellow-600 focus:ring-yellow-500"><span class="text-[10px] text-gray-600 font-bold uppercase">N</span></label>
                                    <label class="flex items-center gap-1"><input type="checkbox" wire:model="editingData.p4_bien" class="rounded text-green-600 focus:ring-green-500"><span class="text-[10px] text-gray-600 font-bold uppercase">C</span></label>
                                </div>
                            </div>

                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="button" wire:click="$set('showEditModal', false)" class="flex-1 h-12 font-bold text-gray-500 bg-gray-100 rounded-2xl hover:bg-gray-200 transition-all">Cancelar</button>
                            <button type="submit" class="flex-2 px-8 h-12 font-bold text-white bg-gradient-to-r from-orange-600 to-amber-500 rounded-2xl hover:from-orange-700 hover:to-amber-600 transition-all shadow-lg active:scale-95">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
