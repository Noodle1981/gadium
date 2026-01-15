<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Client;
use App\Models\Sale;
use App\Models\HourDetail;
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
     * Parsea montos con formato "USD 1.234" o "-USD 631" a float
     */
    protected function parsePurchaseAmount($value): float
    {
        if (empty($value)) return 0.0;
        
        // Remover "USD" y espacios
        $value = str_ireplace(['USD', ' '], '', $value);
        
        // Manejar negativos si el menos estaba antes del USD "- 1.234"
        // normalizeAmount maneja puntos de miles y coma decimal (formato EU/LATAM que usa el Excel)
        return $this->normalizeAmount($value) ?? 0.0;
    }

    /**
     * Parsea porcentajes con formato "36%" a float
     */
    protected function parsePercentage($value): float
    {
        if (empty($value)) return 0.0;
        
        // Remover "%" y espacios
        $value = str_replace(['%', ' '], '', $value);
        
        return $this->normalizeAmount($value) ?? 0.0;
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
        } elseif ($type === 'hour_detail') {
            // Hour Detail format
            $required = ['Dia', 'Fecha', 'Año', 'Mes', 'Personal', 'Funcion', 'Proyecto', 'Horas ponderadas', 'Ponderador', 'Hs'];
        } elseif ($type === 'purchase_detail') {
            // Purchase Detail format
            $required = ['Moneda', 'CC', 'Año', 'Empresa', 'Descripción', 'Materiales presupuestados', 'Materiales comprados'];

        } elseif ($type === 'board_detail') {
            // Board Detail format
            $required = ['Año', 'Proyecto Numero', 'Cliente', 'Descripción Proyecto', 'Columnas', 'Gabinetes', 'Potencia', 'Pot/Control', 'Control', 'Intervención', 'Documento corrección de Fallas'];
        } else {
            // Budget format
            $required = ['Empresa', 'Fecha', 'Monto', 'Orden de Pedido'];
        }

        foreach ($required as $req) {
            if (!in_array($req, $headers)) {
                // Check for case-insensitive match to give a hint
                $found = array_filter($headers, function($h) use ($req) {
                    return strtolower($h) === strtolower($req);
                });
                
                $hint = !empty($found) ? ". Quizás quiso decir: '" . reset($found) . "'?" : "";
                
                throw new Exception("Cabecera faltante: '{$req}'" . $hint . ".\nCabeceras encontradas en el archivo: [" . implode(', ', $headers) . "]");
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
        } elseif ($type === 'hour_detail' || $type === 'purchase_detail' || $type === 'board_detail') {
            return ''; // No client in hour detail, purchase detail, or board detail
        } else {
            return trim($row['Empresa'] ?? '');
        }
    }

    /**
     * Extrae la fecha según el tipo de archivo
     */
    protected function extractDate(array $row, string $type): ?string
    {
        if ($type === 'board_detail' || $type === 'purchase_detail') {
            return null; // Boards and purchases don't use date field for import
        } elseif ($type === 'hour_detail') {
             $dateValue = $row['Fecha'] ?? null;
        } else {
             $dateValue = $type === 'sale' ? ($row['FECHA_EMI'] ?? null) : ($row['Fecha'] ?? null);
        }
        
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
        if ($type === 'hour_detail' || $type === 'purchase_detail' || $type === 'board_detail') {
            return 0.0;
        }

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
        if ($type === 'hour_detail' || $type === 'purchase_detail' || $type === 'board_detail') {
            return '';
        }

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
        if ($type === 'hour_detail' || $type === 'purchase_detail' || $type === 'board_detail') {
            return '';
        }

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
        if (!$fecha && $type !== 'purchase_detail' && $type !== 'board_detail') {
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
        if (empty($clientName) && $type !== 'hour_detail' && $type !== 'purchase_detail' && $type !== 'board_detail') {
            $clientField = $type === 'sale' ? 'RAZON_SOCI' : 'Empresa';
            $errors[] = "Fila {$rowIndex}: Cliente vacío";
        }

        if ($type === 'hour_detail') {
            // Validaciones específicas para Horas
            if (empty($row['Personal'])) {
                $errors[] = "Fila {$rowIndex}: Personal vacío";
            }
            if (empty($row['Proyecto'])) {
                $errors[] = "Fila {$rowIndex}: Proyecto vacío";
            }
            if (!isset($row['Hs']) || !is_numeric($row['Hs'])) {
                $errors[] = "Fila {$rowIndex}: Horas inválidas ({$row['Hs']})";
            }
        } elseif ($type === 'purchase_detail') {
            // Validaciones específicas para Compras
            if (empty($row['Empresa'])) {
                $errors[] = "Fila {$rowIndex}: Empresa vacía";
            }
            if (empty($row['CC'])) {
                $errors[] = "Fila {$rowIndex}: CC vacío";
            }
        } elseif ($type === 'board_detail') {
            if (empty($row['Año']) || !is_numeric($row['Año'])) {
                $errors[] = "Fila {$rowIndex}: Año inválido";
            }
            if (empty($row['Proyecto Numero'])) {
                $errors[] = "Fila {$rowIndex}: Proyecto Numero vacío";
            }
            if (empty($row['Cliente'])) {
                $errors[] = "Fila {$rowIndex}: Cliente vacío";
            }
            
            // Numeric fields are lenient as they default to 0 if invalid/empty, 
            // but if present they should ideally be numeric.
            // checking simple "is_numeric" for non-empty values
            $numericFields = ['Columnas', 'Gabinetes', 'Potencia', 'Pot/Control', 'Control', 'Intervención', 'Documento corrección de Fallas'];
            foreach ($numericFields as $field) {
                $val = $row[$field] ?? '';
                if ($val !== '' && $val !== null && !is_numeric($val)) {
                    $errors[] = "Fila {$rowIndex}: {$field} debe ser numérico";
                }
            }
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

            if (!$client && $type !== 'hour_detail' && $type !== 'purchase_detail' && $type !== 'board_detail') {
                continue;
            }

            $fecha = $this->extractDate($row, $type);
            $monto = $this->extractAmount($row, $type) ?? 0;
            $comprobante = $this->extractComprobante($row, $type);
            $moneda = $this->extractMoneda($row, $type);

            if (!$fecha && $type !== 'purchase_detail' && $type !== 'board_detail') {
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

            } elseif ($type === 'hour_detail') {
                // Importar Detalle de Horas
                $personal = trim($row['Personal'] ?? '');
                $proyecto = trim($row['Proyecto'] ?? '');
                $hs = is_numeric($row['Hs'] ?? null) ? (float)$row['Hs'] : 0;
                
                $hash = HourDetail::generateHash($fecha, $personal, $proyecto, $hs);
                
                if (HourDetail::existsByHash($hash)) {
                    $skipped++;
                    continue;
                }
                
                HourDetail::create([
                    'dia' => trim($row['Dia'] ?? ''),
                    'fecha' => $fecha,
                    'ano' => is_numeric($row['Año'] ?? null) ? (int)$row['Año'] : (int)date('Y', strtotime($fecha)),
                    'mes' => is_numeric($row['Mes'] ?? null) ? (int)$row['Mes'] : (int)date('m', strtotime($fecha)),
                    'personal' => $personal,
                    'funcion' => trim($row['Funcion'] ?? ''),
                    'proyecto' => $proyecto,
                    'horas_ponderadas' => is_numeric($row['Horas ponderadas'] ?? null) ? (float)$row['Horas ponderadas'] : 0,
                    'ponderador' => is_numeric($row['Ponderador'] ?? null) ? (float)$row['Ponderador'] : 1,
                    'hs' => $hs,
                    'hs_comun' => is_numeric($row['Hs comun'] ?? null) ? (float)$row['Hs comun'] : 0,
                    'hs_50' => is_numeric($row['Hs (50%)'] ?? null) ? (float)$row['Hs (50%)'] : 0,
                    'hs_100' => is_numeric($row['Hs (100%)'] ?? null) ? (float)$row['Hs (100%)'] : 0,
                    'hs_viaje' => is_numeric($row['Hs de viaje'] ?? null) ? (float)$row['Hs de viaje'] : 0,
                    'hs_pernoctada' => trim($row['Hs pernoctada'] ?? 'No'),
                    'hs_adeudadas' => is_numeric($row['Hs adeudadas'] ?? null) ? (float)$row['Hs adeudadas'] : 0,
                    'vianda' => trim($row['Vianda'] ?? '0'),
                    'observacion' => trim($row['Observación'] ?? ''),
                    'programacion' => trim($row['Programación'] ?? ''),
                    'hash' => $hash,
                ]);
            } elseif ($type === 'purchase_detail') {
                // Importar Detalle de Compras
                $cc = trim($row['CC'] ?? '');
                $ano = is_numeric($row['Año'] ?? null) ? (int)$row['Año'] : date('Y');
                $empresa = trim($row['Empresa'] ?? '');
                $descripcion = trim($row['Descripción'] ?? '');
                
                // Generar hash
                $hash = \App\Models\PurchaseDetail::generateHash($cc, (string)$ano, $empresa, $descripcion);

                if (\App\Models\PurchaseDetail::existsByHash($hash)) {
                    $skipped++;
                    continue;
                }

                \App\Models\PurchaseDetail::create([
                    'moneda' => trim($row['Moneda'] ?? 'USD'),
                    'cc' => $cc,
                    'ano' => $ano,
                    'empresa' => $empresa,
                    'descripcion' => $descripcion,
                    'materiales_presupuestados' => $this->parsePurchaseAmount($row['Materiales presupuestados'] ?? '0'),
                    'materiales_comprados' => $this->parsePurchaseAmount($row['Materiales comprados'] ?? '0'),
                    'resto_valor' => $this->parsePurchaseAmount($row['Resto (Valor)'] ?? '0'),
                    'resto_porcentaje' => $this->parsePercentage($row['Resto (%)'] ?? '0'),
                    'porcentaje_facturacion' => $this->parsePercentage($row['% de facturación'] ?? '0'),
                    'hash' => $hash,
                ]);
            } elseif ($type === 'board_detail') {
                $ano = (int)($row['Año'] ?? 0);
                $proyectoNumero = trim($row['Proyecto Numero'] ?? '');
                $cliente = trim($row['Cliente'] ?? '');
                $descripcion = trim($row['Descripción Proyecto'] ?? '');

                $hash = \App\Models\BoardDetail::generateHash($ano, $proyectoNumero, $cliente, $descripcion);

                if (\App\Models\BoardDetail::existsByHash($hash)) {
                    $skipped++;
                    continue;
                }

                \App\Models\BoardDetail::create([
                    'ano' => $ano,
                    'proyecto_numero' => $proyectoNumero,
                    'cliente' => $cliente,
                    'descripcion_proyecto' => $descripcion,
                    'columnas' => (int)($row['Columnas'] ?? 0),
                    'gabinetes' => (int)($row['Gabinetes'] ?? 0),
                    'potencia' => (int)($row['Potencia'] ?? 0),
                    'pot_control' => (int)($row['Pot/Control'] ?? 0),
                    'control' => (int)($row['Control'] ?? 0),
                    'intervencion' => (int)($row['Intervención'] ?? 0),
                    'documento_correccion_fallas' => (int)($row['Documento corrección de Fallas'] ?? 0),
                    'hash' => $hash,
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
