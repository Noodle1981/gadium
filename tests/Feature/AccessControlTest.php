<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $superAdmin = User::where('email', 'admin@gaudium.com')->first();

        $this->assertTrue($superAdmin->can('view_users'));
        $this->assertTrue($superAdmin->can('create_users'));
        $this->assertTrue($superAdmin->can('view_roles'));
        $this->assertTrue($superAdmin->can('create_roles'));
    }

    public function test_admin_has_correct_permissions(): void
    {
        $admin = User::where('email', 'administrador@gaudium.com')->first();

        $this->assertTrue($admin->can('view_users'));
        $this->assertTrue($admin->can('create_users'));
        $this->assertTrue($admin->can('view_roles'));
        $this->assertFalse($admin->can('create_roles'));
    }

    public function test_manager_has_limited_permissions(): void
    {
        $manager = User::where('email', 'gerente@gaudium.com')->first();

        $this->assertTrue($manager->can('view_users'));
        $this->assertFalse($manager->can('create_users'));
        $this->assertFalse($manager->can('view_roles'));
    }

    public function test_viewer_has_read_only_permissions(): void
    {
        $viewer = User::where('email', 'viewer@gaudium.com')->first();

        $this->assertTrue($viewer->can('view_sales'));
        $this->assertFalse($viewer->can('create_sales'));
        $this->assertFalse($viewer->can('view_users'));
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->get('/users');
        $response->assertRedirect('/login');

        $response = $this->get('/roles');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }
}
