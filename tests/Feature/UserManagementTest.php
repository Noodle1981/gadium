<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_admin_can_view_users_list(): void
    {
        $admin = User::where('email', 'admin@gaudium.com')->first();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::where('email', 'admin@gaudium.com')->first();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'test@gaudium.com',
            'role' => 'Viewer',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@gaudium.com',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::where('email', 'admin@gaudium.com')->first();
        $user = User::where('email', 'viewer@gaudium.com')->first();

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'Manager',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::where('email', 'admin@gaudium.com')->first();
        $user = User::where('email', 'viewer@gaudium.com')->first();

        $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}");

        $response->assertRedirect(route('users.index'));
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_super_admin_cannot_be_deleted(): void
    {
        $superAdmin = User::where('email', 'admin@gaudium.com')->first();
        $admin = User::where('email', 'administrador@gaudium.com')->first();

        // Intentar que Admin elimine al Super Admin (debería fallar)
        $response = $this->actingAs($admin)->delete("/admin/users/{$superAdmin->id}");

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_viewer_cannot_access_users(): void
    {
        $viewer = User::where('email', 'viewer@gaudium.com')->first();

        $response = $this->actingAs($viewer)->get('/admin/users');

        $response->assertStatus(403);
    }
}
