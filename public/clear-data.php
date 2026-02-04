<?php
/**
 * Script para borrar el contenido de las tablas de datos importados.
 *
 * USO:
 *   - Via navegador: /clear-data.php?confirm=BORRAR_DATOS_2024
 *   - Via CLI: php public/clear-data.php confirm=BORRAR_DATOS_2024
 *
 * TABLAS QUE SE MANTIENEN:
 *   - users
 *   - roles
 *   - permissions
 *   - role_has_permissions
 *   - model_has_roles
 *   - model_has_permissions
 *   - password_reset_tokens
 *   - sessions
 *   - cache
 *   - jobs
 *   - migrations
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Configurar respuesta
header('Content-Type: text/plain; charset=utf-8');

// Clave de confirmacion requerida
$confirmKey = 'BORRAR_DATOS_2024';

// Obtener parametro de confirmacion
$confirm = $_GET['confirm'] ?? null;

// Si se ejecuta desde CLI
if (php_sapi_name() === 'cli') {
    foreach ($argv as $arg) {
        if (strpos($arg, 'confirm=') === 0) {
            $confirm = substr($arg, 8);
        }
    }
}

// Verificar confirmacion
if ($confirm !== $confirmKey) {
    echo "===========================================\n";
    echo "  SCRIPT DE LIMPIEZA DE DATOS\n";
    echo "===========================================\n\n";
    echo "Este script borrara TODOS los datos de los modulos importados.\n";
    echo "Los usuarios y roles NO seran afectados.\n\n";
    echo "Para ejecutar, proporcione la clave de confirmacion:\n\n";
    echo "  Via navegador: ?confirm={$confirmKey}\n";
    echo "  Via CLI: php public/clear-data.php confirm={$confirmKey}\n\n";
    echo "ADVERTENCIA: Esta accion es IRREVERSIBLE.\n";
    exit(1);
}

// Tablas a limpiar (en orden por dependencias FK)
$tablesToClear = [
    // Primero: tablas con FK a otras tablas de datos
    'audits',                          // Registros de auditoria
    'client_satisfaction_analyses',    // Analisis de satisfaccion clientes
    'staff_satisfaction_analyses',     // Analisis de satisfaccion personal
    'client_satisfaction_responses',   // Respuestas satisfaccion clientes
    'staff_satisfaction_responses',    // Respuestas satisfaccion personal
    'daily_metrics_aggregates',        // Metricas diarias
    'manufacturing_logs',              // Logs de produccion

    // Segundo: tablas principales de datos
    'sales',                           // Ventas
    'budgets',                         // Presupuestos
    'hour_details',                    // Horas
    'purchase_details',                // Compras
    'board_details',                   // Tableros
    'automation_projects',             // Proyectos de automatizacion

    // Tercero: tablas de alias
    'client_aliases',                  // Alias de clientes
    'supplier_aliases',                // Alias de proveedores
    'user_aliases',                    // Alias de usuarios

    // Cuarto: catalogos de datos (que se pueden regenerar)
    'weighting_factors',               // Factores de ponderacion
    'guardias',                        // Guardias
    'job_functions',                   // Funciones de trabajo

    // Quinto: entidades principales
    'projects',                        // Proyectos
    'clients',                         // Clientes
    'suppliers',                       // Proveedores
    'cost_centers',                    // Centros de costo
];

echo "===========================================\n";
echo "  INICIANDO LIMPIEZA DE DATOS\n";
echo "===========================================\n\n";
echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "Entorno: " . app()->environment() . "\n\n";

$totalDeleted = 0;
$errors = [];

// Desactivar FK checks para SQLite/MySQL
try {
    if (DB::connection()->getDriverName() === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = OFF');
    } else {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    }
} catch (\Exception $e) {
    // Ignorar si no soporta
}

foreach ($tablesToClear as $table) {
    try {
        if (!Schema::hasTable($table)) {
            echo "[SKIP] Tabla '{$table}' no existe\n";
            continue;
        }

        $count = DB::table($table)->count();

        if ($count === 0) {
            echo "[OK]   Tabla '{$table}' ya estaba vacia\n";
            continue;
        }

        DB::table($table)->truncate();
        $totalDeleted += $count;
        echo "[OK]   Tabla '{$table}': {$count} registros eliminados\n";

    } catch (\Exception $e) {
        // Si truncate falla por FK, intentar delete
        try {
            $count = DB::table($table)->count();
            DB::table($table)->delete();
            $totalDeleted += $count;
            echo "[OK]   Tabla '{$table}': {$count} registros eliminados (via DELETE)\n";
        } catch (\Exception $e2) {
            $errors[] = "Error en '{$table}': " . $e2->getMessage();
            echo "[ERR]  Tabla '{$table}': " . $e2->getMessage() . "\n";
        }
    }
}

// Reactivar FK checks
try {
    if (DB::connection()->getDriverName() === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON');
    } else {
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
} catch (\Exception $e) {
    // Ignorar si no soporta
}

echo "\n===========================================\n";
echo "  RESUMEN\n";
echo "===========================================\n";
echo "Total de registros eliminados: {$totalDeleted}\n";

if (!empty($errors)) {
    echo "\nErrores encontrados:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

echo "\nLimpieza completada.\n";
echo "Los usuarios y roles NO fueron modificados.\n";
