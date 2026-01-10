<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientAlias;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class Epica2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('   → Creando clientes de prueba...');
        
        // Crear 10 clientes de ejemplo
        $clients = [
            'TRIELEC S.A.',
            'Saint Gobain Argentina',
            'Techint Ingeniería',
            'YPF S.A.',
            'Arcor SAIC',
            'Molinos Río de la Plata',
            'Grupo Clarín',
            'Telecom Argentina',
            'Mercado Libre',
            'Globant S.A.',
        ];

        $clientModels = [];
        foreach ($clients as $clientName) {
            $clientModels[] = Client::create([
                'nombre' => $clientName,
            ]);
        }

        $this->command->info('   → Creando aliases de prueba...');
        
        // Crear 5 aliases de ejemplo para testing de normalización
        $aliases = [
            ['client_id' => $clientModels[0]->id, 'alias' => 'TRIELEC S A'],
            ['client_id' => $clientModels[0]->id, 'alias' => 'TRIELEC SA'],
            ['client_id' => $clientModels[1]->id, 'alias' => 'Saint-Gobain'],
            ['client_id' => $clientModels[2]->id, 'alias' => 'Techint'],
            ['client_id' => $clientModels[3]->id, 'alias' => 'YPF'],
        ];

        foreach ($aliases as $alias) {
            ClientAlias::create($alias);
        }

        $this->command->info('   → Creando ventas de prueba...');
        
        // Crear 50 ventas de ejemplo
        $comprobantes = ['FC', 'NC', 'ND', 'REC'];
        $startDate = Carbon::now()->subMonths(6);
        
        for ($i = 1; $i <= 50; $i++) {
            $client = $clientModels[array_rand($clientModels)];
            $fecha = $startDate->copy()->addDays(rand(0, 180));
            $monto = rand(1000, 100000) + (rand(0, 99) / 100);
            $tipoComp = $comprobantes[array_rand($comprobantes)];
            $comprobante = $tipoComp . '-' . str_pad($i, 8, '0', STR_PAD_LEFT);

            Sale::create([
                'fecha' => $fecha,
                'client_id' => $client->id,
                'cliente_nombre' => $client->nombre,
                'monto' => $monto,
                'comprobante' => $comprobante,
                'hash' => Sale::generateHash(
                    $fecha->format('Y-m-d'),
                    $client->nombre,
                    $comprobante,
                    $monto
                ),
            ]);
        }

        $this->command->info('   ✓ Épica 2: 10 clientes, 5 aliases, 50 ventas creadas');
    }
}
