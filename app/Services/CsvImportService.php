<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Client;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class CsvImportService
{
    protected $normalizationService;

    public function __construct(ClientNormalizationService $normalizationService)
    {
        $this->normalizationService = $normalizationService;
    }

    /**
     * Valida la estructura y contenido del archivo CSV.
     * Retorna un resumen de la validación.
     */
    public function validateAndAnalyze(string $filePath, string $type): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("No se pudo abrir el archivo.");
        }

        // 1. Validar Cabeceras
        $headers = fgetcsv($handle, 1000, ',');
        
        // Eliminar BOM si existe
        if (isset($headers[0])) {
            $headers[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $headers[0]);
        }
        
        $this->validateHeaders($headers, $type);

        $validCount = 0;
        $errors = [];
        $unknownClients = [];
        $rowIndex = 2; // 1-based, start after header

        // 2. Escanear filas
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row = array_combine($headers, $data);
            
            // Validación básica de tipos
            $rowErrors = $this->validateRowTypes($row, $rowIndex, $type);
            
            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
            } else {
                // Verificar Cliente
                $clientName = $type === 'sale' ? ($row['Cliente'] ?? '') : ($row['Cliente'] ?? '');
                $client = $this->normalizationService->resolveClientByAlias($clientName);

                if (!$client) {
                    $unknownClients[$clientName] = true; // Use key for uniqueness
                }
                
                $validCount++;
            }

            $rowIndex++;
        }

        fclose($handle);

        return [
            'total_rows' => $rowIndex - 2,
            'valid_rows' => $validCount,
            'errors' => $errors,
            'unknown_clients' => array_keys($unknownClients),
        ];
    }

    /**
     * Valida que las cabeceras sean exactas.
     */
    protected function validateHeaders(array $headers, string $type): void
    {
        $required = ($type === 'sale') 
            ? ['Fecha', 'Cliente', 'Monto', 'Comprobante']
            : ['Fecha', 'Cliente', 'Monto', 'Moneda']; // Budget

        // Normalizar headers del archivo (trim)
        $headers = array_map('trim', $headers);
        
        // Verificar existencia
        foreach ($required as $req) {
            if (!in_array($req, $headers)) {
                throw new Exception("Cabecera faltante: {$req}. Estructura requerida: " . implode(', ', $required));
            }
        }
    }

    protected function validateRowTypes(array $row, int $rowIndex, string $type): array
    {
        $errors = [];

        // Validar Fecha
        if (!strtotime(str_replace('/', '-', $row['Fecha']))) {
             $errors[] = "Fila {$rowIndex}: Fecha inválida ({$row['Fecha']})";
        }

        // Validar Monto
        if (!is_numeric($row['Monto'])) {
            $errors[] = "Fila {$rowIndex}: Monto inválido ({$row['Monto']})";
        }

        return $errors;
    }

    /**
     * Procesa un chunk de datos para importar.
     * Retorna stats: inserted, skipped (duplicates).
     */
    public function importChunk(array $rows, string $type): array
    {
        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $clientName = $row['Cliente'];
            $client = $this->normalizationService->resolveClientByAlias($clientName);

            if (!$client) {
                // Esto no debería pasar si se resolvieron antes, pero por seguridad
                continue; 
            }

            $fecha = date('Y-m-d', strtotime(str_replace('/', '-', $row['Fecha'])));
            $monto = (float) $row['Monto'];
            
            if ($type === 'sale') {
                $comprobante = $row['Comprobante'];
                $hash = Sale::generateHash($fecha, $clientName, $comprobante, $monto); // Sale usa nombre normalizado internamente

                if (Sale::existsByHash($hash)) {
                    $skipped++;
                    continue;
                }

                Sale::create([
                    'fecha' => $fecha,
                    'client_id' => $client->id,
                    'cliente_nombre' => $client->nombre, // Guardamos snapshot
                    'monto' => $monto,
                    'moneda' => $row['Moneda'] ?? 'USD', // Default o columna
                    'comprobante' => $comprobante,
                    'hash' => $hash,
                ]);

            } elseif ($type === 'budget') { // Budget
                $moneda = $row['Moneda'] ?? 'USD';
                $hash = Budget::generateHash($fecha, $client->id, $monto, $moneda);

                if (Budget::where('hash', $hash)->exists()) {
                    $skipped++;
                    continue;
                }

                Budget::create([
                    'fecha' => $fecha,
                    'client_id' => $client->id,
                    'monto' => $monto,
                    'moneda' => $moneda,
                    'hash' => $hash,
                ]);
            }

            $inserted++;
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }
}
