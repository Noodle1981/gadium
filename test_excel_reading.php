<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$excelPath = 'd:\Gadium\doc\excel pruebas\tablero\tableros.xlsx';

if (!file_exists($excelPath)) {
    echo "✗ Excel file not found at: $excelPath\n";
    exit(1);
}

echo "Testing Excel file reading...\n";
echo "File: $excelPath\n";
echo "File size: " . filesize($excelPath) . " bytes\n\n";

$service = new App\Services\ExcelImportService(app(App\Services\ClientNormalizationService::class));

try {
    // Test validateAndAnalyze
    echo "1. Testing validateAndAnalyze...\n";
    $analysis = $service->validateAndAnalyze($excelPath, 'board_detail');
    echo "   Total rows: {$analysis['total_rows']}\n";
    echo "   Valid rows: {$analysis['valid_rows']}\n";
    echo "   Errors: " . count($analysis['errors']) . "\n";
    if (!empty($analysis['errors'])) {
        echo "   First 3 errors:\n";
        foreach (array_slice($analysis['errors'], 0, 3) as $error) {
            echo "     - $error\n";
        }
    }
    echo "\n";
    
    // Test readExcelRows
    echo "2. Testing readExcelRows...\n";
    $rows = $service->readExcelRows($excelPath);
    echo "   Rows returned: " . count($rows) . "\n";
    
    if (count($rows) > 0) {
        echo "   First row keys: " . implode(', ', array_keys($rows[0])) . "\n";
        echo "   First row data:\n";
        print_r($rows[0]);
    } else {
        echo "   ✗ NO ROWS RETURNED!\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
