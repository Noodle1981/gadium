<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Epica6SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeders necesarios en el orden correcto
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /** @test */
    public function viewer_can_access_intelligence_dashboard()
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('Viewer');

        $response = $this->actingAs($viewer)->get(route('viewer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Inteligencia de Negocios');
        $response->assertSee('Torre de Control Grafana');
    }

    /** @test */
    public function viewer_cannot_access_admin_dashboard()
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('Viewer');

        $response = $this->actingAs($viewer)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function manager_can_access_management_dashboard()
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $response = $this->actingAs($manager)->get(route('manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Panel de Gestión');
    }

    /** @test */
    public function manager_cannot_access_role_management()
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $response = $this->actingAs($manager)->get('/admin/roles');

        $response->assertStatus(403);
    }

    /** @test */
    public function sidebar_shows_correct_links_for_viewer()
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('Viewer');

        $response = $this->actingAs($viewer)->get(route('viewer.dashboard'));

        $response->assertSee('Dashboard');
        $response->assertSee('Grafana');
        $response->assertDontSee('Usuarios');
        $response->assertDontSee('Roles');
    }

    /** @test */
    public function metrics_api_is_protected_by_sanctum()
    {
        $response = $this->getJson('/api/v1/metrics/sales-concentration');

        $response->assertStatus(401); // Unauthenticated
    }
}
