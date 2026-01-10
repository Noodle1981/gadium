<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Epica3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener clientes existentes
        $clients = \App\Models\Client::all();
        if ($clients->isEmpty()) {
            $clients = collect([\App\Models\Client::create(['nombre' => 'Cliente Demo'])]);
        }

        // Crear Proyectos
        $p3400 = \App\Models\Project::create([
            'id' => '3400',
            'name' => 'Manga Retráctil Calidra',
            'client_id' => $clients->first()->id,
        ]);

        $p3500 = \App\Models\Project::create([
            'id' => '3500',
            'name' => 'Techado Galpón Techint',
            'client_id' => $clients->first()->id,
        ]);

        // Crear algunos registros de producción
        \App\Models\ManufacturingLog::create([
            'project_id' => '3400',
            'user_id' => 1,
            'units_produced' => 10,
            'correction_documents' => 2, // 20%
        ]);

        \App\Models\ManufacturingLog::create([
            'project_id' => '3400',
            'user_id' => 1,
            'units_produced' => 5,
            'correction_documents' => 3, // Total rate will be (5/15) = 33%
        ]);
    }
}
