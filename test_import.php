<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$service = app(\App\Services\CsvImportService::class);

echo "Testing CSV Import Service...\n\n";

$testFile = 'd:\Gadium\public\samples\ventas_test.csv';

if (!file_exists($testFile)) {
    die("Test file not found: $testFile\n");
}

echo "File exists: $testFile\n";
echo "File size: " . filesize($testFile) . " bytes\n\n";

try {
    $result = $service->validateAndAnalyze($testFile, 'sale');
    
    echo "Analysis Results:\n";
    echo "================\n";
    echo "Total Rows: " . ($result['total_rows'] ?? 'N/A') . "\n";
    echo "Valid Rows: " . ($result['valid_rows'] ?? 'N/A') . "\n";
    echo "Errors: " . count($result['errors'] ?? []) . "\n";
    echo "Unknown Clients: " . count($result['unknown_clients'] ?? []) . "\n\n";
    
    if (!empty($result['errors'])) {
        echo "Errors found:\n";
        foreach ($result['errors'] as $error) {
            echo "  - $error\n";
        }
    }
    
    if (!empty($result['unknown_clients'])) {
        echo "\nUnknown clients:\n";
        foreach ($result['unknown_clients'] as $client) {
            echo "  - $client\n";
        }
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
