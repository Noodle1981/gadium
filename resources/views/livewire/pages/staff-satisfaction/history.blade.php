<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\StaffSatisfactionResponse;
use App\Services\StaffSatisfactionCalculator;

new class extends Component {
    use WithPagination;

    public function delete($id)
    {
        $response = StaffSatisfactionResponse::find($id);
        if ($response) {
            $periodo = $response->fecha ? $response->fecha->format('Y-m') : null;
            $response->delete();
            
            // Recalcular stats
            if ($periodo) {
                app(StaffSatisfactionCalculator::class)->updateMonthlyStats($periodo);
            }
            
            // Flash message?
        }
    }

    public function with()
    {
        return [
            'responses' => StaffSatisfactionResponse::orderByDesc('created_at')->paginate(20),
        ];
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Historial de Importación (Operarios)</h2>
                <a href="{{ route('staff-satisfaction.dashboard') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                    &larr; Volver al Dashboard
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Encuesta</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($responses as $response)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $response->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $response->fecha ? $response->fecha->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ $response->personal }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="delete({{ $response->id }})" 
                                            wire:confirm="¿Está seguro de eliminar este registro?"
                                            class="text-red-600 hover:text-red-900">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $responses->links() }}
            </div>
        </div>
    </div>
</div>
