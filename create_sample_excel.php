<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Headers
$headers = [
    'Año',
    'Proyecto Numero',
    'Cliente',
    'Descripción Proyecto',
    'Columnas',
    'Gabinetes',
    'Potencia',
    'Pot/Control',
    'Control',
    'Intervención',
    'Documento corrección de Fallas'
];

$sheet->fromArray($headers, NULL, 'A1');

// Sample data
$data = [
    [2025, 'T001', 'Cliente A', 'Tablero de control principal edificio norte', 2, 3, 150, 1, 0, 0, 0],
    [2025, 'T002', 'Cliente B', 'Tablero de potencia para maquinaria industrial', 1, 2, 200, 0, 1, 0, 0],
    [2025, 'T003', 'Cliente C', 'Tablero de distribución área producción', 3, 1, 100, 1, 1, 1, 0],
    [2025, 'T004', 'Cliente D', 'Tablero de emergencia sistema contra incendios', 0, 2, 75, 0, 0, 0, 1]
];

$sheet->fromArray($data, NULL, 'A2');

// Auto-size columns
foreach(range('A','K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Bold headers
$sheet->getStyle('A1:K1')->getFont()->setBold(true);

$outputPath = __DIR__ . '/doc/excel pruebas/tablero/tableros_ejemplo.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($outputPath);

echo "✓ Excel de ejemplo creado exitosamente!\n";
echo "Ubicación: $outputPath\n\n";
echo "Contenido:\n";
echo "- 4 registros de tableros de ejemplo\n";
echo "- Todos los campos requeridos incluidos\n";
echo "- Formato correcto para importación\n";
