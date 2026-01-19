<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

echo "STARTING REAL FILE IMPORT TEST...\n";

try {
    $targetFile = 'd:\Gadium\doc\excel pruebas\detalles_hora\horas.xlsx';
    if (!file_exists($targetFile)) {
        die("File not found");
    }

    $spreadsheet = IOFactory::load($targetFile);
    $rows = $spreadsheet->getActiveSheet()->toArray();
    $headers = array_shift($rows);
    
    // Get first data row
    $firstRowData = $rows[0];
    
    // Combine keys
    $row = array_combine($headers, $firstRowData);
    
    echo "Row Data to Import:\n";
    print_r($row);

    // Call service
    $service = app(App\Services\ExcelImportService::class);
    echo "Calling importChunk...\n";
    
    $stats = $service->importChunk([$row], 'hour_detail');
    echo "Service returned: " . json_encode($stats) . "\n";

    // Verify DB
    $personal = $row['Personal'];
    $hd = App\Models\HourDetail::where('personal', $personal)->latest()->first();
    
    file_put_contents('d:\Gadium\result.txt', "RESULT: " . ($hd ? "SUCCESS ID: " . $hd->id : "FAIL") . "\nStats: " . json_encode($stats));

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
