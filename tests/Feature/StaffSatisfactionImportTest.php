<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\StaffSatisfactionResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StaffSatisfactionImportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles and Permissions
        $role = Role::create(['name' => 'Gestor de Satisfacción Personal']);
        Permission::create(['name' => 'view_staff_satisfaction']);
        $role->givePermissionTo('view_staff_satisfaction');

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    }

    public function test_route_access_protected()
    {
        $otherUser = User::factory()->create();
        
        $this->actingAs($otherUser)
             ->get(route('staff-satisfaction.dashboard'))
             ->assertForbidden();

        $this->actingAs($this->user)
             ->get(route('staff-satisfaction.dashboard'))
             ->assertOk();
    }

    public function test_manual_entry_saves_data()
    {
        Volt::test('pages.staff-satisfaction.manual-create')
            ->set('fecha', '2026-01-01')
            ->set('rows', [
                [
                    'personal' => 'Juan Perez',
                    'p1_mal' => true, 'p1_normal' => false, 'p1_bien' => false,
                    'p2_mal' => false, 'p2_normal' => true, 'p2_bien' => false,
                    'p3_mal' => false, 'p3_normal' => false, 'p3_bien' => true,
                    'p4_mal' => true, 'p4_normal' => false, 'p4_bien' => false,
                ]
            ])
            ->call('save')
            ->assertHasNoErrors();
            // ->assertRedirect(); // We just show a flash message usually, check component logic

        $this->assertDatabaseHas('staff_satisfaction_responses', [
             'personal' => 'Juan Perez',
             'p1_mal' => 1,
             'p2_normal' => 1,
             'p3_bien' => 1,
             'p4_mal' => 1,
        ]);
        
        // Verify Analysis triggered
        $this->assertDatabaseHas('staff_satisfaction_analysis', [
            'periodo' => '2026-01',
            'p1_mal_count' => 1
        ]);
    }
}
