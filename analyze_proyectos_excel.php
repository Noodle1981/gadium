<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'd:\\Gadium\\doc\\excel pruebas\\proyectos\\Proyectos Automatización.xlsx';

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Get the highest column and row
    $highestColumn = $sheet->getHighestColumn();
    $highestRow = $sheet->getHighestRow();
    
    echo "Total columns: " . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn) . "\n";
    echo "Total rows: " . $highestRow . "\n\n";
    
    // Get headers
    $headers = [];
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $value = $sheet->getCell($col . '1')->getValue();
        if ($value !== null && trim($value) !== '') {
            $headers[$col] = trim($value);
        }
    }
    
    echo "=== HEADERS ===\n";
    foreach ($headers as $col => $header) {
        echo "$col: $header\n";
    }
    echo "\n";
    
    echo "=== SAMPLE DATA (First 5 rows) ===\n";
    for ($rowNum = 2; $rowNum <= min(6, $highestRow); $rowNum++) {
        echo "Row $rowNum:\n";
        foreach ($headers as $col => $header) {
            $value = $sheet->getCell($col . $rowNum)->getValue();
            echo "  $header: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
