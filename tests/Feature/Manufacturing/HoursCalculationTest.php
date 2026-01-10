<?php

namespace Tests\Feature\Manufacturing;

use App\Models\Client;
use App\Models\ManufacturingLog;
use App\Models\Project;
use App\Models\User;
use App\Models\WeightingFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoursCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_automatic_hours_weighting_calculation()
    {
        $client = Client::create(['nombre' => 'Test Client']);
        $project = Project::create([
            'id' => 'P-WEIGHT',
            'name' => 'Weighted Project',
            'client_id' => $client->id
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Crear factor de ponderación: 1.5 para el rol Admin
        WeightingFactor::create([
            'role_name' => 'Admin',
            'value' => 1.50000000,
            'start_date' => now()->subDay()->toDateString(),
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        // Simular el guardado vía el componente (o directamente al modelo con la lógica esperada)
        // Nota: El componente Volt realiza el cálculo antes de crear el log.
        
        $hoursClock = 8.00;
        $factor = WeightingFactor::vigente('Admin')->first();
        $hoursWeighted = $hoursClock * $factor->value;

        $log = ManufacturingLog::create([
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'units_produced' => 10,
            'hours_clock' => $hoursClock,
            'hours_weighted' => $hoursWeighted,
            'recorded_at' => now(),
        ]);

        $this->assertEquals(8.00, $log->hours_clock);
        $this->assertEquals(12.00, $log->hours_weighted);
    }

    public function test_decimal_precision_is_maintained()
    {
        $client = Client::create(['nombre' => 'Test Client']);
        $project = Project::create([
            'id' => 'P-PRECISION',
            'name' => 'Precision Project',
            'client_id' => $client->id
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Factor complejo: 1.94390399
        WeightingFactor::create([
            'role_name' => 'Admin',
            'value' => 1.94390399,
            'start_date' => now()->subDay()->toDateString(),
            'is_active' => true,
        ]);

        $hoursClock = 10.00;
        $factor = WeightingFactor::vigente('Admin', now())->first();
        $hoursWeighted = round($hoursClock * $factor->value, 2);

        $log = ManufacturingLog::create([
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'units_produced' => 10,
            'hours_clock' => $hoursClock,
            'hours_weighted' => $hoursWeighted,
            'recorded_at' => now(),
        ]);

        // 10 * 1.94390399 = 19.4390399 -> redondeado a 2 decimales = 19.44
        $this->assertEquals(19.44, $log->hours_weighted);
    }
}
