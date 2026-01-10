<?php

namespace Tests\Feature\Manufacturing;

use App\Models\Client;
use App\Models\ManufacturingLog;
use App\Models\Project;
use App\Models\User;
use App\Notifications\CriticalQualityAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class QualityMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_project_calculates_error_rate_correctly()
    {
        $client = Client::create(['nombre' => 'Test Client']);
        $project = Project::create([
            'id' => 'P-TEST',
            'name' => 'Test Project',
            'client_id' => $client->id
        ]);

        ManufacturingLog::create([
            'project_id' => $project->id,
            'user_id' => User::factory()->create()->id,
            'units_produced' => 10,
            'correction_documents' => 2
        ]);

        $this->assertEquals(20, $project->fresh()->error_rate);

        ManufacturingLog::create([
            'project_id' => $project->id,
            'user_id' => User::factory()->create()->id,
            'units_produced' => 5,
            'correction_documents' => 3
        ]);

        // Total: 5 corrections / 15 units = 33.33%
        $this->assertEquals(33.33, $project->fresh()->error_rate);
    }

    public function test_quality_status_becomes_critical_when_threshold_exceeded()
    {
        Notification::fake();

        $client = Client::create(['nombre' => 'Test Client']);
        $project = Project::create([
            'id' => 'P-ALERTA',
            'name' => 'Alerta Project',
            'client_id' => $client->id
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Primera carga: 10% (debajo del 20%)
        $log1 = ManufacturingLog::create([
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'units_produced' => 10,
            'correction_documents' => 1
        ]);
        \App\Events\ProductionLogCreated::dispatch($log1);

        $this->assertEquals('normal', $project->fresh()->quality_status);
        Notification::assertNothingSent();

        // Segunda carga: Eleva a 40% (supera el 20%)
        $log2 = ManufacturingLog::create([
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'units_produced' => 10,
            'correction_documents' => 7
        ]);
        \App\Events\ProductionLogCreated::dispatch($log2);

        $this->assertEquals('crítico', $project->fresh()->quality_status);
        Notification::assertSentTo(
            [$admin],
            CriticalQualityAlert::class
        );
    }

    public function test_quality_status_reverts_to_normal_if_new_production_dilutes_errors()
    {
        $client = Client::create(['nombre' => 'Test Client']);
        $project = Project::create([
            'id' => 'P-REVERT',
            'name' => 'Revert Project',
            'client_id' => $client->id,
            'quality_status' => 'crítico'
        ]);

        // Estado inicial crítico: 5/10 = 50%
        ManufacturingLog::create([
            'project_id' => $project->id,
            'user_id' => User::factory()->create()->id,
            'units_produced' => 10,
            'correction_documents' => 5
        ]);

        // Nueva carga masiva limpia: 0/100
        // Total: 5/110 = 4.54%
        $log = ManufacturingLog::create([
            'project_id' => $project->id,
            'user_id' => User::factory()->create()->id,
            'units_produced' => 100,
            'correction_documents' => 0
        ]);
        \App\Events\ProductionLogCreated::dispatch($log);

        $this->assertEquals('normal', $project->fresh()->quality_status);
    }
}
