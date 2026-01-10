<?php

namespace App\Imports;

use App\Models\Sale;
use App\Models\Client;
use App\Services\ClientNormalizationService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class SalesImport implements ToModel, WithHeadingRow, WithChunkReading
{
    protected int $newCount = 0;
    protected int $duplicateCount = 0;
    protected int $errorCount = 0;
    protected array $errors = [];
    protected ClientNormalizationService $normalizationService;

    public function __construct()
    {
        $this->normalizationService = new ClientNormalizationService();
    }

    /**
     * Procesar cada fila del CSV
     */
    public function model(array $row)
    {
        try {
            // Validar que existan las columnas obligatorias
            if (!isset($row['fecha']) || !isset($row['cliente']) || !isset($row['monto']) || !isset($row['comprobante'])) {
                $this->errorCount++;
                $this->errors[] = "Fila con columnas faltantes: " . json_encode($row);
                return null;
            }

            // Parsear fecha
            $fecha = $this->parseDate($row['fecha']);
            if (!$fecha) {
                $this->errorCount++;
                $this->errors[] = "Fecha inválida en fila: " . json_encode($row);
                return null;
            }

            $clienteNombre = trim($row['cliente']);
            $monto = (float) $row['monto'];
            $comprobante = trim($row['comprobante']);

            // Generar hash para idempotencia
            $hash = Sale::generateHash(
                $fecha->format('Y-m-d'),
                $clienteNombre,
                $comprobante,
                $monto
            );

            // Verificar si ya existe (duplicado)
            if (Sale::existsByHash($hash)) {
                $this->duplicateCount++;
                return null;
            }

            // Resolver cliente (por alias o nombre normalizado)
            $client = $this->normalizationService->resolveClientByAlias($clienteNombre);

            // Si no existe el cliente, crearlo
            if (!$client) {
                $client = Client::create([
                    'nombre' => $clienteNombre,
                ]);
            }

            // Crear venta
            $this->newCount++;
            
            return new Sale([
                'fecha' => $fecha,
                'client_id' => $client->id,
                'cliente_nombre' => $clienteNombre,
                'monto' => $monto,
                'comprobante' => $comprobante,
                'hash' => $hash,
            ]);

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errors[] = "Error procesando fila: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Parsear fecha en diferentes formatos
     */
    protected function parseDate($fecha)
    {
        try {
            // Intentar parsear como fecha de Excel (número serial)
            if (is_numeric($fecha)) {
                return Carbon::createFromFormat('Y-m-d', '1899-12-30')->addDays($fecha);
            }

            // Intentar diferentes formatos comunes
            $formats = [
                'Y-m-d',
                'd/m/Y',
                'd-m-Y',
                'm/d/Y',
                'Y/m/d',
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $fecha);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Tamaño de chunks para procesamiento
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Obtener estadísticas de importación
     */
    public function getStats(): array
    {
        return [
            'new' => $this->newCount,
            'duplicates' => $this->duplicateCount,
            'errors' => $this->errorCount,
            'error_messages' => $this->errors,
            'total' => $this->newCount + $this->duplicateCount + $this->errorCount,
        ];
    }
}
