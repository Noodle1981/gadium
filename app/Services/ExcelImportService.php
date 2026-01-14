<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Client;
use App\Models\Sale;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Exception;

class ExcelImportService
{
    protected $normalizationService;

    public function __construct(ClientNormalizationService $normalizationService)
    {
        $this->normalizationService = $normalizationService;
    }

    /**
     * Valida la estructura y contenido del archivo Excel.
     * Retorna un resumen de la validación.
     */
    public function validateAndAnalyze(string $filePath, string $type): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                throw new Exception("El archivo Excel está vacío.");
            }

            // 1. Validar Cabeceras
            $headers = array_shift($rows);
            $headers = array_map('trim', $headers);
            
            $this->validateHeaders($headers, $type);

            $validCount = 0;
            $errors = [];
            $unknownClients = [];
            $rowIndex = 2; // 1-based, start after header

            // 2. Escanear filas
            foreach ($rows as $data) {
                // Skip completely empty rows
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

                $row = array_combine($headers, $data);

                // Validación básica de tipos
                $rowErrors = $this->validateRowTypes($row, $rowIndex, $type);

                if (!empty($rowErrors)) {
                    $errors = array_merge($errors, $rowErrors);
                } else {
                    // Verificar Cliente
                    $clientName = $this->extractClientName($row, $type);
                    $client = $this->normalizationService->resolveClientByAlias($clientName);

                    if (!$client) {
                        $unknownClients[$clientName] = true;
                    }

                    $validCount++;
                }

                $rowIndex++;
            }

            return [
                'total_rows' => count($rows),
                'valid_rows' => $validCount,
                'errors' => $errors,
                'unknown_clients' => array_keys($unknownClients),
            ];

        } catch (Exception $e) {
            throw new Exception("Error al procesar Excel: " . $e->getMessage());
        }
    }

    /**
     * Valida que las cabeceras contengan las columnas necesarias según el tipo.
     */
    protected function validateHeaders(array $headers, string $type): void
    {
        if ($type === 'sale') {
            // Tango format - verificar columnas clave
            $required = ['RAZON_SOCI', 'FECHA_EMI', 'TOTAL_COMP', 'N_COMP', 'MONEDA'];
        } else {
            // Budget format
            $required = ['Empresa', 'Fecha', 'Monto', 'Orden de Pedido'];
        }

        foreach ($required as $req) {
            if (!in_array($req, $headers)) {
                throw new Exception("Cabecera faltante: {$req}. Estructura requerida: " . implode(', ', $required));
            }
        }
    }

    /**
     * Extrae el nombre del cliente según el tipo de archivo
     */
    protected function extractClientName(array $row, string $type): string
    {
        if ($type === 'sale') {
            return trim($row['RAZON_SOCI'] ?? '');
        } else {
            return trim($row['Empresa'] ?? '');
        }
    }

    /**
     * Extrae la fecha según el tipo de archivo
     */
    protected function extractDate(array $row, string $type): ?string
    {
        $dateValue = $type === 'sale' ? ($row['FECHA_EMI'] ?? null) : ($row['Fecha'] ?? null);
        
        if (empty($dateValue)) {
            return null;
        }

        // Si es un número (Excel serial date)
        if (is_numeric($dateValue)) {
            try {
                $date = Date::excelToDateTimeObject($dateValue);
                return $date->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }

        // Si es string, intentar múltiples formatos
        $dateString = trim($dateValue);
        
        // Formato con barras: intentar detectar si es dd/mm/yyyy o mm/dd/yyyy
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateString, $matches)) {
            $first = (int)$matches[1];
            $second = (int)$matches[2];
            $year = $matches[3];
            
            // Si el primer número es > 12, definitivamente es dd/mm/yyyy
            if ($first > 12) {
                $day = str_pad($first, 2, '0', STR_PAD_LEFT);
                $month = str_pad($second, 2, '0', STR_PAD_LEFT);
                
                if (checkdate((int)$month, (int)$day, (int)$year)) {
                    return "{$year}-{$month}-{$day}";
                }
            }
            // Si el segundo número es > 12, definitivamente es mm/dd/yyyy
            elseif ($second > 12) {
                $month = str_pad($first, 2, '0', STR_PAD_LEFT);
                $day = str_pad($second, 2, '0', STR_PAD_LEFT);
                
                if (checkdate((int)$month, (int)$day, (int)$year)) {
                    return "{$year}-{$month}-{$day}";
                }
            }
            // Ambos son <= 12, intentar primero formato dd/mm/yyyy (más común en LATAM)
            else {
                // Intentar dd/mm/yyyy
                $day = str_pad($first, 2, '0', STR_PAD_LEFT);
                $month = str_pad($second, 2, '0', STR_PAD_LEFT);
                
                if (checkdate((int)$month, (int)$day, (int)$year)) {
                    return "{$year}-{$month}-{$day}";
                }
                
                // Si falla, intentar mm/dd/yyyy
                $month = str_pad($first, 2, '0', STR_PAD_LEFT);
                $day = str_pad($second, 2, '0', STR_PAD_LEFT);
                
                if (checkdate((int)$month, (int)$day, (int)$year)) {
                    return "{$year}-{$month}-{$day}";
                }
            }
        }
        
        // Intentar parsear con strtotime (para otros formatos)
        $timestamp = strtotime(str_replace('/', '-', $dateString));
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    /**
     * Extrae el monto según el tipo de archivo
     */
    protected function extractAmount(array $row, string $type): ?float
    {
        $amountValue = $type === 'sale' ? ($row['TOTAL_COMP'] ?? null) : ($row['Monto'] ?? null);
        
        if ($amountValue === null || $amountValue === '') {
            return null;
        }

        // Si ya es numérico
        if (is_numeric($amountValue)) {
            return (float) $amountValue;
        }

        // Si es string, normalizar
        return $this->normalizeAmount($amountValue);
    }

    /**
     * Extrae el comprobante según el tipo de archivo
     */
    protected function extractComprobante(array $row, string $type): string
    {
        if ($type === 'sale') {
            return trim($row['N_COMP'] ?? '');
        } else {
            return trim($row['Orden de Pedido'] ?? '');
        }
    }

    /**
     * Extrae la moneda según el tipo de archivo
     */
    protected function extractMoneda(array $row, string $type): string
    {
        if ($type === 'sale') {
            $moneda = trim($row['MONEDA'] ?? 'USD');
            // Normalizar: CTE -> USD, etc.
            return $moneda === 'CTE' ? 'USD' : $moneda;
        } else {
            // Budget siempre en USD según el formato
            return 'USD';
        }
    }

    protected function validateRowTypes(array $row, int $rowIndex, string $type): array
    {
        $errors = [];

        // Validar Fecha
        $fecha = $this->extractDate($row, $type);
        if (!$fecha) {
            $dateField = $type === 'sale' ? 'FECHA_EMI' : 'Fecha';
            $errors[] = "Fila {$rowIndex}: Fecha inválida ({$row[$dateField]})";
        }

        // Validar Monto
        $monto = $this->extractAmount($row, $type);
        if ($monto === null) {
            $amountField = $type === 'sale' ? 'TOTAL_COMP' : 'Monto';
            $errors[] = "Fila {$rowIndex}: Monto inválido ({$row[$amountField]})";
        }

        // Validar Cliente
        $clientName = $this->extractClientName($row, $type);
        if (empty($clientName)) {
            $clientField = $type === 'sale' ? 'RAZON_SOCI' : 'Empresa';
            $errors[] = "Fila {$rowIndex}: Cliente vacío";
        }

        return $errors;
    }

    /**
     * Normaliza un monto en formato EU/LATAM (1.240.000,00)
     */
    protected function normalizeAmount(string $amount): ?float
    {
        $amount = trim($amount);
        
        $lastComma = strrpos($amount, ',');
        $lastDot = strrpos($amount, '.');
        
        // Si tiene punto después de coma, es formato US -> RECHAZAR
        if ($lastDot !== false && $lastComma !== false && $lastDot > $lastComma) {
            return null;
        }
        
        // Eliminar puntos (separadores de miles)
        $amount = str_replace('.', '', $amount);
        
        // Convertir coma decimal a punto
        $amount = str_replace(',', '.', $amount);
        
        if (is_numeric($amount)) {
            return (float) $amount;
        }
        
        return null;
    }

    /**
     * Helper para parsear fechas de Excel (maneja valores vacíos)
     */
    protected function parseExcelDate($dateValue): ?string
    {
        if (empty($dateValue)) {
            return null;
        }

        // Si es un número (Excel serial date)
        if (is_numeric($dateValue)) {
            try {
                $date = Date::excelToDateTimeObject($dateValue);
                return $date->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }

        // Si es string, intentar múltiples formatos
        $dateString = trim($dateValue);
        
        // Formato con barras: detectar dd/mm/yyyy o mm/dd/yyyy
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateString, $matches)) {
            $first = (int)$matches[1];
            $second = (int)$matches[2];
            $year = $matches[3];
            
            // Si el primer número es > 12, es dd/mm/yyyy
            if ($first > 12) {
                $day = str_pad($first, 2, '0', STR_PAD_LEFT);
                $month = str_pad($second, 2, '0', STR_PAD_LEFT);
                
                if (checkdate((int)$month, (int)$day, (int)$year)) {
                    return "{$year}-{$month}-{$day}";
                }
            }
            // Si el segundo número es > 12, es mm/dd/yyyy
            elseif ($second > 12) {
                $month = str_pad($first, 2, '0', STR_PAD_LEFT);
                $day = str_pad($second, 2, '0', STR_PAD_LEFT);
                
                if (checkdate((int)$month, (int)$day, (int)$year)) {
                    return "{$year}-{$month}-{$day}";
                }
            }
            // Ambos <= 12, intentar dd/mm/yyyy primero
            else {
                $day = str_pad($first, 2, '0', STR_PAD_LEFT);
                $month = str_pad($second, 2, '0', STR_PAD_LEFT);
                
                if (checkdate((int)$month, (int)$day, (int)$year)) {
                    return "{$year}-{$month}-{$day}";
                }
                
                // Si falla, intentar mm/dd/yyyy
                $month = str_pad($first, 2, '0', STR_PAD_LEFT);
                $day = str_pad($second, 2, '0', STR_PAD_LEFT);
                
                if (checkdate((int)$month, (int)$day, (int)$year)) {
                    return "{$year}-{$month}-{$day}";
                }
            }
        }
        
        // Intentar parsear con strtotime
        $timestamp = strtotime(str_replace('/', '-', $dateString));
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    /**
     * Procesa un chunk de datos para importar desde Excel.
     */
    public function importChunk(array $rows, string $type): array
    {
        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $clientName = $this->extractClientName($row, $type);
            $client = $this->normalizationService->resolveClientByAlias($clientName);

            if (!$client) {
                continue;
            }

            $fecha = $this->extractDate($row, $type);
            $monto = $this->extractAmount($row, $type) ?? 0;
            $comprobante = $this->extractComprobante($row, $type);
            $moneda = $this->extractMoneda($row, $type);

            if (!$fecha) {
                continue;
            }

            if ($type === 'sale') {
                $hash = Sale::generateHash($fecha, $clientName, $comprobante, $monto);

                if (Sale::existsByHash($hash)) {
                    $skipped++;
                    continue;
                }

                Sale::create([
                    'fecha' => $fecha,
                    'client_id' => $client->id,
                    'cliente_nombre' => $client->nombre,
                    'monto' => $monto,
                    'moneda' => $moneda,
                    'comprobante' => $comprobante,
                    'hash' => $hash,
                    // Columnas Tango adicionales
                    'cod_cli' => trim($row['COD_CLI'] ?? ''),
                    'n_remito' => trim($row['N_REMITO'] ?? ''),
                    't_comp' => trim($row['T_COMP'] ?? ''),
                    'cond_vta' => trim($row['COND_VTA'] ?? ''),
                    'porc_desc' => $this->normalizeAmount($row['PORC_DESC'] ?? '0'),
                    'cotiz' => $this->normalizeAmount($row['COTIZ'] ?? '1'),
                    'cod_transp' => trim($row['COD_TRANSP'] ?? ''),
                    'nom_transp' => trim($row['NOM_TRANSP'] ?? ''),
                    'cod_articu' => trim($row['COD_ARTICU'] ?? ''),
                    'descripcio' => trim($row['DESCRIPCIO'] ?? ''),
                    'cod_dep' => trim($row['COD_DEP'] ?? ''),
                    'um' => trim($row['UM'] ?? ''),
                    'cantidad' => $this->normalizeAmount($row['CANTIDAD'] ?? '0'),
                    'precio' => $this->normalizeAmount($row['PRECIO'] ?? '0'),
                    'tot_s_imp' => $this->normalizeAmount($row['TOT_S_IMP'] ?? '0'),
                    'n_comp_rem' => trim($row['N_COMP_REM'] ?? ''),
                    'cant_rem' => $this->normalizeAmount($row['CANT_REM'] ?? '0'),
                    'fecha_rem' => $this->extractDate($row, 'sale') ?: null, // Usar helper para fecha_rem
                ]);

            } elseif ($type === 'budget') {
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
                    'moneda' => $moneda,
                    'comprobante' => $comprobante,
                    'hash' => $hash,
                    // Columnas adicionales de Presupuestos
                    'centro_costo' => trim($row['Centro de Costo'] ?? ''),
                    'nombre_proyecto' => trim($row['Nombre Proyecto'] ?? ''),
                    'fecha_oc' => $this->parseExcelDate($row['Fecha de OC'] ?? null),
                    'fecha_estimada_culminacion' => $this->parseExcelDate($row['Fecha estimada de culminación'] ?? null),
                    'estado_proyecto_dias' => is_numeric($row['Estado del proyecto en días'] ?? null) ? (int)$row['Estado del proyecto en días'] : null,
                    'fecha_culminacion_real' => $this->parseExcelDate($row['Fecha de culminación real'] ?? null),
                    'estado' => trim($row['Estado'] ?? ''),
                    'enviado_facturar' => trim($row['Enviado a facturar'] ?? ''),
                    'nro_factura' => trim($row['Nº de Factura'] ?? ''),
                    'porc_facturacion' => trim($row['% Facturación'] ?? ''),
                    'saldo' => $this->normalizeAmount($row['Saldo [$]'] ?? '0'),
                    'horas_ponderadas' => is_numeric($row['Horas ponderadas'] ?? null) ? (float)$row['Horas ponderadas'] : null,
                ]);
            }

            $inserted++;
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    /**
     * Lee todas las filas del Excel para procesamiento
     */
    public function readExcelRows(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            return [];
        }

        // Primera fila son headers
        $headers = array_shift($rows);
        $headers = array_map('trim', $headers);

        $result = [];
        foreach ($rows as $data) {
            // Skip empty rows
            if (empty(array_filter($data, function($val) { return $val !== '' && $val !== null; }))) {
                continue;
            }

            if (count($data) === count($headers)) {
                $result[] = array_combine($headers, $data);
            }
        }

        return $result;
    }
}
