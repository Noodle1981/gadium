<?php

namespace Tests\Feature\Epica2;

use App\Models\User;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SalesImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
        Storage::fake('local');
    }

    public function test_can_upload_valid_csv_via_livewire(): void
    {
        $superAdmin = User::where('email', 'admin@gaudium.com')->first();
        $this->actingAs($superAdmin);

        $csv = "fecha,cliente,monto,comprobante\n2025-01-01,Test Client,1000.50,FC-0001";
        $file = UploadedFile::fake()->createWithContent('sales.csv', $csv);

        Volt::test('pages.sales.import-wizard')
            ->set('file', $file)
            ->call('upload')
            ->assertSet('step', 2)
            ->assertSet('totalRows', 1)
            ->assertSee('Test Client');
    }

    public function test_rejects_csv_with_missing_columns_via_livewire(): void
    {
        $superAdmin = User::where('email', 'admin@gaudium.com')->first();
        $this->actingAs($superAdmin);

        $csv = "fecha,cliente,comprobante\n2025-01-01,Test Client,FC-0001";
        $file = UploadedFile::fake()->createWithContent('sales.csv', $csv);

        Volt::test('pages.sales.import-wizard')
            ->set('file', $file)
            ->call('upload')
            ->assertSet('step', 1)
            ->assertSee('Error: Columna \'monto\' no encontrada');
    }

    public function test_can_complete_full_import_flow(): void
    {
        $superAdmin = User::where('email', 'admin@gaudium.com')->first();
        $this->actingAs($superAdmin);

        $csv = "fecha,cliente,monto,comprobante\n2025-01-01,Import Test,500.00,FC-999";
        $file = UploadedFile::fake()->createWithContent('sales.csv', $csv);

        Volt::test('pages.sales.import-wizard')
            ->set('file', $file)
            ->call('upload')
            ->call('process')
            ->assertSet('step', 3)
            ->assertSee('Importación Finalizada')
            ->assertSee('Nuevos');
            
        $this->assertDatabaseHas('sales', ['cliente_nombre' => 'Import Test']);
    }
}
