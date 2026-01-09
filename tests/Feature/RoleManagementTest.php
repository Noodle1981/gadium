<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_super_admin_can_view_roles(): void
    {
        $superAdmin = User::where('email', 'admin@gaudium.com')->first();

        $response = $this->actingAs($superAdmin)->get('/roles');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_create_role(): void
    {
        $superAdmin = User::where('email', 'admin@gaudium.com')->first();

        $response = $this->actingAs($superAdmin)->post('/roles', [
            'name' => 'Operario',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', [
            'name' => 'Operario',
        ]);
    }

    public function test_super_admin_can_assign_permissions_to_role(): void
    {
        $superAdmin = User::where('email', 'admin@gaudium.com')->first();
        $role = Role::where('name', 'Viewer')->first();

        $response = $this->actingAs($superAdmin)->post("/roles/{$role->id}/permissions", [
            'permissions' => ['view_users', 'view_sales'],
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertTrue($role->hasPermissionTo('view_users'));
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $superAdmin = User::where('email', 'admin@gaudium.com')->first();
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        $response = $this->actingAs($superAdmin)->delete("/roles/{$superAdminRole->id}");

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', [
            'name' => 'Super Admin',
        ]);
    }

    public function test_admin_cannot_access_roles(): void
    {
        $admin = User::where('email', 'administrador@gaudium.com')->first();

        $response = $this->actingAs($admin)->get('/roles');

        $response->assertStatus(403);
    }
}
