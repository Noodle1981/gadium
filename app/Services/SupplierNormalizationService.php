<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\SupplierAlias;
use Illuminate\Support\Collection;

class SupplierNormalizationService
{
    /**
     * Umbral de similitud por defecto (85%)
     */
    const DEFAULT_THRESHOLD = 85;

    /**
     * Buscar proveedores similares usando algoritmo Levenshtein
     *
     * @param string $nombre Nombre a buscar
     * @param int $threshold Umbral de similitud (0-100)
     * @return Collection Colección de proveedores similares con su porcentaje de similitud
     */
    public function findSimilarSuppliers(string $nombre, int $threshold = self::DEFAULT_THRESHOLD): Collection
    {
        $normalized = Supplier::normalizeSupplierName($nombre);
        $suppliers = Supplier::all();
        $similar = collect();

        foreach ($suppliers as $supplier) {
            // 1. Coincidencia parcial (Autocomplete)
            if ($normalized !== '' && str_contains($supplier->name_normalized, $normalized)) {
                
                // Priorizar "Empieza con" vs "Contiene"
                $isStart = str_starts_with($supplier->name_normalized, $normalized);
                $score = $isStart ? 100.0 : 90.0;
                
                // Ajustar score si es exacto
                if ($supplier->name_normalized === $normalized) {
                    $score = 100.0;
                }

                $similar->push([
                    'supplier' => $supplier,
                    'similarity' => $score,
                ]);
                continue;
            }

            // 2. Coincidencia difusa (Typos)
            $similarity = $this->calculateSimilarity($normalized, $supplier->name_normalized);
            
            if ($similarity >= $threshold) {
                $similar->push([
                    'supplier' => $supplier,
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
     * Crear alias para un proveedor
     *
     * @param int $supplierId ID del proveedor
     * @param string $alias Alias a crear
     * @return SupplierAlias
     */
    public function createAlias(int $supplierId, string $alias): SupplierAlias
    {
        return SupplierAlias::create([
            'supplier_id' => $supplierId,
            'alias' => Supplier::normalizeSupplierName($alias),
        ]);
    }

    /**
     * Resolver proveedor por alias
     * Busca primero en aliases, luego en nombres normalizados
     *
     * @param string $nombre Nombre o alias a buscar
     * @return Supplier|null Proveedor encontrado o null
     */
    public function resolveSupplierByAlias(string $nombre): ?Supplier
    {
        $normalized = Supplier::normalizeSupplierName($nombre);

        // Buscar en aliases
        $alias = SupplierAlias::where('alias', $normalized)->first();
        if ($alias) {
            return $alias->supplier;
        }

        // Buscar en nombres normalizados
        return Supplier::where('name_normalized', $normalized)->first();
    }

    /**
     * Normalizar string (wrapper del método de Supplier)
     *
     * @param string $str String a normalizar
     * @return string String normalizado
     */
    public function normalizeString(string $str): string
    {
        return Supplier::normalizeSupplierName($str);
    }
}
