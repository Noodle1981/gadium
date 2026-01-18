<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAlias;
use App\Models\JobFunction;
use App\Models\Guardia;
use Illuminate\Support\Collection;

class EmployeeNormalizationService
{
    /**
     * Umbral de similitud por defecto (85%)
     */
    const DEFAULT_THRESHOLD = 85;

    /**
     * Normaliza un nombre para comparación (lowercase, trim, remove symbols)
     */
    public function normalizeName(string $name): string
    {
        $normalized = mb_strtolower($name, 'UTF-8');
        $normalized = str_replace(['.', ',', '-', '_'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return trim($normalized);
    }

    /**
     * Busca empleados similares usando algoritmo Levenshtein
     */
    public function findSimilarEmployees(string $name, int $threshold = self::DEFAULT_THRESHOLD): Collection
    {
        $normalized = $this->normalizeName($name);
        $users = User::all(); // TODO: Filtrar solo usuarios activos o con rol de operario? Por ahora todos.
        $similar = collect();

        foreach ($users as $user) {
            $userNormalized = $this->normalizeName($user->name);

            // 1. Coincidencia Exacta (o contención simple)
            if ($userNormalized !== '' && str_contains($userNormalized, $normalized)) {
                $isStart = str_starts_with($userNormalized, $normalized);
                $score = $isStart ? 100.0 : 90.0;
                
                if ($userNormalized === $normalized) {
                    $score = 100.0;
                }

                $similar->push([
                    'user' => $user,
                    'similarity' => $score,
                    'match_type' => 'name_match',
                ]);
                continue;
            }

            // 2. Buscar en Alias existentes
            foreach ($user->aliases as $alias) {
                if ($alias->alias === $normalized) {
                    $similar->push([
                        'user' => $user,
                        'similarity' => 100.0,
                        'match_type' => 'alias_match',
                    ]);
                    continue 2;
                }
            }

            // 3. Coincidencia Difusa (Levenshtein)
            $similarity = $this->calculateSimilarity($normalized, $userNormalized);
            
            if ($similarity >= $threshold) {
                $similar->push([
                    'user' => $user,
                    'similarity' => $similarity,
                    'match_type' => 'fuzzy_match',
                ]);
            }
        }

        return $similar->sortByDesc('similarity');
    }

    /**
     * Calcular porcentaje de similitud
     */
    public function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = mb_strtolower($str1, 'UTF-8');
        $str2 = mb_strtolower($str2, 'UTF-8');

        if ($str1 === $str2) return 100.0;

        $maxLength = max(strlen($str1), strlen($str2));
        if ($maxLength === 0) return 100.0;

        $distance = levenshtein($str1, $str2);
        return round((1 - ($distance / $maxLength)) * 100, 2);
    }

    /**
     * Resuelve o Crea una Función de Trabajo
     * Usa nombres en Mayúsculas para mantener el catálogo limpio.
     */
    public function resolveJobFunction(string $name): ?JobFunction
    {
        if (empty(trim($name))) return null;

        return JobFunction::firstOrCreate(
            ['name' => mb_strtoupper(trim($name), 'UTF-8')]
        );
    }

    /**
     * Resuelve o Crea una Guardia
     * Usa nombres Capitalizados (Primera mayúscula).
     */
    public function resolveGuardia(string $name): ?Guardia
    {
        if (empty(trim($name))) return null;

        // "Hs. pernoctada" -> "Pernoctada" o lo que venga
        // Normalmente vienen "Si", "No", o valores numéricos a veces?
        // El user dijo "Que no todos tendrán guardia".
        // Si viene "No", quizás no deberíamos crear una guardia "NO".
        // Pero el campo en hours original era hs_pernoctada string.
        // Asumamos que creamos todo lo que venga distinto de vacío/null/0/No?
        // Revisando importación: hs_pernoctada default 'No'.
        
        $cleanName = mb_strtoupper(trim($name), 'UTF-8');
        
        if (in_array($cleanName, ['NO', '0', '', 'NONE'])) {
            return null; 
        }

        return Guardia::firstOrCreate(
            ['name' => $cleanName]
        );
    }

    /**
     * Intenta resolver un usuario automáticamente si hay coincidencia perfecta.
     */
    public function resolveUser(string $importedName): ?User
    {
        $normalized = $this->normalizeName($importedName);
        
        // 1. Buscar en Alias exacto
        $alias = UserAlias::where('alias', $normalized)->with('user')->first();
        if ($alias) return $alias->user;

        // 2. Buscar por nombre exacto (normalizado)
        // Esto es costoso si no tenemos columna normalizada en users, pero User::all() es manejable por ahora.
        // Mejor: Buscar directo en DB si asumimos que users.name está limpio.
        // $user = User::where('name', $importedName)->first();
        // Si no, iteramos (el dataset de empleados no suele ser > 1000).
        
        $candidates = $this->findSimilarEmployees($importedName, 100); // Threshold 100 for exact match
        
        if ($candidates->isNotEmpty()) {
            return $candidates->first()['user'];
        }

        return null;
    }
}
