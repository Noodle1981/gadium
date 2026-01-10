<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientAlias;
use Illuminate\Support\Collection;

class ClientNormalizationService
{
    /**
     * Umbral de similitud por defecto (85%)
     */
    const DEFAULT_THRESHOLD = 85;

    /**
     * Buscar clientes similares usando algoritmo Levenshtein
     *
     * @param string $nombre Nombre a buscar
     * @param int $threshold Umbral de similitud (0-100)
     * @return Collection Colección de clientes similares con su porcentaje de similitud
     */
    public function findSimilarClients(string $nombre, int $threshold = self::DEFAULT_THRESHOLD): Collection
    {
        $normalized = Client::normalizeClientName($nombre);
        $clients = Client::all();
        $similar = collect();

        foreach ($clients as $client) {
            $similarity = $this->calculateSimilarity($normalized, $client->nombre_normalizado);
            
            if ($similarity >= $threshold) {
                $similar->push([
                    'client' => $client,
                    'similarity' => $similarity,
                ]);
            }
        }

        // Ordenar por similitud descendente
        return $similar->sortByDesc('similarity');
    }

    /**
     * Calcular porcentaje de similitud entre dos strings usando Levenshtein
     *
     * @param string $str1 Primer string
     * @param string $str2 Segundo string
     * @return float Porcentaje de similitud (0-100)
     */
    public function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = mb_strtolower($str1, 'UTF-8');
        $str2 = mb_strtolower($str2, 'UTF-8');

        // Si son idénticos
        if ($str1 === $str2) {
            return 100.0;
        }

        $maxLength = max(strlen($str1), strlen($str2));
        
        if ($maxLength === 0) {
            return 100.0;
        }

        $distance = levenshtein($str1, $str2);
        $similarity = (1 - ($distance / $maxLength)) * 100;

        return round($similarity, 2);
    }

    /**
     * Crear alias para un cliente
     *
     * @param int $clientId ID del cliente
     * @param string $alias Alias a crear
     * @return ClientAlias
     */
    public function createAlias(int $clientId, string $alias): ClientAlias
    {
        return ClientAlias::create([
            'client_id' => $clientId,
            'alias' => Client::normalizeClientName($alias),
        ]);
    }

    /**
     * Resolver cliente por alias
     * Busca primero en aliases, luego en nombres normalizados
     *
     * @param string $nombre Nombre o alias a buscar
     * @return Client|null Cliente encontrado o null
     */
    public function resolveClientByAlias(string $nombre): ?Client
    {
        $normalized = Client::normalizeClientName($nombre);

        // Buscar en aliases
        $alias = ClientAlias::where('alias', $normalized)->first();
        if ($alias) {
            return $alias->client;
        }

        // Buscar en nombres normalizados
        return Client::where('nombre_normalizado', $normalized)->first();
    }

    /**
     * Normalizar string (wrapper del método de Client)
     *
     * @param string $str String a normalizar
     * @return string String normalizado
     */
    public function normalizeString(string $str): string
    {
        return Client::normalizeClientName($str);
    }
}
