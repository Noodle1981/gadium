<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    /** @test */
    public function admin_can_access_admin_routes()
    {
        $this->withoutExceptionHandling();
        $admin = User::where('email', 'administrador@gaudium.com')->first();
        $this->actingAs($admin);
        // dd(auth()->user()->toArray()); 

        try {
            $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));
            $this->get('/admin/dashboard')->assertStatus(200);
            $this->get('/admin/users')->assertStatus(200);
        } catch (\Throwable $e) {
            file_put_contents('exception_error.txt', $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
        $response = $this->get('/admin/sales/import');
        if ($response->status() === 500) {
            file_put_contents('debug_500.html', $response->getContent());
        }
        $response->assertStatus(200);
        $this->get('/admin/clients/resolve')->assertStatus(200);
    }

    /** @test */
    public function manager_can_access_manager_routes()
    {
        $this->withoutExceptionHandling();
        $manager = User::where('email', 'gerente@gaudium.com')->first();
        $this->actingAs($manager);

        $this->get('/dashboard')->assertRedirect(route('manager.dashboard'));
        $this->get('/manager/dashboard')->assertStatus(200);
        $this->get('/manager/sales/import')->assertStatus(200);
        $this->get('/manager/clients/resolve')->assertStatus(200);
    }

    /** @test */
    public function manager_cannot_access_admin_routes()
    {
        $manager = User::where('email', 'gerente@gaudium.com')->first();
        $this->actingAs($manager);

        $this->get('/admin/users')->assertStatus(403);
    }

    /** @test */
    public function routes_redirect_correctly_to_role_prefix()
    {
        $admin = User::where('email', 'administrador@gaudium.com')->first();
        $this->actingAs($admin);

        $this->get('/sales/import')->assertRedirect(route('admin.sales.import'));
        $this->get('/clients/resolve')->assertRedirect(route('admin.clients.resolve'));

        $manager = User::where('email', 'gerente@gaudium.com')->first();
        $this->actingAs($manager);

        $this->get('/sales/import')->assertRedirect(route('manager.sales.import'));
        $this->get('/clients/resolve')->assertRedirect(route('manager.clients.resolve'));
    }
}
