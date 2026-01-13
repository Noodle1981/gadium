<?php

namespace Tests\Feature\Imports;

use App\Models\Budget;
use App\Models\Client;
use App\Models\Sale;
use App\Models\User;
use App\Services\CsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CsvImportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create user with permission (assuming 'view_sales' is enough or 'admin')
        $this->user = User::factory()->create();
        // Assign role/permission if needed. For now assuming middleware passes with factory user, 
        // or I might need to actAs a specific role. 
        // Based on web.php: middleware(['role:Super Admin|Admin|Manager'])
        // I will need to seed permissions or mock checks. 
        // For simplicity, I will assume actingAs($user) works if I don't test middleware strictly here, usually.
        // But web.php has specific middleware. I should create a user with that role.
        
        $role = \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
        $permission = \Spatie\Permission\Models\Permission::create(['name' => 'view_sales']);
        $role->givePermissionTo($permission);
        $this->user->assignRole($role);
    }

    public function test_can_render_import_wizard()
    {
        $this->actingAs($this->user)
            ->get(route('admin.sales.import'))
            ->assertOk()
            ->assertSee('Tipo de Importación');
    }

    public function test_rejects_malformed_headers()
    {
        $this->withoutExceptionHandling();
        $file = UploadedFile::fake()->createWithContent('test.csv', "Fecha,ColumnaMal,Monto\n2025-01-01,Test,100");

        Volt::test('pages.sales.import-wizard')
            ->set('file', $file)
            ->assertHasErrors(['file']);
    }

    public function test_accepts_valid_csv_and_detects_unknown_client()
    {
        $content = "Fecha,Cliente,Monto,Comprobante\n01/01/2025,Cliente Nuevo,100.00,A001";
        $file = UploadedFile::fake()->createWithContent('sales.csv', $content);

        Volt::test('pages.sales.import-wizard')
            ->set('type', 'sale')
            ->set('file', $file)
            ->assertSet('step', 2) // Should go to resolution
            ->assertSee('Cliente Nuevo');
    }

    public function test_auto_resolves_known_client()
    {
        Client::create(['nombre' => 'Cliente Conocido', 'nombre_normalizado' => 'cliente conocido']);
        
        $content = "Fecha,Cliente,Monto,Comprobante\n01/01/2025,Cliente Conocido,100.00,A001";
        $file = UploadedFile::fake()->createWithContent('sales.csv', $content);

        Volt::test('pages.sales.import-wizard')
            ->set('file', $file)
            ->assertSet('step', 3); // Validation Passed directly
    }

    public function test_service_rejects_malformed_csv()
    {
        $content = "Fecha,ColumnaMal,Monto\n2025-01-01,Test,100";
        $path = sys_get_temp_dir() . '/bad_csv.csv';
        file_put_contents($path, $content);

        $service = app(CsvImportService::class);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cabecera faltante');
        
        $service->validateAndAnalyze($path, 'sale');
    }

    public function test_import_process_is_idempotent()
    {
        // Setup Client
        $client = Client::create(['nombre' => 'Cliente Test', 'nombre_normalizado' => 'cliente test']);
        
        // Mock File
        $content = "Fecha,Cliente,Monto,Moneda\n01/01/2025,Cliente Test,500.00,USD";
        $path = sys_get_temp_dir() . '/budget_test.csv';
        file_put_contents($path, $content);
        
        // Run Service Directly to test Idempotency logic
        // (Avoiding Job dispatch for sync testing)
        $service = app(CsvImportService::class);
        $normService = app(\App\Services\ClientNormalizationService::class);
        
        // 1st Run
        $rows = [
            ['Fecha' => '01/01/2025', 'Cliente' => 'Cliente Test', 'Monto' => '500.00', 'Moneda' => 'USD']
        ];
        
        $stats1 = $service->importChunk($rows, 'budget');
        $this->assertEquals(1, $stats1['inserted']);
        $this->assertEquals(0, $stats1['skipped']);
        $this->assertEquals(1, Budget::count());

        // 2nd Run (Same Data)
        $stats2 = $service->importChunk($rows, 'budget');
        $this->assertEquals(0, $stats2['inserted']);
        $this->assertEquals(1, $stats2['skipped']);
        $this->assertEquals(1, Budget::count()); // Count should not increase
    }
}
