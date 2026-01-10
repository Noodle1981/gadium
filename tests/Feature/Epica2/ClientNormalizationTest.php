<?php

namespace Tests\Feature\Epica2;

use Livewire\Volt\Volt;
use App\Models\Client;
use App\Models\ClientAlias;
use App\Services\ClientNormalizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientNormalizationTest extends TestCase
{
    use RefreshDatabase;

    protected ClientNormalizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClientNormalizationService();
    }

    public function test_resolution_page_loads_with_client_name(): void
    {
        Volt::test('pages.clients.resolution', ['client_name' => 'TRIELEC'])
            ->assertSet('clientName', 'TRIELEC')
            ->assertSee('TRIELEC');
    }

    public function test_can_resolve_by_linking_alias_via_livewire(): void
    {
        $client = Client::create(['nombre' => 'TRIELEC S.A.']);
        
        Volt::test('pages.clients.resolution', ['client_name' => 'TRIELEC'])
            ->call('resolve', 'link', $client->id)
            ->assertSet('successMessage', "Cliente vinculado exitosamente. 'TRIELEC' ahora es un alias.")
            ->assertSet('clientName', '');

        $this->assertDatabaseHas('client_aliases', [
            'client_id' => $client->id,
            'alias' => 'trielec',
        ]);
    }

    public function test_can_resolve_by_creating_new_client_via_livewire(): void
    {
        Volt::test('pages.clients.resolution', ['client_name' => 'New ClientCorp'])
            ->call('resolve', 'create')
            ->assertSet('successMessage', "Nuevo cliente creado: New ClientCorp");

        $this->assertDatabaseHas('clients', [
            'nombre' => 'New ClientCorp',
            'nombre_normalizado' => 'new clientcorp',
        ]);
    }

    public function test_levenshtein_similarity_calculation(): void
    {
        // Strings idénticos
        $similarity1 = $this->service->calculateSimilarity('test', 'test');
        $this->assertEquals(100.0, $similarity1);

        // Strings similares
        $similarity2 = $this->service->calculateSimilarity('TRIELEC S.A.', 'TRIELEC S A');
        $this->assertGreaterThan(80, $similarity2);
    }

    public function test_normalizes_client_names_correctly(): void
    {
        $normalized1 = Client::normalizeClientName('TRIELEC S.A.');
        $normalized2 = Client::normalizeClientName('TRIELEC S A');

        $this->assertEquals($normalized1, $normalized2);
        $this->assertEquals('trielec s a', $normalized1);
    }
}
