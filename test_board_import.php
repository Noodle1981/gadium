<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Board Import...\n\n";

// Test data from Excel
$testRow = [
    'Año' => 2025,
    'Proyecto Numero' => '3294',
    'Cliente' => 'DOM',
    'Descripción Proyecto' => 'Fabricación de tablero pozo de agua Potencia 150 HP',
    'Columnas' => 1,
    'Gabinetes' => 0,
    'Potencia' => 0,
    'Pot/Control' => 1,
    'Control' => 0,
    'Intervención' => 0,
    'Documento corrección de Fallas' => 0
];

echo "Test Row Data:\n";
print_r($testRow);

// Generate hash
$hash = App\Models\BoardDetail::generateHash(
    (int)$testRow['Año'],
    trim($testRow['Proyecto Numero']),
    trim($testRow['Cliente']),
    trim($testRow['Descripción Proyecto'])
);

echo "\nGenerated Hash: $hash\n";

// Check if exists
$exists = App\Models\BoardDetail::existsByHash($hash);
echo "Already exists: " . ($exists ? 'YES' : 'NO') . "\n\n";

if (!$exists) {
    echo "Creating record...\n";
    try {
        $record = App\Models\BoardDetail::create([
            'ano' => (int)$testRow['Año'],
            'proyecto_numero' => trim($testRow['Proyecto Numero']),
            'cliente' => trim($testRow['Cliente']),
            'descripcion_proyecto' => trim($testRow['Descripción Proyecto']),
            'columnas' => (int)($testRow['Columnas'] ?? 0),
            'gabinetes' => (int)($testRow['Gabinetes'] ?? 0),
            'potencia' => (int)($testRow['Potencia'] ?? 0),
            'pot_control' => (int)($testRow['Pot/Control'] ?? 0),
            'control' => (int)($testRow['Control'] ?? 0),
            'intervencion' => (int)($testRow['Intervención'] ?? 0),
            'documento_correccion_fallas' => (int)($testRow['Documento corrección de Fallas'] ?? 0),
            'hash' => $hash,
        ]);
        
        echo "✓ Record created successfully! ID: {$record->id}\n";
        echo "\nTotal records in database: " . App\Models\BoardDetail::count() . "\n";
    } catch (\Exception $e) {
        echo "✗ Error creating record: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
} else {
    echo "Record already exists, skipping.\n";
}
