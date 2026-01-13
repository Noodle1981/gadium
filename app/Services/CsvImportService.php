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

        // Detect delimiter by reading first line
        $firstLine = fgets($handle);
        rewind($handle);
        
        $delimiter = $this->detectDelimiter($firstLine);

        // 1. Validar Cabeceras
        $headers = fgetcsv($handle, 1000, $delimiter);
        
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
        while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
            // Skip completely empty lines or null data
            if ($data === null || $data === false || (count($data) === 1 && empty($data[0]))) {
                $rowIndex++;
                continue;
            }
            
            // Skip lines with only empty values
            if (empty(array_filter($data, function($val) { return $val !== '' && $val !== null; }))) {
                $rowIndex++;
                continue;
            }
            
            // Validate column count
            if (count($data) !== count($headers)) {
                $errors[] = "Fila {$rowIndex}: Número de columnas incorrecto (esperadas: " . count($headers) . ", encontradas: " . count($data) . ")";
                $rowIndex++;
                continue;
            }
            
            // Safe array_combine with validation
            if (count($headers) === 0 || count($data) === 0) {
                $rowIndex++;
                continue;
            }
            
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
     * Detecta el delimitador del CSV (coma o punto y coma)
     */
    protected function detectDelimiter(string $line): string
    {
        $commaCount = substr_count($line, ',');
        $semicolonCount = substr_count($line, ';');
        
        return $semicolonCount > $commaCount ? ';' : ',';
    }

    /**
     * Valida que las cabeceras sean exactas.
     */
    protected function validateHeaders(array $headers, string $type): void
    {
        // Both sales and budgets require the same 4 columns
        $required = ['Fecha', 'Cliente', 'Monto', 'Comprobante'];

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

        // Validar Monto (acepta separadores de miles)
        $normalizedAmount = $this->normalizeAmount($row['Monto']);
        if ($normalizedAmount === null) {
            $errors[] = "Fila {$rowIndex}: Monto inválido ({$row['Monto']})";
        }

        return $errors;
    }

    /**
     * Normaliza un monto en formato EU/LATAM (1.240.000,00)
     * Rechaza formato US (1,240,000.00) para mantener consistencia
     * 
     * @param string $amount
     * @return float|null
     */
    protected function normalizeAmount(string $amount): ?float
    {
        // Eliminar espacios
        $amount = trim($amount);
        
        // Detectar si usa formato US (coma como separador de miles, punto como decimal)
        // Esto lo rechazamos para forzar el estándar EU/LATAM
        $lastComma = strrpos($amount, ',');
        $lastDot = strrpos($amount, '.');
        
        // Si tiene punto después de coma, es formato US -> RECHAZAR
        if ($lastDot !== false && $lastComma !== false && $lastDot > $lastComma) {
            return null; // Formato US no permitido
        }
        
        // Formato EU/LATAM esperado:
        // - Punto (.) como separador de miles
        // - Coma (,) como separador decimal
        
        // Eliminar puntos (separadores de miles)
        $amount = str_replace('.', '', $amount);
        
        // Convertir coma decimal a punto
        $amount = str_replace(',', '.', $amount);
        
        // Validar que sea numérico después de normalizar
        if (is_numeric($amount)) {
            return (float) $amount;
        }
        
        return null;
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
            $monto = $this->normalizeAmount($row['Monto']) ?? 0;
            
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
                $comprobante = $row['Comprobante'];
                $hash = Budget::generateHash($fecha, $clientName, $comprobante, $monto);

                if (Budget::where('hash', $hash)->exists()) {
                    $skipped++;
                    continue;
                }

                Budget::create([
                    'fecha' => $fecha,
                    'client_id' => $client->id,
                    'cliente_nombre' => $client->nombre,
                    'monto' => $monto,
                    'moneda' => $row['Moneda'] ?? 'USD',
                    'comprobante' => $comprobante,
                    'hash' => $hash,
                ]);
            }

            $inserted++;
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }
}
