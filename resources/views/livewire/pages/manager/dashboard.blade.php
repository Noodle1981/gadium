<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Sale;
use App\Models\Client;

new #[Layout('layouts.app')] class extends Component {
    public int $saleCount = 0;
    public int $clientCount = 0;

    public function mount() {
        $this->saleCount = Sale::count();
        $this->clientCount = Client::count();
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded">
                        <p class="text-sm text-gray-600 uppercase font-bold">Ventas Importadas</p>
                        <p class="text-3xl font-bold">{{ $saleCount }}</p>
                    </div>
                    <div class="p-4 bg-purple-50 border-l-4 border-purple-500 rounded">
                        <p class="text-sm text-gray-600 uppercase font-bold">Clientes Normalizados</p>
                        <p class="text-3xl font-bold">{{ $clientCount }}</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Operaciones</h3>
                    <div class="flex space-x-4">
                        <a href="{{ route('manager.sales.import') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded transition">
                            Nueva Importación
                        </a>
                        <a href="{{ route('manager.clients.resolve') }}" class="bg-gray-800 hover:bg-black text-white font-bold py-2 px-4 rounded transition">
                            Resolver Clientes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
