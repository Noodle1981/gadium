<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup básico si es necesario
    }

    public function test_endpoints_are_protected_by_sanctum()
    {
        $response = $this->getJson('/api/v1/metrics/production-efficiency');
        $response->assertStatus(401); // Unauthorized
    }

    public function test_authenticated_user_can_access_efficiency_mock()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['view-metrics']);

        $response = $this->getJson('/api/v1/metrics/production-efficiency');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'meta' => ['note', 'generated_at'],
                'data' => [
                    '*' => ['month', 'target_hours', 'actual_weighted_hours', 'efficiency_percentage']
                ]
            ]);
    }

    public function test_sales_concentration_calculation()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['view-metrics']);

        // Crear datos de prueba (Pareto)
        // 1 Cliente "Whale" (Ballena) con muchas ventas (80%)
        // 4 Clientes "Minnows" (Pececillos) con pocas ventas (20%)
        
        $whale = Client::create(['nombre' => 'Whale Client']);
        Sale::create([
            'client_id' => $whale->id,
            'cliente_nombre' => 'Whale Client',
            'fecha' => now(),
            'monto' => 80000,
            'moneda' => 'ARS',
            'comprobante' => 'FV-0001',
            'hash' => 'hash_whale_001'
        ]);

        $minnowsAmount = 5000;
        for ($i = 0; $i < 4; $i++) {
            $c = Client::create(['nombre' => 'Minnow ' . $i]);
            Sale::create([
                'client_id' => $c->id,
                'cliente_nombre' => 'Minnow ' . $i,
                'fecha' => now(),
                'monto' => $minnowsAmount,
                'moneda' => 'ARS',
                'comprobante' => 'FV-MIN-' . $i,
                'hash' => 'hash_minnow_' . $i
            ]);
        }

        // Total = 80000 + (5000 * 4) = 100000
        // Top 20% de 5 clientes = 1 cliente
        // Revenue de Top Client = 80000
        // Concentración = 80%

        $response = $this->getJson('/api/v1/metrics/sales-concentration');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total_revenue' => 100000,
                    'total_active_clients' => 5,
                    'top_20_count' => 1,
                    'pareto_concentration_percentage' => 80,
                    // 'risk_level' => 'NORMAL', // <= 80 es normal, > 80 es alto. Justo en el límite.
                ]
            ]);
            
        // Verificar estructura del Top Clients
        $response->assertJsonStructure([
            'data' => [
                'top_clients' => [
                    '*' => ['name', 'revenue', 'share']
                ]
            ]
        ]);
    }
}
