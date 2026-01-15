<?php

use Livewire\Volt\Component;
use App\Models\PurchaseDetail;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    public string $moneda = 'USD';
    public string $cc = '';
    public int $ano;
    public string $empresa = '';
    public string $descripcion = '';
    public float $materiales_presupuestados = 0;
    public float $materiales_comprados = 0;
    public float $porcentaje_facturacion = 0;

    public function mount()
    {
        $this->ano = date('Y');
    }

    // Computed properties for calculated fields
    public function with()
    {
        $resto_valor = $this->materiales_presupuestados - $this->materiales_comprados;
        $resto_porcentaje = $this->materiales_presupuestados > 0 
            ? ($resto_valor / $this->materiales_presupuestados) * 100 
            : 0;

        return [
            'resto_valor' => $resto_valor,
            'resto_porcentaje' => $resto_porcentaje
        ];
    }

    public function save()
    {
        $this->validate([
            'moneda' => 'required|string',
            'cc' => 'required|string',
            'ano' => 'required|integer|min:2000|max:2099',
            'empresa' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'materiales_presupuestados' => 'required|numeric|min:0',
            'materiales_comprados' => 'required|numeric|min:0',
            'porcentaje_facturacion' => 'required|numeric|min:0|max:100',
        ]);

        // Calculate final values
        $resto_valor = $this->materiales_presupuestados - $this->materiales_comprados;
        $resto_porcentaje = $this->materiales_presupuestados > 0 
            ? ($resto_valor / $this->materiales_presupuestados) * 100 
            : 0;

        // Generate Hash
        $hash = PurchaseDetail::generateHash($this->cc, (string)$this->ano, $this->empresa, $this->descripcion);

        if (PurchaseDetail::existsByHash($hash)) {
            $this->addError('duplicate', 'Ya existe un registro con estos datos (CC, Año, Empresa, Descripción).');
            return;
        }

        PurchaseDetail::create([
            'moneda' => $this->moneda,
            'cc' => $this->cc,
            'ano' => $this->ano,
            'empresa' => $this->empresa,
            'descripcion' => $this->descripcion,
            'materiales_presupuestados' => $this->materiales_presupuestados,
            'materiales_comprados' => $this->materiales_comprados,
            'resto_valor' => $resto_valor,
            'resto_porcentaje' => $resto_porcentaje,
            'porcentaje_facturacion' => $this->porcentaje_facturacion,
            'hash' => $hash,
        ]);

        $redirectRoute = auth()->user()->hasRole('Manager') ? 'manager.historial.compras' : (auth()->user()->hasRole('Gestor de Compras') ? 'purchases.historial.importacion' : 'admin.historial.compras');
        return redirect()->route($redirectRoute)->with('status', 'Compra registrada correctamente.');
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Registrar Compra de Materiales</h2>
                    <p class="text-gray-600">Ingrese los detalles de la compra manualmente.</p>
                </div>

                @if($errors->has('duplicate'))
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        {{ $errors->first('duplicate') }}
                    </div>
                @endif

                <form wire:submit="save" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Columna 1 -->
                        <div class="space-y-4">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Moneda</label>
                                <select wire:model="moneda" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                    <option value="USD">USD - Dólar Estadounidense</option>
                                    <option value="ARS">ARS - Peso Argentino</option>
                                    <option value="EUR">EUR - Euro</option>
                                </select>
                                @error('moneda') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Centro de Costo (CC)</label>
                                <input type="text" wire:model="cc" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                @error('cc') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Año</label>
                                <input type="number" wire:model="ano" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                @error('ano') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Empresa</label>
                                <input type="text" wire:model="empresa" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                @error('empresa') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Descripción</label>
                                <input type="text" wire:model="descripcion" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Columna 2: Financiera -->
                        <div class="space-y-4 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-gray-700 mb-2 border-b pb-2">Datos Financieros</h3>
                            
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Materiales Presupuestados</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" step="0.01" wire:model.live="materiales_presupuestados" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                @error('materiales_presupuestados') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Materiales Comprados</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" step="0.01" wire:model.live="materiales_comprados" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                @error('materiales_comprados') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium text-sm text-gray-500">Resto (Valor)</label>
                                    <div class="mt-1 p-2 bg-gray-200 rounded text-right font-mono {{ $resto_valor < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($resto_valor, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-500">Resto (%)</label>
                                    <div class="mt-1 p-2 bg-gray-200 rounded text-right font-mono {{ $resto_porcentaje < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($resto_porcentaje, 1) }}%
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">% de Facturación</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <input type="number" step="0.01" wire:model="porcentaje_facturacion" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-8">
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                                @error('porcentaje_facturacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-6 pt-6 border-t border-gray-200">
                        <a href="{{ route(auth()->user()->hasRole('Manager') ? 'manager.historial.compras' : (auth()->user()->hasRole('Gestor de Compras') ? 'purchases.historial.importacion' : 'admin.historial.compras')) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Guardar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
