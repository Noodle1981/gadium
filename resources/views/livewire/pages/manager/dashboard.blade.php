<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Sale;
use App\Models\Client;

new #[Layout('layouts.app')] class extends Component {
    public int $saleCount = 0;
    public int $clientCount = 0;
    public float $monthlyWeightedHours = 0;
    public float $targetHours = 3394.0;
    public float $efficiencyPercentage = 0;

    public function mount() {
        $this->saleCount = Sale::count();
        $this->clientCount = Client::count();
        
        // Calcular horas ponderadas del mes actual
        $this->monthlyWeightedHours = \App\Models\ManufacturingLog::whereMonth('recorded_at', now()->month)
            ->whereYear('recorded_at', now()->year)
            ->sum('hours_weighted');
            
        if ($this->targetHours > 0) {
            $this->efficiencyPercentage = ($this->monthlyWeightedHours / $this->targetHours) * 100;
        }
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manager Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-bold">Ventas Importadas</p>
                        <p class="text-2xl font-bold">{{ $saleCount }}</p>
                    </div>
                    <div class="p-4 bg-purple-50 border-l-4 border-purple-500 rounded shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-bold">Clientes</p>
                        <p class="text-2xl font-bold">{{ $clientCount }}</p>
                    </div>
                    <div class="p-4 bg-orange-50 border-l-4 border-orange-500 rounded shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-bold">Horas Ponderadas (Mes)</p>
                        <p class="text-2xl font-bold">{{ number_format($monthlyWeightedHours, 2) }}</p>
                        <p class="text-[10px] text-gray-400">Meta: {{ number_format($targetHours) }}h</p>
                    </div>
                    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-bold">Eficiencia vs Meta</p>
                        <div class="flex items-center">
                            <p class="text-2xl font-bold">{{ number_format($efficiencyPercentage, 1) }}%</p>
                            <div class="ml-2 w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(100, $efficiencyPercentage) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Operaciones de Datos</h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('manager.sales.import') }}" class="bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold py-2 px-4 rounded transition">
                                Importar Ventas
                            </a>
                            <a href="{{ route('manager.clients.resolve') }}" class="bg-gray-800 hover:bg-black text-white text-sm font-bold py-2 px-4 rounded transition">
                                Resolver Clientes
                            </a>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Registro Operativo</h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('manager.manufacturing.production.log') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded transition">
                                Registrar Producción/Horas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
